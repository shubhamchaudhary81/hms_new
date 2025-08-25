<?php
session_start();
include_once '../config/configdatabse.php';

// Fetch all bookings with related info and invoice balance_due
$bookings_query = "
SELECT 
    b.booking_id, b.actual_check_in, b.actual_check_out, b.status AS booking_status, b.advance_amount, b.notes,
    r.reservation_id, r.requested_check_in_date, r.requested_check_out_date, r.num_guests, r.status AS reservation_status,
    c.first_name, c.last_name, c.email, c.number,
    rm.room_number, rt.room_type_name,
    inv.balance_due
FROM Bookings b
JOIN Reservations r ON b.reservation_id = r.reservation_id
JOIN Customers c ON r.customer_id = c.id
JOIN Room rm ON b.room_id = rm.room_id
JOIN RoomType rt ON rm.room_type = rt.room_type_id
LEFT JOIN invoices inv ON inv.booking_id = b.booking_id
ORDER BY b.booking_id ASC
";
$bookings = $conn->query($bookings_query);
if (!$bookings) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Himalaya Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/adminbooking.css">
    <link rel="stylesheet" href="adminsidebar.css">
</head>
<body>
    <?php include_once 'sidebar.php'; ?>

<div class="content">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="booking-header">
            <h2><i class="bi bi-journal-bookmark"></i> All Bookings</h2>
            <div class="d-flex align-items-center gap-2">
                <button id="showCheckIn" class="btn btn-outline-primary btn-sm">Check In</button>
                <button id="showCheckOut" class="btn btn-outline-success btn-sm">Check Out</button>
                <form class="search-box ms-2" method="get" action="">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" name="search" placeholder="Search bookings..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="booking-summary">
            <div class="summary-card total">
                <div class="title">Total Bookings</div>
                <div class="value">
                    <?php $total = $conn->query("SELECT COUNT(*) FROM Bookings")->fetch_row()[0]; echo $total; ?>
                </div>
            </div>
            <div class="summary-card confirmed">
                <div class="title">Confirmed</div>
                <div class="value">
                    <?php $confirmed = $conn->query("SELECT COUNT(*) FROM Bookings WHERE status='confirmed'")->fetch_row()[0]; echo $confirmed; ?>
                </div>
            </div>
            <div class="summary-card completed">
                <div class="title">Completed</div>
                <div class="value">
                    <?php $completed = $conn->query("SELECT COUNT(*) FROM Bookings WHERE status='completed'")->fetch_row()[0]; echo $completed; ?>
                </div>
            </div>
            <div class="summary-card cancelled">
                <div class="title">Cancelled</div>
                <div class="value">
                    <?php $cancelled = $conn->query("SELECT COUNT(*) FROM Bookings WHERE status='cancelled'")->fetch_row()[0]; echo $cancelled; ?>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="card-container">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Room</th>
                            <th>Room Type</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Status</th>
                            <th>Advance</th>
                            <th>Contact</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings->num_rows > 0): while ($row = $bookings->fetch_assoc()): ?>
                        <tr data-checkin="<?= empty($row['actual_check_out']) ? '1' : '0' ?>">
                            <td><strong class="text-primary">#<?= $row['booking_id'] ?></strong></td>
                            <td>
                                <div class="customer-name"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></div>
                                <div class="customer-email"><?= htmlspecialchars($row['email']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($row['room_number']) ?></td>
                            <td><?= htmlspecialchars($row['room_type_name']) ?></td>
                            <td class="date-cell"><?= date('M j, Y', strtotime($row['actual_check_in'])) ?></td>
                            <td class="date-cell"><?= $row['actual_check_out'] ? date('M j, Y', strtotime($row['actual_check_out'])) : '<span class="text-muted">-</span>' ?></td>
                            <td>
                                <?php
                                $status = strtolower($row['booking_status']);
                                $badgeClass = '';
                                if ($status === 'confirmed') $badgeClass = 'confirmed';
                                elseif ($status === 'completed') $badgeClass = 'completed';
                                elseif ($status === 'cancelled') $badgeClass = 'cancelled';
                                elseif ($status === 'pending') $badgeClass = 'pending';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td>रु<?= number_format($row['advance_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($row['number']) ?></td>
                            <td><?= !empty($row['notes']) ? htmlspecialchars($row['notes']) : '<span class="text-muted">-</span>' ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <?php if ($status === 'confirmed' && empty($row['actual_check_out'])): ?>
                                        <?php if (isset($row['balance_due']) && floatval($row['balance_due']) > 0): ?>
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="bi bi-cash-coin"></i> Pending Payment
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-success checkout-btn" data-booking-id="<?= $row['booking_id'] ?>">
                                                <i class="bi bi-box-arrow-right"></i> Check Out
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <!-- Payment button removed -->
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="bi bi-calendar-x" style="font-size: 2rem; opacity: 0.5;"></i>
                                <div class="mt-2">No bookings found</div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
<!-- Payment Modal (hidden by default) -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel">Process Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="paymentForm">
          <input type="hidden" name="booking_id" id="paymentBookingId">
          <div class="mb-3">
            <label for="paymentAmount" class="form-label">Amount</label>
            <input type="number" class="form-control" id="paymentAmount" name="amount" min="0" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="paymentMethod" class="form-label">Payment Method</label>
            <select class="form-select" id="paymentMethod" name="method" required>
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="online">Online</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="paymentNotes" class="form-label">Notes (optional)</label>
            <textarea class="form-control" id="paymentNotes" name="notes" rows="2"></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100">Submit Payment</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
// Handle Payment button click
document.querySelectorAll('.payment-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const bookingId = this.getAttribute('data-booking-id');
        document.getElementById('paymentBookingId').value = bookingId;
        document.getElementById('paymentAmount').value = '';
        document.getElementById('paymentMethod').value = 'cash';
        document.getElementById('paymentNotes').value = '';
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    });
});
// Handle Payment form submit (AJAX placeholder)
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // TODO: Implement AJAX to process payment
    alert('Payment processed (demo only).');
    bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
});
// Handle Check Out button click (redirect to payment/checkout page)
document.querySelectorAll('.checkout-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const bookingId = this.getAttribute('data-booking-id');
        window.location.href = `checkout_payment.php?booking_id=${bookingId}`;
    });
});
// Filter by Check In / Check Out
const showCheckInBtn = document.getElementById('showCheckIn');
const showCheckOutBtn = document.getElementById('showCheckOut');
const tableRows = document.querySelectorAll('tbody tr[data-checkin]');

showCheckInBtn.addEventListener('click', function() {
    tableRows.forEach(row => {
        if (row.getAttribute('data-checkin') === '1') {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
showCheckOutBtn.addEventListener('click', function() {
    tableRows.forEach(row => {
        if (row.getAttribute('data-checkin') === '0') {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
</body>
</html>