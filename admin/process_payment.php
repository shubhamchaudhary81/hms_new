<?php
include_once '../config/configdatabse.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: billing.php');
    exit;
}

// Get payment data
$invoice_id = $_POST['invoice_id'] ?? '';
$payment_amount = floatval($_POST['payment_amount'] ?? 0);
$payment_method = $_POST['payment_method'] ?? '';
$customer_id = $_POST['customer_id'] ?? '';
$booking_id = $_POST['booking_id'] ?? '';

if (empty($invoice_id) || $payment_amount <= 0) {
    header('Location: billing.php?error=Invalid payment data');
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Fetch invoice details
    $invoice_sql = "
        SELECT 
            i.invoice_id,
            i.total_amount,
            i.amount_paid,
            i.balance_due,
            i.booking_id,
            i.customer_id,
            b.advance_amount,
            rm.price_per_night,
            b.actual_check_in,
            b.actual_check_out
        FROM Invoices i
        LEFT JOIN Bookings b ON i.booking_id = b.booking_id
        LEFT JOIN Room rm ON b.room_id = rm.room_id
        WHERE i.invoice_id = ?
    ";
    
    $invoice_stmt = $conn->prepare($invoice_sql);
    $invoice_stmt->bind_param('i', $invoice_id);
    $invoice_stmt->execute();
    $invoice_result = $invoice_stmt->get_result();
    $invoice = $invoice_result->fetch_assoc();
    $invoice_stmt->close();
    
    if (!$invoice) {
        throw new Exception('Invoice not found');
    }
    
    // Calculate nights
    $nights = 0;
    if ($invoice['actual_check_in'] && $invoice['actual_check_out']) {
        $start = new DateTime($invoice['actual_check_in']);
        $end = new DateTime($invoice['actual_check_out']);
        $nights = $start->diff($end)->days;
    }
    
    // Fetch room services
    $room_service_total = 0;
    $room_service_sql = "
        SELECT SUM(charge_amount) as total
        FROM BookingRoomService 
        WHERE booking_id = ?
    ";
    $room_service_stmt = $conn->prepare($room_service_sql);
    $room_service_stmt->bind_param('i', $invoice['booking_id']);
    $room_service_stmt->execute();
    $room_service_result = $room_service_stmt->get_result();
    $room_service_data = $room_service_result->fetch_assoc();
    $room_service_total = floatval($room_service_data['total'] ?? 0);
    $room_service_stmt->close();
    
    // Fetch restaurant orders
    $restaurant_total = 0;
    $restaurant_sql = "
        SELECT SUM(final_amount) as total
        FROM RestaurantOrders 
        WHERE booking_id = ?
    ";
    $restaurant_stmt = $conn->prepare($restaurant_sql);
    $restaurant_stmt->bind_param('i', $invoice['booking_id']);
    $restaurant_stmt->execute();
    $restaurant_result = $restaurant_stmt->get_result();
    $restaurant_data = $restaurant_result->fetch_assoc();
    $restaurant_total = floatval($restaurant_data['total'] ?? 0);
    $restaurant_stmt->close();
    
    // Calculate final amount with proper structure
    $room_rate = floatval($invoice['price_per_night'] ?? 0);
    $room_total = $nights * $room_rate;
    $advance_amount = floatval($invoice['advance_amount'] ?? 0);
    
    // Billing calculation
    $subtotal_after_advance = $room_total - $advance_amount;
    $subtotal_with_services = $subtotal_after_advance + $room_service_total;
    $subtotal_with_restaurant = $subtotal_with_services + $restaurant_total;
    $vat_amount = $subtotal_with_restaurant * 0.13;
    $subtotal_with_vat = $subtotal_with_restaurant + $vat_amount;
    $discount_amount = $subtotal_with_vat * 0.10;
    $final_amount = $subtotal_with_vat - $discount_amount;
    
    // Check if payment amount is valid
    $current_balance = $final_amount - $invoice['amount_paid'];
    if ($payment_amount > $current_balance) {
        throw new Exception('Payment amount exceeds balance due');
    }
    
    // Generate transaction ID
    $transaction_id = 'TXN' . date('YmdHis') . rand(1000, 9999);
    
    // Insert payment record
    $payment_sql = "
        INSERT INTO Payments (
            booking_id, 
            customer_id, 
            payment_date, 
            amount, 
            payment_method, 
            transaction_id, 
            status, 
            notes
        ) VALUES (?, ?, NOW(), ?, ?, ?, 'Completed', ?)
    ";
    
    $payment_notes = "Payment for Invoice INV-" . str_pad($invoice_id, 6, '0', STR_PAD_LEFT);
    
    $payment_stmt = $conn->prepare($payment_sql);
    $payment_stmt->bind_param('iidsss', 
        $invoice['booking_id'], 
        $invoice['customer_id'], 
        $payment_amount, 
        $payment_method, 
        $transaction_id, 
        $payment_notes
    );
    $payment_stmt->execute();
    $payment_stmt->close();
    
    // Update invoice
    $new_amount_paid = $invoice['amount_paid'] + $payment_amount;
    $new_balance_due = $final_amount - $new_amount_paid;
    $new_status = ($new_balance_due <= 0) ? 'paid' : 'partial';
    
    $update_invoice_sql = "
        UPDATE Invoices 
        SET amount_paid = ?, 
            balance_due = ?, 
            status = ?,
            total_amount = ?
        WHERE invoice_id = ?
    ";
    
    $update_invoice_stmt = $conn->prepare($update_invoice_sql);
    $update_invoice_stmt->bind_param('ddsdi', 
        $new_amount_paid, 
        $new_balance_due, 
        $new_status, 
        $final_amount, 
        $invoice_id
    );
    $update_invoice_stmt->execute();
    $update_invoice_stmt->close();
    
    // If fully paid, update booking status
    if ($new_balance_due <= 0) {
        $update_booking_sql = "
            UPDATE Bookings 
            SET status = 'completed' 
            WHERE booking_id = ?
        ";
        $update_booking_stmt = $conn->prepare($update_booking_sql);
        $update_booking_stmt->bind_param('i', $invoice['booking_id']);
        $update_booking_stmt->execute();
        $update_booking_stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Redirect with success message
    header('Location: billing.php?success=Payment processed successfully. Transaction ID: ' . $transaction_id);
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    header('Location: billing.php?error=Payment failed: ' . $e->getMessage());
    exit;
}
?>

