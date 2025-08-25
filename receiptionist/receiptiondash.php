<?php
session_start();
include_once '../config/configdatabse.php';

// Check if user is logged in as staff
// if (!isset($_SESSION['staff_id'])) {
//     header("Location: staff_login.php");
//     exit();
// }

// Get all pending reservations
$reservations_query = "
SELECT 
    r.reservation_id, r.requested_check_in_date AS check_in, r.requested_check_out_date AS check_out, 
    r.num_guests, r.status, 
    c.first_name, c.last_name, c.number, c.email,
    rm.room_number, rt.room_type_name, rm.price_per_night
FROM Reservations r
JOIN Customers c ON r.customer_id = c.id
JOIN RoomType rt ON r.room_type_id = rt.room_type_id
LEFT JOIN Room rm ON rm.room_type = rt.room_type_id
WHERE r.status IN ('pending', 'confirmed')
ORDER BY r.requested_check_in_date ASC
";
$reservations = $conn->query($reservations_query);
if (!$reservations) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Management | Hotel Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/receiptionist.css">  
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-3">
            <div class="text-center mb-4">
                <h3>Hotel Admin</h3>
                <p class="text-muted">Front Desk</p>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#">
                        <i class="fas fa-calendar-check"></i> Reservations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-bed"></i> Room Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-users"></i> Guests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-chart-line"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-warning" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content w-100">
            <div class="dashboard-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Reservation Management
                </h4>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" placeholder="Search reservations...">
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Reservations</h5>
                            <h2 class="mb-0"><?= $reservations->num_rows ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Confirmed</h5>
                            <h2 class="mb-0">
                                <?php 
                                    $confirmed = $conn->query("SELECT COUNT(*) FROM Reservations WHERE status = 'confirmed'")->fetch_row()[0];
                                    echo $confirmed;
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Pending</h5>
                            <h2 class="mb-0">
                                <?php 
                                    $pending = $conn->query("SELECT COUNT(*) FROM Reservations WHERE status = 'pending'")->fetch_row()[0];
                                    echo $pending;
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">Cancelled</h5>
                            <h2 class="mb-0">
                                <?php 
                                    $cancelled = $conn->query("SELECT COUNT(*) FROM Reservations WHERE status = 'cancelled'")->fetch_row()[0];
                                    echo $cancelled;
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Reservations</h5>
                    <div>
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> New Reservation
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="reservationsTable">
                            <thead>
                                <tr>
                                    <th>Reservation ID</th>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Check-In</th>
                                    <th>Check-Out</th>
                                    <th>Guests</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reservation = $reservations->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $reservation['reservation_id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="customer-avatar me-3">
                                                <?= substr($reservation['first_name'], 0, 1) . substr($reservation['last_name'], 0, 1) ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= $reservation['first_name'] ?> <?= $reservation['last_name'] ?></h6>
                                                <small class="text-muted"><?= $reservation['email'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= $reservation['room_number'] ?></strong>
                                        <div class="text-muted small"><?= $reservation['room_type_name'] ?></div>
                                    </td>
                                    <td>
                                        <?= date('M j, Y', strtotime($reservation['check_in'])) ?>
                                        <div class="text-muted small"><?= date('g:i A', strtotime($reservation['check_in'])) ?></div>
                                    </td>
                                    <td>
                                        <?= date('M j, Y', strtotime($reservation['check_out'])) ?>
                                        <div class="text-muted small"><?= date('g:i A', strtotime($reservation['check_out'])) ?></div>
                                    </td>
                                    <td><?= $reservation['num_guests'] ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $reservation['status'] ?>">
                                            <?= ucfirst($reservation['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <?php if ($reservation['status'] == 'pending'): ?>
                                                <button class="btn btn-sm btn-success btn-action me-1 confirm-btn" 
                                                        data-id="<?= $reservation['reservation_id'] ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-sm btn-primary btn-action me-1 view-btn" 
                                                    data-id="<?= $reservation['reservation_id'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <?php if ($reservation['status'] != 'cancelled'): ?>
                                                <button class="btn btn-sm btn-danger btn-action cancel-btn" 
                                                        data-id="<?= $reservation['reservation_id'] ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if ($reservations->num_rows == 0): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No reservations found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Reservation Modal -->
    <div class="modal fade" id="viewReservationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reservation Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="reservationDetails">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary print-btn">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#reservationsTable').DataTable({
                responsive: true,
                order: [[3, 'asc']] // Sort by check-in date
            });
            
            // View reservation details
            $('.view-btn').click(function() {
                const reservationId = $(this).data('id');
                
                $.ajax({
                    url: 'get_reservation_details.php',
                    method: 'POST',
                    data: { reservation_id: reservationId },
                    success: function(response) {
                        $('#reservationDetails').html(response);
                        $('#viewReservationModal').modal('show');
                    }
                });
            });
            
            // Confirm reservation
            $('.confirm-btn').click(function() {
                const reservationId = $(this).data('id');
                const row = $(this).closest('tr');
                
                if (confirm('Are you sure you want to confirm this reservation?')) {
                    $.ajax({
                        url: 'update_reservation_status.php',
                        method: 'POST',
                        data: { 
                            reservation_id: reservationId,
                            status: 'confirmed'
                        },
                        success: function() {
                            // Update the status badge
                            const badge = row.find('.status-badge');
                            badge.removeClass('status-pending').addClass('status-confirmed').text('Confirmed');
                            
                            // Remove the confirm button
                            row.find('.confirm-btn').remove();
                            
                            // Show success message
                            alert('Reservation confirmed successfully!');
                        }
                    });
                }
            });
            
            // Cancel reservation
            $('.cancel-btn').click(function() {
                const reservationId = $(this).data('id');
                const row = $(this).closest('tr');
                
                if (confirm('Are you sure you want to cancel this reservation?')) {
                    $.ajax({
                        url: 'update_reservation_status.php',
                        method: 'POST',
                        data: { 
                            reservation_id: reservationId,
                            status: 'cancelled'
                        },
                        success: function() {
                            // Update the status badge
                            const badge = row.find('.status-badge');
                            badge.removeClass('status-pending status-confirmed').addClass('status-cancelled').text('Cancelled');
                            
                            // Remove action buttons
                            row.find('.confirm-btn, .cancel-btn').remove();
                            
                            // Show success message
                            alert('Reservation cancelled successfully!');
                        }
                    });
                }
            });
            
            // Print reservation
            $('.print-btn').click(function() {
                window.print();
            });
        });
    </script>
</body>
</html>