<?php
session_start();
include_once '../config/configdatabse.php';
$invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
$invoice = null;
$booking = null;
$customer = null;
$restaurant_orders = [];
$room_services = [];
$room_total = 0;
$restaurant_total = 0;
$services_total = 0;
$advance_paid = 0;

if ($invoice_id) {
    // Fetch invoice, booking, customer
    $sql = "SELECT i.*, b.booking_id, b.room_id, b.reservation_id, b.advance_amount, b.notes AS booking_notes, b.actual_check_in, b.actual_check_out, c.first_name, c.last_name, c.email, c.number, r.requested_check_in_date, r.requested_check_out_date, r.num_guests, rm.room_number, rt.room_type_name
            FROM invoices i
            LEFT JOIN Bookings b ON i.booking_id = b.booking_id
            LEFT JOIN Reservations r ON b.reservation_id = r.reservation_id
            LEFT JOIN Customers c ON i.customer_id = c.id
            LEFT JOIN Room rm ON b.room_id = rm.room_id
            LEFT JOIN RoomType rt ON rm.room_type = rt.room_type_id
            WHERE i.invoice_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();
    $stmt->close();

    if ($invoice) {
        $room_total = isset($invoice['estimated_total_amount']) ? floatval($invoice['estimated_total_amount']) : 0;
        $advance_paid = floatval($invoice['advance_amount'] ?? 0);
        // Restaurant orders
        $rest_stmt = $conn->prepare("SELECT order_id, final_amount, payment_status, order_date FROM RestaurantOrders WHERE booking_id = ?");
        $rest_stmt->bind_param('i', $invoice['booking_id']);
        $rest_stmt->execute();
        $rest_result = $rest_stmt->get_result();
        while ($row = $rest_result->fetch_assoc()) {
            $restaurant_total += floatval($row['final_amount']);
            $restaurant_orders[] = $row;
        }
        $rest_stmt->close();
        // Room services
        $serv_stmt = $conn->prepare("SELECT brs.booking_room_service_id, brs.charge_amount, brs.quantity, brs.status, rs.service_name, rs.price FROM BookingRoomService brs JOIN RoomService rs ON brs.room_service_id = rs.room_service_id WHERE brs.booking_id = ?");
        $serv_stmt->bind_param('i', $invoice['booking_id']);
        $serv_stmt->execute();
        $serv_result = $serv_stmt->get_result();
        while ($row = $serv_result->fetch_assoc()) {
            $services_total += floatval($row['charge_amount']);
            $room_services[] = $row;
        }
        $serv_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Detail | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .invoice-box { background: #fff; border-radius: 16px; box-shadow: 0 6px 32px rgba(0,0,0,0.10); padding: 48px; margin: 48px auto; max-width: 950px; }
        .invoice-title { font-size: 2.4rem; color: #3a5a78; font-weight: 700; letter-spacing: 1px; }
        .section-title { font-size: 1.2rem; color: #6c757d; font-weight: 700; margin-top: 2.5rem; margin-bottom: 1rem; letter-spacing: 1px; border-bottom: 2px solid #e9ecef; padding-bottom: 0.5rem; }
        .badge-status { font-size: 1rem; padding: 0.5em 1.2em; border-radius: 8px; font-weight: 600; }
        .badge-success { background: #4caf50; color: #fff; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #e53935; color: #fff; }
        .info-label { color: #6c757d; font-weight: 600; }
        .info-value { font-weight: 500; }
        .table th { background: #f8f9fa; font-weight: 600; color: #495057; }
        .table-sm th, .table-sm td { padding: 0.75rem; font-size: 0.97rem; }
        .print-btn { margin-top: 32px; }
        .invoice-summary-table td, .invoice-summary-table th { font-size: 1.08rem; }
        .fw-bold { font-weight: 700 !important; }
        .text-primary { color: #3a5a78 !important; }
        .text-green { color: #388e3c !important; }
        .text-red { color: #e53935 !important; }
        @media (max-width: 900px) {
            .invoice-box { padding: 24px; }
        }
        @media (max-width: 600px) {
            .invoice-box { padding: 8px; }
            .invoice-title { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 p-0">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="py-5">
                <?php if (!$invoice): ?>
                    <div class="alert alert-danger">Invoice not found.</div>
                <?php else: ?>
                <div class="invoice-box">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <div class="invoice-title">Invoice #<?= htmlspecialchars($invoice['invoice_id']) ?></div>
                            <div class="text-muted">Date: <?= date('M d, Y', strtotime($invoice['invoice_date'])) ?></div>
                        </div>
                        <button class="btn btn-outline-primary print-btn" onclick="window.print()"><i class="fas fa-print me-2"></i>Print</button>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-2"><span class="info-label">Customer:</span> <span class="info-value"><?= htmlspecialchars(($invoice['first_name'] ?? '') . ' ' . ($invoice['last_name'] ?? '')) ?></span></div>
                            <div class="mb-2"><span class="info-label">Email:</span> <span class="info-value"><?= htmlspecialchars($invoice['email'] ?? '-') ?></span></div>
                            <div class="mb-2"><span class="info-label">Phone:</span> <span class="info-value"><?= htmlspecialchars($invoice['number'] ?? '-') ?></span></div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="mb-2"><span class="info-label">Room:</span> <span class="info-value"><?= htmlspecialchars($invoice['room_number'] ?? '-') ?> (<?= htmlspecialchars($invoice['room_type_name'] ?? '-') ?>)</span></div>
                            <div class="mb-2"><span class="info-label">Check-in:</span> <span class="info-value"><?= isset($invoice['requested_check_in_date']) ? date('M d, Y', strtotime($invoice['requested_check_in_date'])) : '-' ?></span></div>
                            <div class="mb-2"><span class="info-label">Check-out:</span> <span class="info-value"><?= isset($invoice['requested_check_out_date']) ? date('M d, Y', strtotime($invoice['requested_check_out_date'])) : '-' ?></span></div>
                            <div class="mb-2"><span class="info-label">Guests:</span> <span class="info-value"><?= htmlspecialchars($invoice['num_guests'] ?? '-') ?></span></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <span class="info-label">Payment Status:</span> 
                        <?php if ($invoice['balance_due'] > 0): ?>
                            <span class="badge badge-warning badge-status">Partial Payment</span>
                        <?php else: ?>
                            <span class="badge badge-success badge-status">Paid in Full</span>
                        <?php endif; ?>
                    </div>
                    <div class="section-title"><i class="fas fa-list-alt me-2"></i>Invoice Breakdown</div>
                    <table class="table table-bordered invoice-summary-table mb-4">
                        <tbody>
                            <tr><th>Room Charge</th><td class="text-end">रु<?= number_format($room_total,2) ?></td></tr>
                            <tr><th>Advance Paid</th><td class="text-end">- रु<?= number_format($advance_paid,2) ?></td></tr>
                            <tr><th>Restaurant Orders</th><td class="text-end">रु<?= number_format($restaurant_total,2) ?></td></tr>
                            <tr><th>Room Services</th><td class="text-end">रु<?= number_format($services_total,2) ?></td></tr>
                            <tr class="fw-bold"><th>Total Amount</th><td class="text-end">रु<?= number_format($invoice['total_amount'],2) ?></td></tr>
                            <tr><th>Amount Paid</th><td class="text-end">रु<?= number_format($invoice['amount_paid'],2) ?></td></tr>
                            <tr><th>Balance Due</th><td class="text-end text-red">रु<?= number_format($invoice['balance_due'],2) ?></td></tr>
                        </tbody>
                    </table>
                    <?php if (!empty($room_services)): ?>
                    <div class="section-title"><i class="fas fa-concierge-bell me-2"></i>Room Services Details</div>
                    <div class="table-responsive mb-4">
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
                                    <td><span class="badge <?= $service['status'] === 'Completed' ? 'badge-success' : 'badge-warning' ?> badge-status"><?= htmlspecialchars($service['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($restaurant_orders)): ?>
                    <div class="section-title"><i class="fas fa-utensils me-2"></i>Restaurant Orders Details</div>
                    <div class="table-responsive mb-4">
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
                                    <td><span class="badge <?= strtolower($order['payment_status']) === 'paid' ? 'badge-success' : 'badge-warning' ?> badge-status"><?= htmlspecialchars($order['payment_status']) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    <div class="section-title"><i class="fas fa-info-circle me-2"></i>Other Details</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2"><span class="info-label">Invoice Notes:</span> <span class="info-value"><?= htmlspecialchars($invoice['notes'] ?? '-') ?></span></div>
                            <div class="mb-2"><span class="info-label">Booking Notes:</span> <span class="info-value"><?= htmlspecialchars($invoice['booking_notes'] ?? '-') ?></span></div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="mb-2"><span class="info-label">Created:</span> <span class="info-value"><?= htmlspecialchars($invoice['invoice_date']) ?></span></div>
                            <div class="mb-2"><span class="info-label">Check-in Actual:</span> <span class="info-value"><?= isset($invoice['actual_check_in']) ? date('M d, Y H:i', strtotime($invoice['actual_check_in'])) : '-' ?></span></div>
                            <div class="mb-2"><span class="info-label">Check-out Actual:</span> <span class="info-value"><?= isset($invoice['actual_check_out']) ? date('M d, Y H:i', strtotime($invoice['actual_check_out'])) : '-' ?></span></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html> 