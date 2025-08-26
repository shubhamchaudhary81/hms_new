<?php
include_once '../config/configdatabse.php';
if (!isset($_GET['booking_id'])) {
    die('Booking ID missing.');
}

$headerTitle = "Bookings Management";
$headerSubtitle = "Manage and track bookings with full details, check-in/out, cancellations, and edits.";
$booking_id = intval($_GET['booking_id']);
// Fetch booking, customer, and room info
$stmt = $conn->prepare("SELECT b.*, r.room_number, rt.room_type_name, c.first_name, c.last_name, c.email, c.number, res.requested_check_in_date AS check_in_date, res.estimated_total_amount AS total_amount FROM Bookings b JOIN Room r ON b.room_id = r.room_id JOIN RoomType rt ON r.room_type = rt.room_type_id JOIN Reservations res ON b.reservation_id = res.reservation_id JOIN Customers c ON res.customer_id = c.id WHERE b.booking_id = ?");
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();
if (!$booking) { die('Booking not found.'); }

// Fetch restaurant charges for this booking (unpaid only)
$restaurant_total = 0;
$restaurant_paid = 0;
$restaurant_due = 0;
$restaurant_orders = [];
$rest_stmt = $conn->prepare("SELECT order_id, final_amount, payment_status FROM RestaurantOrders WHERE booking_id = ?");
$rest_stmt->bind_param('i', $booking_id);
$rest_stmt->execute();
$rest_result = $rest_stmt->get_result();
while ($row = $rest_result->fetch_assoc()) {
    $restaurant_total += floatval($row['final_amount']);
    if (strtolower($row['payment_status']) === 'paid') {
        $restaurant_paid += floatval($row['final_amount']);
    } else {
        $restaurant_due += floatval($row['final_amount']);
    }
    $restaurant_orders[] = $row;
}
$rest_stmt->close();

// Fetch room service charges for this booking
$room_service_total = 0;
$room_service_orders = [];
$room_service_stmt = $conn->prepare("SELECT brs.booking_room_service_id, brs.charge_amount, brs.status, rs.service_name FROM BookingRoomService brs JOIN RoomService rs ON brs.room_service_id = rs.room_service_id WHERE brs.booking_id = ?");
$room_service_stmt->bind_param('i', $booking_id);
$room_service_stmt->execute();
$room_service_result = $room_service_stmt->get_result();
while ($row = $room_service_result->fetch_assoc()) {
    $room_service_total += floatval($row['charge_amount']);
    $room_service_orders[] = $row;
}
$room_service_stmt->close();

// Calculate booking charge and advance
$booking_charge = floatval($booking['total_amount'] ?? 0); // If you have a total booking amount field
$booking_advance = floatval($booking['advance_amount'] ?? 0);
if ($booking_charge == 0) {
    // Fallback: use advance as booking charge if no total field
    $booking_charge = $booking_advance;
}

// Calculate total due
$total_due = ($booking_charge - $booking_advance) + $restaurant_due + $room_service_total;
if ($total_due < 0) $total_due = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $methods = isset($_POST['method']) ? $_POST['method'] : [];
    $amounts = isset($_POST['amount']) ? $_POST['amount'] : [];
    $transactions = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : [];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $now = date('Y-m-d H:i:s');
    $payment_breakdown = [];
    $total_paid = 0;
    if (is_array($methods) && is_array($amounts)) {
        foreach ($methods as $i => $m) {
            $amt = isset($amounts[$i]) ? floatval($amounts[$i]) : 0;
            $trans_id = isset($transactions[$i]) ? trim($transactions[$i]) : '';
            if ($m && $amt > 0) {
                $payment_breakdown[] = ['method' => $m, 'amount' => $amt, 'transaction_id' => $trans_id];
                $total_paid += $amt;
            }
        }
    }
    // Validate total matches due
    if (abs($total_paid - $total_due) > 0.01) {
        $error = 'Total payment does not match the amount due!';
    } else {
        // Insert each payment into Payments table
        $customer_id = $booking['customer_id'] ?? null;
        if (!$customer_id && isset($booking['reservation_id'])) {
            // Fetch from Reservations if not present
            $res_stmt = $conn->prepare("SELECT customer_id FROM Reservations WHERE reservation_id = ?");
            $res_stmt->bind_param('i', $booking['reservation_id']);
            $res_stmt->execute();
            $res_stmt->bind_result($customer_id);
            $res_stmt->fetch();
            $res_stmt->close();
        }
     $all_success = true;
foreach ($payment_breakdown as $pay) {
    $method = $pay['method'];
    $amt = $pay['amount'];
    $transaction_id = $pay['transaction_id'];
    $currency = 'NPR';
    $status = 'Completed';
    $payment_notes = $notes;

    // Match payment to a due restaurant order if any unpaid
    $restaurant_order_id = null;
    foreach ($restaurant_orders as $key => $order) {
        if (strtolower($order['payment_status']) !== 'paid' && $amt >= floatval($order['final_amount'])) {
            $restaurant_order_id = $order['order_id'];
            // Optional: mark as paid (you can update status in the RestaurantOrders table)
            break;
        }
    }

    // Now include restaurant_order_id in the query
    $stmt = $conn->prepare("INSERT INTO Payments (booking_id, restaurant_order_id, customer_id, amount, payment_method, transaction_id, currency, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) { $all_success = false; $error = 'DB error: ' . $conn->error; break; }

    $stmt->bind_param('iiidsssss', $booking_id, $restaurant_order_id, $customer_id, $amt, $method, $transaction_id, $currency, $status, $payment_notes);
    if (!$stmt->execute()) {
        $all_success = false;
        $error = 'Payment insert error: ' . $stmt->error;
        $stmt->close();
        break;
    }
    $stmt->close();
}
        if ($all_success) {
            // Update booking: set actual_check_out and notes (DO NOT update advance_amount)
            $stmt = $conn->prepare("UPDATE Bookings SET actual_check_out = ?, notes = ? WHERE booking_id = ?");
            $stmt->bind_param('ssi', $now, $notes, $booking_id);
            $stmt->execute();
            $stmt->close();
            // Set room to available
            $conn->query("UPDATE Room SET status = 'available' WHERE room_id = " . intval($booking['room_id']));
            // Mark all restaurant orders as paid for this booking
            $conn->query("UPDATE RestaurantOrders SET payment_status = 'paid' WHERE booking_id = " . intval($booking_id));
            header('Location: payment_success.php?booking_id=' . $booking_id);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check Out & Payment | Himalaya Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link rel="stylesheet" href="../css/admin/content.css">
    <style>
        :root {
            --primary-color: #8d6e63;
            --secondary-color: #6D4C41;
            --accent-color: #ff6b6b;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            /* background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%); */
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            /* min-height: 100vh; */
            /* padding: 20px 0; */
        }
        
        .text {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 3.5rem;
            /* text-align: center; */
            margin-bottom: -2rem;
        }
        
        .card {
            border: none;
            width: 1200px;
            margin-left: -100px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            margin-bottom: 2rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        }
        
        .card-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .guest-info {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .guest-info h5 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }
        
        .info-icon {
            color: var(--primary-color);
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .guest-info p {
            margin-bottom: 0.8rem;
            font-size: 1rem;
            line-height: 1.6;
        }
        
        .guest-info p:last-child {
            margin-bottom: 0;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 15px 18px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(141, 110, 99, 0.15);
            outline: none;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            padding: 15px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(141, 110, 99, 0.3);
        }
        
        .price-display {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .section-title {
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 700;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .logo img {
            height: 60px;
        }
        
        /* Payment Breakdown Styling */
        .payment-breakdown {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
        }
        
        .payment-breakdown .d-flex {
            padding: 0.8rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .payment-breakdown .d-flex:last-child {
            border-bottom: none;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .payment-breakdown hr {
            margin: 1rem 0;
            border-color: var(--primary-color);
            opacity: 0.3;
        }
        
        /* Table Styling */
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
        }
        
        .table tbody td {
            padding: 1rem;
            border-color: #e9ecef;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Badge Styling */
        .badge {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 8px;
        }
        
        /* Room Services Table */
        .room-services-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-top: 1rem;
        }
        
        .room-services-table .table {
            margin-bottom: 0;
        }
        
        .room-services-table h6 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        /* Button Styling */
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-danger {
            background: #dc3545;
            border-color: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
            border-color: #bd2130;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            border-color: #545b62;
            transform: translateY(-1px);
        }
        
        /* Alert Styling */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            
            .guest-info {
                padding: 1.5rem;
            }
            
            .text {
                font-size: 2.5rem;
            }
            
            .payment-breakdown {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
<div class="container py-5" >
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="logo">
                <h2 class="text" ><i class="fas fa-mountain me-2"></i>Himalaya Hotel</h2>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0 text-white"><i class="fas fa-receipt me-2"></i>Check Out & Payment</h4>
                </div>
                
                <div class="card-body p-4">
                    <div class="guest-info">
                        <h5 class="fw-bold mb-4">Booking #<?= htmlspecialchars($booking['booking_id']) ?></h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <p class="mb-2"><span class="info-icon"><i class="fas fa-user"></i></span> 
                                    <strong>Guest:</strong> <?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?>
                                </p>
                                <p class="mb-2"><span class="info-icon"><i class="fas fa-envelope"></i></span> 
                                    <?= htmlspecialchars($booking['email']) ?>
                                </p>
                                <p class="mb-0"><span class="info-icon"><i class="fas fa-phone"></i></span> 
                                    <?= htmlspecialchars($booking['number']) ?>
                                </p>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <p class="mb-2"><span class="info-icon"><i class="fas fa-bed"></i></span> 
                                    <strong>Room Type:</strong> <?= htmlspecialchars($booking['room_type_name']) ?>
                                </p>
                                <p class="mb-2"><span class="info-icon"><i class="fas fa-door-open"></i></span> 
                                    <strong>Room Number:</strong> <?= htmlspecialchars($booking['room_number']) ?>
                                </p>
                                <p class="mb-0"><span class="info-icon"><i class="fas fa-calendar-alt"></i></span> 
                                    <strong>Check-in:</strong> <?= date('M j, Y', strtotime($booking['check_in_date'])) ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if (!empty($room_service_orders)): ?>
                        <div class="room-services-table">
                            <h6 class="fw-bold text-primary mb-2"><i class="fas fa-concierge-bell me-2"></i>Room Services Used</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Service</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($room_service_orders as $service): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($service['service_name']) ?></td>
                                            <td><span class="badge bg-<?= $service['status'] === 'Completed' ? 'success' : 'warning' ?>"><?= htmlspecialchars($service['status']) ?></span></td>
                                            <td>रु<?= number_format($service['charge_amount'], 2) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>
                        </div>
                    </div>
                    
                    <form method="post" id="paymentForm">
                        <h6 class="section-title">Payment Breakdown</h6>
                        <div class="payment-breakdown">
                            <div class="d-flex justify-content-between">
                                <span>Booking Charge:</span>
                                <span>रु<?= number_format($booking_charge,2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Booking Advance Paid:</span>
                                <span>- रु<?= number_format($booking_advance,2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Unpaid Restaurant Charges:</span>
                                <span>रु<?= number_format($restaurant_due,2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Room Service Charges:</span>
                                <span>रु<?= number_format($room_service_total,2) ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Final Amount Due:</span>
                                <span id="finalDue">रु<?= number_format($total_due,2) ?></span>
                            </div>
                        </div>
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
                        <?php endif; ?>
                        <h6 class="section-title">Payment Methods</h6>
                        <div class="mb-4">
                            <table class="table table-bordered align-middle" id="paymentTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:30%">Method</th>
                                        <th style="width:25%">Amount (रु)</th>
                                        <th style="width:30%">Transaction ID</th>
                                        <th style="width:15%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="method[]" class="form-select pay-method" required onchange="toggleTransactionField(this)">
                                                <option value="" disabled selected>Select method</option>
                                                <option value="cash">Cash</option>
                                                <option value="card">Credit/Debit Card</option>
                                                <option value="online">Online Payment</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="amount[]" class="form-control pay-amount" min="0" step="0.01" required>
                                        </td>
                                        <td>
                                            <input type="text" name="transaction_id[]" class="form-control transaction-id-field" style="display:none;" placeholder="Transaction/Ref ID">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-row" disabled><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-secondary btn-sm" id="addPaymentRow"><i class="fas fa-plus"></i> Add Payment Method</button>
                            <div class="mt-2 text-end">
                                <span class="fw-bold">Total Entered: </span><span id="enteredTotal">रु0.00</span>
                            </div>
                        </div>
                        <h6 class="section-title">Additional Notes</h6>
                        <div class="mb-4">
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any special instructions or comments..."></textarea>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-submit">
                                <i class="fas fa-check-circle me-2"></i>Complete Payment & Check Out
                            </button>
                        </div>
                    </form>
                </div>
<!--                 
                <div class="card-footer bg-light border-0 text-center py-3">
                    <p class="text-muted mb-0">Thank you for staying with us at Himalaya Hotel</p>
                </div>
            </div>
            
            <div class="text-center mt-4 text-muted">
                <p>Need assistance? Call us at +977-1-1234567 or email frontdesk@himalayahotel.com</p>
            </div> -->
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Enhance select options with icons
    document.addEventListener('DOMContentLoaded', function() {
        const paymentSelect = document.querySelector('select[name="method"]');
        paymentSelect.innerHTML = `
            <option value="" disabled selected>Select payment method</option>
            <option value="cash"><i class="fas fa-money-bill-wave me-2"></i> Cash</option>
            <option value="card"><i class="fas fa-credit-card me-2"></i> Credit/Debit Card</option>
            <option value="online"><i class="fas fa-globe me-2"></i> Online Payment</option>
        `;
    });
</script>
<script>
// Payment row logic
function updateEnteredTotal() {
    let sum = 0;
    document.querySelectorAll('.pay-amount').forEach(function(input) {
        sum += parseFloat(input.value) || 0;
    });
    document.getElementById('enteredTotal').textContent = 'रु' + sum.toFixed(2);
    // Optionally, highlight if not matching due
    let due = <?= json_encode($total_due) ?>;
    document.getElementById('enteredTotal').style.color = (Math.abs(sum - due) < 0.01) ? '#388e3c' : '#d32f2f';
}
function toggleTransactionField(select) {
    var row = select.closest('tr');
    var transField = row.querySelector('.transaction-id-field');
    if (select.value === 'card' || select.value === 'online') {
        transField.style.display = '';
        transField.required = true;
    } else {
        transField.style.display = 'none';
        transField.required = false;
        transField.value = '';
    }
}
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('pay-amount')) updateEnteredTotal();
});
document.getElementById('addPaymentRow').onclick = function() {
    let row = document.createElement('tr');
    row.innerHTML = `<td><select name="method[]" class="form-select pay-method" required onchange="toggleTransactionField(this)"><option value="" disabled selected>Select method</option><option value="cash">Cash</option><option value="card">Credit/Debit Card</option><option value="online">Online Payment</option></select></td><td><input type="number" name="amount[]" class="form-control pay-amount" min="0" step="0.01" required></td><td><input type="text" name="transaction_id[]" class="form-control transaction-id-field" style="display:none;" placeholder="Transaction/Ref ID"></td><td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>`;
    document.querySelector('#paymentTable tbody').appendChild(row);
};
document.getElementById('paymentTable').addEventListener('click', function(e) {
    if (e.target.closest('.remove-row')) {
        let row = e.target.closest('tr');
        if (document.querySelectorAll('#paymentTable tbody tr').length > 1) row.remove();
        updateEnteredTotal();
    }
});
document.getElementById('paymentTable').addEventListener('change', function(e) {
    if (e.target.classList.contains('pay-method')) toggleTransactionField(e.target);
});
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    let sum = 0;
    document.querySelectorAll('.pay-amount').forEach(function(input) { sum += parseFloat(input.value) || 0; });
    let due = <?= json_encode($total_due) ?>;
    if (Math.abs(sum - due) > 0.01) {
        alert('Total payment entered does not match the amount due!');
        e.preventDefault();
    }
});
updateEnteredTotal();
</script>
</body>
</html>