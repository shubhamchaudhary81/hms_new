<?php
include_once '../config/configdatabse.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: billing.php');
    exit;
}

// Get form data
$invoice_id = $_POST['invoice_id'] ?? '';
$guest_name = $_POST['guest_name'] ?? '';
$format = $_POST['format'] ?? 'detailed';
$include_logo = isset($_POST['include_logo']);
$include_notes = isset($_POST['include_notes']);

if (empty($invoice_id)) {
    header('Location: billing.php?error=Invalid invoice ID');
    exit;
}

// Fetch invoice data with all related information
$sql = "
    SELECT 
        i.invoice_id,
        i.invoice_date,
        i.total_amount,
        i.amount_paid,
        i.balance_due,
        i.status,
        i.notes,
        i.due_date,
        i.booking_id,
        c.first_name,
        c.last_name,
        c.email,
        c.number,
        b.advance_amount,
        b.actual_check_in,
        b.actual_check_out,
        rm.room_number,
        rt.room_type_name,
        rm.price_per_night,
        r.estimated_total_amount,
        r.requested_check_in_date,
        r.requested_check_out_date
    FROM Invoices i
    LEFT JOIN Bookings b ON i.booking_id = b.booking_id
    LEFT JOIN Reservations r ON b.reservation_id = r.reservation_id
    LEFT JOIN Customers c ON r.customer_id = c.id
    LEFT JOIN Room rm ON b.room_id = rm.room_id
    LEFT JOIN RoomType rt ON rt.room_type_id = rm.room_type
    WHERE i.invoice_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: billing.php?error=Invoice not found');
    exit;
}

$invoice = $result->fetch_assoc();

// Calculate additional values
$nights = 0;
if ($invoice['actual_check_in'] && $invoice['actual_check_out']) {
    $start = new DateTime($invoice['actual_check_in']);
    $end = new DateTime($invoice['actual_check_out']);
    $nights = $start->diff($end)->days;
}

// Fetch room services for this booking
$room_services = [];
$room_service_total = 0;
$room_service_sql = "
    SELECT 
        brs.booking_room_service_id,
        brs.quantity,
        brs.charge_amount,
        brs.service_date,
        brs.status,
        rs.service_name,
        rs.price
    FROM BookingRoomService brs
    JOIN RoomService rs ON brs.room_service_id = rs.room_service_id
    WHERE brs.booking_id = ?
    ORDER BY brs.service_date DESC
";
$room_service_stmt = $conn->prepare($room_service_sql);
$room_service_stmt->bind_param('i', $invoice['booking_id']);
$room_service_stmt->execute();
$room_service_result = $room_service_stmt->get_result();
while ($row = $room_service_result->fetch_assoc()) {
    $room_services[] = $row;
    $room_service_total += floatval($row['charge_amount']);
}
$room_service_stmt->close();

// Fetch restaurant orders for this booking
$restaurant_orders = [];
$restaurant_total = 0;
$restaurant_sql = "
    SELECT 
        ro.order_id,
        ro.final_amount,
        ro.order_date,
        ro.payment_status,
        ro.notes
    FROM RestaurantOrders ro
    WHERE ro.booking_id = ?
    ORDER BY ro.order_date DESC
";
$restaurant_stmt = $conn->prepare($restaurant_sql);
$restaurant_stmt->bind_param('i', $invoice['booking_id']);
$restaurant_stmt->execute();
$restaurant_result = $restaurant_stmt->get_result();
while ($row = $restaurant_result->fetch_assoc()) {
    $restaurant_orders[] = $row;
    $restaurant_total += floatval($row['final_amount']);
}
$restaurant_stmt->close();

// Calculate billing breakdown
$room_rate = floatval($invoice['price_per_night'] ?? 0);
$room_total = $nights * $room_rate;
$advance_amount = floatval($invoice['advance_amount'] ?? 0);

// Billing calculation as per user requirements:
// 1. Total amount minus advance
$subtotal_after_advance = $room_total - $advance_amount;

// 2. Add room services
$subtotal_with_services = $subtotal_after_advance + $room_service_total;

// 3. Add restaurant orders
$subtotal_with_restaurant = $subtotal_with_services + $restaurant_total;

// 4. Add 13% VAT
$vat_amount = $subtotal_with_restaurant * 0.13;

// 5. Add VAT to get subtotal before discount
$subtotal_with_vat = $subtotal_with_restaurant + $vat_amount;

// 6. Apply 10% discount
$discount_amount = $subtotal_with_vat * 0.10;

// 7. Final amount
$final_amount = $subtotal_with_vat - $discount_amount;

// Generate PDF content
$pdf_content = generatePdfContent($invoice, $nights, $room_services, $restaurant_orders, $format, $include_logo, $include_notes, $room_total, $advance_amount, $room_service_total, $restaurant_total, $vat_amount, $discount_amount, $final_amount);

// Set headers for HTML download (printable as PDF)
$filename = "Invoice_INV-" . str_pad($invoice_id, 6, '0', STR_PAD_LEFT) . "_" . date('Y-m-d') . ".html";
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output HTML content that can be printed as PDF
echo generatePrintableHtml($pdf_content);

function generatePdfContent($invoice, $nights, $room_services, $restaurant_orders, $format, $include_logo, $include_notes, $room_total, $advance_amount, $room_service_total, $restaurant_total, $vat_amount, $discount_amount, $final_amount) {
    $content = '';
    
    // Header with logo
    if ($include_logo) {
        $content .= '<div class="header">';
        $content .= '<h1>HIMALAYA HOTEL</h1>';
        $content .= '<p>Professional Hotel Services</p>';
        $content .= '</div>';
    }
    
    // Invoice title
    $content .= '<h2 style="color: #333; margin: 0 0 20px 0; text-align: center;">INVOICE</h2>';
    
    // Invoice and Guest Information
    $content .= '<div class="invoice-info">';
    $content .= '<div class="invoice-details">';
    $content .= '<h3>Invoice Information</h3>';
    $content .= '<div class="info-row"><strong>Invoice #:</strong> INV-' . str_pad($invoice['invoice_id'], 6, '0', STR_PAD_LEFT) . '</div>';
    $content .= '<div class="info-row"><strong>Date:</strong> ' . date('F d, Y', strtotime($invoice['invoice_date'])) . '</div>';
    $content .= '<div class="info-row"><strong>Due Date:</strong> ' . ($invoice['due_date'] ? date('F d, Y', strtotime($invoice['due_date'])) : 'N/A') . '</div>';
    $content .= '<div class="info-row"><strong>Status:</strong> ' . ucfirst($invoice['status']) . '</div>';
    $content .= '<div class="info-row"><strong>Booking #:</strong> ' . ($invoice['booking_id'] ?? 'N/A') . '</div>';
    $content .= '</div>';
    
    $content .= '<div class="guest-details">';
    $content .= '<h3>Guest Information</h3>';
    $content .= '<div class="info-row"><strong>Name:</strong> ' . htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']) . '</div>';
    $content .= '<div class="info-row"><strong>Email:</strong> ' . htmlspecialchars($invoice['email'] ?? 'N/A') . '</div>';
    $content .= '<div class="info-row"><strong>Phone:</strong> ' . htmlspecialchars($invoice['number'] ?? 'N/A') . '</div>';
    $content .= '<div class="info-row"><strong>Room:</strong> ' . htmlspecialchars($invoice['room_number'] ?? 'N/A') . ' - ' . htmlspecialchars($invoice['room_type_name'] ?? 'N/A') . '</div>';
    $content .= '<div class="info-row"><strong>Check-in:</strong> ' . ($invoice['actual_check_in'] ? date('F d, Y', strtotime($invoice['actual_check_in'])) : 'N/A') . '</div>';
    $content .= '<div class="info-row"><strong>Check-out:</strong> ' . ($invoice['actual_check_out'] ? date('F d, Y', strtotime($invoice['actual_check_out'])) : 'N/A') . '</div>';
    $content .= '<div class="info-row"><strong>Nights:</strong> ' . $nights . '</div>';
    $content .= '</div>';
    $content .= '</div>';
    
    // Billing Details Table
    if ($format === 'detailed') {
        $content .= '<h3 style="color: #333; margin: 20px 0 15px 0;">Billing Details</h3>';
        $content .= '<table class="billing-table">';
        $content .= '<thead>';
        $content .= '<tr>';
        $content .= '<th>Description</th>';
        $content .= '<th class="text-right">Rate</th>';
        $content .= '<th class="text-center">Nights</th>';
        $content .= '<th class="text-right">Amount</th>';
        $content .= '</tr>';
        $content .= '</thead>';
        $content .= '<tbody>';
        
        // Room accommodation
        $content .= '<tr>';
        $content .= '<td>Room Accommodation</td>';
        $content .= '<td class="text-right">' . number_format($invoice['price_per_night'] ?? 0, 2) . '</td>';
        $content .= '<td class="text-center">' . $nights . '</td>';
        $content .= '<td class="text-right">' . number_format($room_total, 2) . '</td>';
        $content .= '</tr>';
        
        // Room services
        if (!empty($room_services)) {
            foreach ($room_services as $service) {
                $content .= '<tr>';
                $content .= '<td>Room Service: ' . htmlspecialchars($service['service_name']) . '</td>';
                $content .= '<td class="text-right">' . number_format($service['price'], 2) . '</td>';
                $content .= '<td class="text-center">' . $service['quantity'] . '</td>';
                $content .= '<td class="text-right">' . number_format($service['charge_amount'], 2) . '</td>';
                $content .= '</tr>';
            }
        }
        
        // Restaurant orders
        if (!empty($restaurant_orders)) {
            foreach ($restaurant_orders as $order) {
                $content .= '<tr>';
                $content .= '<td>Restaurant Order #' . $order['order_id'] . '</td>';
                $content .= '<td class="text-right">-</td>';
                $content .= '<td class="text-center">-</td>';
                $content .= '<td class="text-right">' . number_format($order['final_amount'], 2) . '</td>';
                $content .= '</tr>';
            }
        }
        
        $content .= '</tbody>';
        $content .= '</table>';
    }
    
    // Summary Section with proper calculation structure
    $content .= '<div class="summary">';
    $content .= '<table class="summary-table">';
    $content .= '<tr><td style="text-align: right;"><strong>Room Total:</strong></td><td style="text-align: right;">' . number_format($room_total, 2) . '</td></tr>';
    $content .= '<tr><td style="text-align: right;"><strong>Advance Paid:</strong></td><td style="text-align: right;" class="paid">-' . number_format($advance_amount, 2) . '</td></tr>';
    $content .= '<tr style="border-top: 1px solid #ddd;"><td style="text-align: right;"><strong>After Advance:</strong></td><td style="text-align: right;">' . number_format($room_total - $advance_amount, 2) . '</td></tr>';
    
    if ($room_service_total > 0) {
        $content .= '<tr><td style="text-align: right;"><strong>Room Services:</strong></td><td style="text-align: right;">+' . number_format($room_service_total, 2) . '</td></tr>';
    }
    
    if ($restaurant_total > 0) {
        $content .= '<tr><td style="text-align: right;"><strong>Restaurant Orders:</strong></td><td style="text-align: right;">+' . number_format($restaurant_total, 2) . '</td></tr>';
    }
    
    $subtotal_with_restaurant = ($room_total - $advance_amount) + $room_service_total + $restaurant_total;
    $content .= '<tr style="border-top: 1px solid #ddd;"><td style="text-align: right;"><strong>Subtotal:</strong></td><td style="text-align: right;">' . number_format($subtotal_with_restaurant, 2) . '</td></tr>';
    
    $content .= '<tr><td style="text-align: right;"><strong>VAT (13%):</strong></td><td style="text-align: right;">+' . number_format($vat_amount, 2) . '</td></tr>';
    
    $subtotal_with_vat = $subtotal_with_restaurant + $vat_amount;
    $content .= '<tr><td style="text-align: right;"><strong>After VAT:</strong></td><td style="text-align: right;">' . number_format($subtotal_with_vat, 2) . '</td></tr>';
    
    $content .= '<tr><td style="text-align: right;"><strong>Discount (10%):</strong></td><td style="text-align: right;" class="discount">-' . number_format($discount_amount, 2) . '</td></tr>';
    
    $content .= '<tr class="total-row"><td style="text-align: right;"><strong>Final Amount:</strong></td><td style="text-align: right;">' . number_format($final_amount, 2) . '</td></tr>';
    $content .= '<tr><td style="text-align: right;"><strong>Amount Paid:</strong></td><td style="text-align: right;" class="paid">' . number_format($invoice['amount_paid'], 2) . '</td></tr>';
    $content .= '<tr><td style="text-align: right;"><strong>Balance Due:</strong></td><td style="text-align: right;" class="balance">' . number_format($final_amount - $invoice['amount_paid'], 2) . '</td></tr>';
    $content .= '</table>';
    $content .= '</div>';
    
    // Notes Section
    if ($include_notes && !empty($invoice['notes'])) {
        $content .= '<div class="notes">';
        $content .= '<h4 style="color: #333; margin: 0 0 10px 0;">Notes</h4>';
        $content .= '<p style="margin: 0; color: #666;">' . htmlspecialchars($invoice['notes']) . '</p>';
        $content .= '</div>';
    }
    
    // Footer
    $content .= '<div class="footer">';
    $content .= '<p>Thank you for choosing our hotel services!</p>';
    $content .= '<p>For any questions, please contact us at support@himalayahotel.com</p>';
    $content .= '</div>';
    
    return $content;
}

function generatePrintableHtml($html_content) {
    // Generate HTML that can be printed as PDF
    $printable_html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Invoice - Himalaya Hotel</title>
        <style>
            * { box-sizing: border-box; }
            body { 
                font-family: Arial, sans-serif; 
                margin: 0; 
                padding: 20px; 
                background: #fff;
                color: #333;
                line-height: 1.4;
            }
            .invoice-container {
                max-width: 800px;
                margin: 0 auto;
                background: #fff;
                padding: 30px;
                border: 1px solid #ddd;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #8B7355;
            }
            .header h1 {
                color: #8B7355;
                margin: 0 0 5px 0;
                font-size: 24px;
            }
            .header p {
                color: #666;
                margin: 0;
                font-size: 14px;
            }
            .invoice-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 30px;
                flex-wrap: wrap;
            }
            .invoice-details, .guest-details {
                flex: 1;
                min-width: 300px;
            }
            .invoice-details h3, .guest-details h3 {
                color: #333;
                margin: 0 0 15px 0;
                font-size: 16px;
                border-bottom: 1px solid #eee;
                padding-bottom: 5px;
            }
            .info-row {
                margin-bottom: 8px;
                font-size: 14px;
            }
            .info-row strong {
                display: inline-block;
                width: 120px;
                color: #666;
            }
            .billing-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 14px;
            }
            .billing-table th {
                background: #f8f9fa;
                border: 1px solid #ddd;
                padding: 12px;
                text-align: left;
                font-weight: 600;
            }
            .billing-table td {
                border: 1px solid #ddd;
                padding: 12px;
            }
            .billing-table .text-right {
                text-align: right;
            }
            .billing-table .text-center {
                text-align: center;
            }
            .summary {
                border-top: 2px solid #8B7355;
                padding-top: 20px;
                margin-top: 20px;
            }
            .summary-table {
                width: 350px;
                margin-left: auto;
                border-collapse: collapse;
            }
            .summary-table td {
                padding: 8px;
                border: none;
            }
            .summary-table .total-row {
                border-top: 1px solid #ddd;
                font-weight: bold;
                font-size: 16px;
            }
            .summary-table .paid {
                color: #28a745;
            }
            .summary-table .balance {
                color: #dc3545;
                font-weight: bold;
            }
            .summary-table .discount {
                color: #ffc107;
                font-weight: bold;
            }
            .notes {
                margin-top: 30px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 5px;
                font-size: 14px;
            }
            .footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                text-align: center;
                color: #666;
                font-size: 12px;
            }
            .print-controls {
                text-align: center;
                margin-top: 30px;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 8px;
            }
            .print-btn {
                background: #8B7355;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                margin: 0 10px;
            }
            .print-btn:hover {
                background: #7a6347;
            }
            @media print {
                body { margin: 0; padding: 0; }
                .print-controls { display: none; }
                .invoice-container { 
                    border: none; 
                    box-shadow: none; 
                    padding: 20px;
                }
                .header { margin-bottom: 20px; }
            }
        </style>
    </head>
    <body>
        <div class="invoice-container">
            ' . $html_content . '
        </div>
        <div class="print-controls">
            <p><strong>Instructions:</strong> Click "Print as PDF" to save this invoice as a PDF file</p>
            <button class="print-btn" onclick="window.print()">Print as PDF</button>
            <button class="print-btn" onclick="window.close()">Close</button>
        </div>
        <script>
            // Auto-print dialog on load (optional)
            // window.onload = function() {
            //     setTimeout(function() {
            //         window.print();
            //     }, 1000);
            // };
        </script>
    </body>
    </html>';
    
    return $printable_html;
}
?>
