<?php
session_start();
include_once '../config/configdatabse.php';
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
if (!$booking_id) {
    die('<div class="alert alert-danger m-5">Booking ID missing.</div>');
}
// Fetch booking, customer, reservation
$stmt = $conn->prepare("SELECT b.*, c.id AS customer_id, c.first_name, c.last_name, c.email, c.number, r.requested_check_in_date, r.requested_check_out_date, r.num_guests, r.estimated_total_amount, rm.room_number, rt.room_type_name FROM Bookings b JOIN Reservations r ON b.reservation_id = r.reservation_id JOIN Customers c ON r.customer_id = c.id JOIN Room rm ON b.room_id = rm.room_id JOIN RoomType rt ON rm.room_type = rt.room_type_id WHERE b.booking_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$booking) die('<div class="alert alert-danger m-5">Booking not found.</div>');
$customer_id = $booking['customer_id'] ?? $booking['customer_id'];

// Room charge from reservation
$room_total = isset($booking['estimated_total_amount']) ? floatval($booking['estimated_total_amount']) : 0;
$advance_paid = floatval($booking['advance_amount'] ?? 0);
// Restaurant orders
$restaurant_total = 0;
$restaurant_orders = [];
$rest_stmt = $conn->prepare("SELECT order_id, final_amount, payment_status, order_date FROM RestaurantOrders WHERE booking_id = ?");
$rest_stmt->bind_param('i', $booking_id);
$rest_stmt->execute();
$rest_result = $rest_stmt->get_result();
while ($row = $rest_result->fetch_assoc()) {
    $restaurant_total += floatval($row['final_amount']);
    $restaurant_orders[] = $row;
}
$rest_stmt->close();
// Room services
$services_total = 0;
$room_services = [];
$serv_stmt = $conn->prepare("SELECT brs.booking_room_service_id, brs.charge_amount, brs.quantity, brs.status, rs.service_name, rs.price FROM BookingRoomService brs JOIN RoomService rs ON brs.room_service_id = rs.room_service_id WHERE brs.booking_id = ?");
$serv_stmt->bind_param('i', $booking_id);
$serv_stmt->execute();
$serv_result = $serv_stmt->get_result();
while ($row = $serv_result->fetch_assoc()) {
    $services_total += floatval($row['charge_amount']);
    $room_services[] = $row;
}
$serv_stmt->close();
// Payments
$payments_total = 0;
$pay_stmt = $conn->prepare("SELECT SUM(amount) AS total FROM Payments WHERE booking_id = ?");
$pay_stmt->bind_param('i', $booking_id);
$pay_stmt->execute();
$pay_stmt->bind_result($payments_total);
$pay_stmt->fetch();
$pay_stmt->close();

// Fetch or create invoice
$inv_stmt = $conn->prepare("SELECT * FROM invoices WHERE booking_id = ?");
$inv_stmt->bind_param('i', $booking_id);
$inv_stmt->execute();
$invoice = $inv_stmt->get_result()->fetch_assoc();
$inv_stmt->close();
if (!$invoice) {
    // Calculate totals (room + extras - advance)
    $total_amount = $room_total + floatval($restaurant_total) + floatval($services_total) - $advance_paid;
    if ($total_amount < 0) $total_amount = 0;
    $balance_due = $total_amount - floatval($payments_total);
    if ($balance_due < 0) $balance_due = 0;
    // Insert invoice
    $ins_stmt = $conn->prepare("INSERT INTO invoices (booking_id, customer_id, total_amount, amount_paid, balance_due, status, notes, invoice_date) VALUES (?, ?, ?, ?, ?, 'Generated', '', NOW())");
    $ins_stmt->bind_param('iidds', $booking_id, $booking['customer_id'], $total_amount, $payments_total, $balance_due);
    $ins_stmt->execute();
    $ins_stmt->close();
    // Fetch the new invoice
    $inv_stmt = $conn->prepare("SELECT * FROM invoices WHERE booking_id = ?");
    $inv_stmt->bind_param('i', $booking_id);
    $inv_stmt->execute();
    $invoice = $inv_stmt->get_result()->fetch_assoc();
    $inv_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= htmlspecialchars($invoice['invoice_id']) ?> | Himalaya Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .invoice-box { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 40px; margin: 40px auto; max-width: 900px; }
        .invoice-title { font-size: 2.2rem; color: #3a5a78; font-weight: 700; }
        .print-btn { margin-top: 32px; }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .table-sm th, .table-sm td {
            padding: 0.75rem;
            font-size: 0.9rem;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
        }
        
        .text-primary {
            color: #3a5a78 !important;
        }
        
        .fw-bold {
            font-weight: 700 !important;
        }
        
        @media print {
            .print-btn, .back-btn { display: none !important; }
            body { background: #fff !important; }
            .invoice-box { box-shadow: none !important; margin: 0 !important; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 p-0 d-print-none">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="py-5">
                <a href="javascript:history.back()" class="btn btn-outline-secondary mb-3 back-btn d-print-none"><i class="fa fa-arrow-left"></i> Back</a>
                <div class="invoice-box">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <div class="invoice-title">Himalaya Hotel</div>
                            <div class="text-muted">Invoice #<?= htmlspecialchars($invoice['invoice_id']) ?> | Date: <?= date('M d, Y', strtotime($invoice['invoice_date'])) ?></div>
                        </div>
                        <img src="../assets/images/hotel-exterior.jpg" alt="Hotel Logo" style="height:60px; border-radius:8px;">
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Billed To</h6>
                            <div><strong><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></strong></div>
                            <div><?= htmlspecialchars($booking['email']) ?></div>
                            <div><?= htmlspecialchars($booking['number']) ?></div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="fw-bold">Booking Details</h6>
                            <div>Room: <?= htmlspecialchars($booking['room_number']) ?> (<?= htmlspecialchars($booking['room_type_name']) ?>)</div>
                            <div>Check-in: <?= date('M d, Y', strtotime($booking['requested_check_in_date'])) ?></div>
                            <div>Check-out: <?= date('M d, Y', strtotime($booking['requested_check_out_date'])) ?></div>
                            <div>Guests: <?= htmlspecialchars($booking['num_guests']) ?></div>
                        </div>
                    </div>
                    <h6 class="fw-bold">Invoice Breakdown</h6>
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th>Room Charge</th><td class="text-end">रु<?= number_format($room_total,2) ?></td></tr>
                            <tr><th>Advance Paid</th><td class="text-end">- रु<?= number_format($advance_paid,2) ?></td></tr>
                            <tr><th>Restaurant Orders</th><td class="text-end">रु<?= number_format($restaurant_total,2) ?></td></tr>
                            <tr><th>Room Services</th><td class="text-end">रु<?= number_format($services_total,2) ?></td></tr>
                            <tr class="fw-bold"><th>Total Amount</th><td class="text-end">रु<?= number_format($invoice['total_amount'],2) ?></td></tr>
                            <tr><th>Amount Paid</th><td class="text-end">रु<?= number_format($invoice['amount_paid'],2) ?></td></tr>
                            <tr><th>Balance Due</th><td class="text-end text-danger">रु<?= number_format($invoice['balance_due'],2) ?></td></tr>
                        </tbody>
                    </table>
                    
                    <?php if (!empty($room_services)): ?>
                    <div class="mt-4">
                        <h6 class="fw-bold text-primary"><i class="fas fa-concierge-bell me-2"></i>Room Services Details</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Service</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($room_services as $service): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($service['service_name']) ?></td>
                                        <td><?= htmlspecialchars($service['quantity']) ?></td>
                                        <td>रु<?= number_format($service['price'], 2) ?></td>
                                        <td>रु<?= number_format($service['charge_amount'], 2) ?></td>
                                        <td><span class="badge bg-<?= $service['status'] === 'Completed' ? 'success' : 'warning' ?>"><?= htmlspecialchars($service['status']) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($restaurant_orders)): ?>
                    <div class="mt-4">
                        <h6 class="fw-bold text-primary"><i class="fas fa-utensils me-2"></i>Restaurant Orders Details</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Amount</th>
                                        <th>Payment Status</th>
                                        <th>Order Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($restaurant_orders as $order): ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($order['order_id']) ?></td>
                                        <td>रु<?= number_format($order['final_amount'], 2) ?></td>
                                        <td><span class="badge bg-<?= strtolower($order['payment_status']) === 'paid' ? 'success' : 'warning' ?>"><?= htmlspecialchars($order['payment_status']) ?></span></td>
                                        <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Payment Status</h6>
                            <div class="fs-5 fw-bold <?= $invoice['balance_due'] > 0 ? 'text-danger' : 'text-success' ?>">
                                <?= $invoice['balance_due'] > 0 ? 'Partial Payment' : 'Paid in Full' ?>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <button class="btn btn-outline-primary print-btn" onclick="window.print()"><i class="fas fa-print me-2"></i>Print Invoice</button>
                        </div>
                    </div>
                  
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html> 