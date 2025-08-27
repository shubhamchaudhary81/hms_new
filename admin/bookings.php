<?php
session_start();
if ($_SESSION['admin_id'] == "" || $_SESSION['admin_name'] == "") {
    header("Location: ../login.php");
    exit();
} 
include_once '../config/configdatabse.php';

$headerTitle = "Bookings Management";
$headerSubtitle = "Manage and track bookings with full details, check-in/out, cancellations, and edits.";
// $buttonText = "Add New Room";
// $buttonLink = "newroom.php";
// $showButton = true;

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
    <link rel="stylesheet" href="../css/admin/content.css">
    <!-- <link rel="stylesheet" href="../css/adminbooking.css"> -->
    <!-- <link rel="stylesheet" href="adminsidebar.css"> -->
     <style>
        
        :root {
    --primary: #8D6E63;
    --primary-light: #f8f6f5;
    --secondary: #6D4C41;
    --success: #4CAF50;
    --warning: #FFC107;
    --danger: #F44336;
    --dark: #3E2723;
    --light: #ffffff;
    --text-light: #8D6E63;
    --border-radius: 16px;
    --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
}

body {
    font-family: 'Poppins', sans-serif;
    background-color: var(--primary-light);
    color: var(--dark);
    line-height: 1.6;
}

/* Content Wrapper */
.content {
    margin-left: 270px;
    min-height: 100vh;
    padding: 2rem;
    transition: var(--transition);
}

/* ---------- HEADER ---------- */
.booking-header {
    background: var(--light);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.25rem 1.75rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    border: 1px solid #eee;
}

.booking-header h2 {
    font-weight: 600;
    margin: 0;
    font-size: 1.6rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    color: var(--dark);
}

.booking-header .bi {
    font-size: 1.8rem;
    color: var(--primary);
}

/* ---------- SUMMARY CARDS ---------- */
.booking-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.summary-card {
    background: var(--light);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    text-align: center;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    border: 1px solid #eee;
}

.summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.summary-card .title {
    font-size: 0.95rem;
    color: #777;
    margin-bottom: 0.5rem;
}

.summary-card .value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark);
}

/* Colors */
.summary-card.total { border-top: 4px solid var(--primary); }
.summary-card.confirmed { border-top: 4px solid var(--success); }
.summary-card.completed { border-top: 4px solid #795548; }
.summary-card.cancelled { border-top: 4px solid var(--danger); }

/* ---------- TABLE ---------- */
.card-container {
    background: var(--light);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    border: 1px solid #eee;
    overflow: hidden;
}

.table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table thead th {
    background: var(--primary);
    color: #fff;
    font-weight: 500;
    padding: 1rem 1.25rem;
    border: none;
}

.table tbody tr {
    transition: var(--transition);
}

.table tbody tr:hover {
    background: #f7f4f3;
}

.table td, .table th {
    padding: 1rem 1.25rem;
    vertical-align: middle;
    border-top: 1px solid #f0eae8;
}

/* Customer Info */
.customer-name {
    font-weight: 600;
    color: var(--dark);
    font-size: 0.95rem;
}

.customer-email {
    font-size: 0.85rem;
    color: #888;
}

/* Status Badges */
.badge {
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.45rem 0.75rem;
    border-radius: 30px;
    text-transform: capitalize;
}

.badge.confirmed { background: #e8f5e9; color: var(--success); }
.badge.completed { background: #efebe9; color: #795548; }
.badge.cancelled { background: #fdecea; color: var(--danger); }
.badge.pending { background: #fff8e1; color: var(--warning); }

/* Actions Buttons */
.btn-sm {
    border-radius: 8px;
    font-size: 0.85rem;
    padding: 0.4rem 0.7rem;
}

/* Search box */
.search-box input {
    border-radius: 50px;
    padding-left: 2.5rem;
    border: 1px solid #ddd;
    transition: var(--transition);
}

.search-box input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(141, 110, 99, 0.15);
}

   
     </style>
</head>
<body>
    <?php include_once 'sidebar.php'; ?>

<div class="content">
    <?php include_once 'header-content.php' ?>
    <div class="container-fluid">
        <!-- Header Section -->
        <!-- <div class="booking-header">
            <h2><i class="bi bi-journal-bookmark"></i> All Bookings</h2>
            <div class="d-flex align-items-center gap-2">
                <button id="showCheckIn" class="btn btn-outline-primary btn-sm">Check In</button>
                <button id="showCheckOut" class="btn btn-outline-success btn-sm">Check Out</button>
                <form class="search-box ms-2" method="get" action="">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" name="search" placeholder="Search bookings..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </form>
            </div>
        </div> -->
        

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