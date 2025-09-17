<?php
session_start();
if ($_SESSION['admin_id'] == "" || $_SESSION['admin_name'] == "") {
    header("Location: ../login.php");
    exit();
}
$headerTitle = "Reservation Management";
$headerSubtitle = "Manage all hotel bookings and reservations";
$buttonText = "New Reservation";
$buttonLink = "room-reservation.php";
$showButton = true;
include_once '../config/configdatabse.php';

// Check if user is logged in as staff
// if (!isset($_SESSION['staff_id'])) {
//     header("Location: staff_login.php");
//     exit();
// }

// Get pending reservations
$pending_query = "
SELECT 
    r.reservation_id, r.requested_check_in_date AS check_in, r.requested_check_out_date AS check_out, 
    r.num_guests, r.status, 
    c.first_name, c.last_name, c.number, c.email,
    rt.room_type_name,
    rm.room_number, rm.price_per_night
FROM Reservations r
JOIN Customers c ON r.customer_id = c.id
JOIN RoomType rt ON r.room_type_id = rt.room_type_id
LEFT JOIN Room rm ON rm.room_type = rt.room_type_id
WHERE r.status = 'pending'
ORDER BY r.requested_check_in_date ASC
";
$pending_reservations = $conn->query($pending_query);
if (!$pending_reservations) {
    die("Pending query failed: " . $conn->error);
}

// Get confirmed reservations
$confirmed_query = "
SELECT 
    r.reservation_id, r.requested_check_in_date AS check_in, r.requested_check_out_date AS check_out, 
    r.num_guests, r.status, 
    c.first_name, c.last_name, c.number, c.email,
    rm.room_number, rt.room_type_name, rm.price_per_night
FROM Reservations r
JOIN Customers c ON r.customer_id = c.id
JOIN RoomType rt ON r.room_type_id = rt.room_type_id
LEFT JOIN Room rm ON rm.room_type = rt.room_type_id
WHERE r.status = 'confirmed'
ORDER BY r.requested_check_in_date ASC
";
$confirmed_reservations = $conn->query($confirmed_query);
if (!$confirmed_reservations) {
    die("Confirmed query failed: " . $conn->error);
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
    <!-- <link rel="stylesheet" href="adminsidebar.css"> -->
    <link rel="stylesheet" href="../css/admin/reservationsadmin.css">
</head>

<body>
    <?php include_once 'sidebar.php'; ?>
    <div class="d-flex">
        <!-- Main Content -->
        <div class="main-content w-100">
            <?php include_once 'header-content.php'; ?>
            <!-- <div class="dashboard-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class=" "></i>Reservation Management
                </h4>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" placeholder="Search reservations...">
                </div>
            </div> -->

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Reservations</h5>
                            <h2 class="mb-0">
                                <?php
                                // Only count pending reservations
                                $total_reservations = $conn->query("SELECT COUNT(*) FROM Reservations WHERE status = 'pending'")->fetch_row()[0];
                                echo $total_reservations;
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Confirmed Bookings</h5>
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
                <!-- <div class="card-header bg-white d-flex justify-content-between align-items-center"> -->
                    <!-- <h5 class="mb-0">Reservation Management</h5> -->
                    <!-- <div>
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> New Reservation
                        </button>
                    </div> -->
                    <!-- <div>
                        <a href="room-reservation.php"
                            style="display: inline-block; padding: 5px 12px; font-size: 0.875rem; color: #fff; background-color: #0d6efd; border: 1px solid #0d6efd; border-radius: 0.25rem; text-decoration: none;">
                            <i class="fas fa-plus me-1"></i> New Reservation
                        </a>
                    </div> -->
                <!-- </div> -->
                <div class="card-body">
                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs" id="reservationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab"
                                data-bs-target="#pending" type="button" role="tab" aria-controls="pending"
                                aria-selected="true">
                                <i class="fas fa-clock me-2"></i>Pending Reservations
                                <span class="badge bg-warning ms-2"><?= $pending_reservations->num_rows ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="confirmed-tab" data-bs-toggle="tab" data-bs-target="#confirmed"
                                type="button" role="tab" aria-controls="confirmed" aria-selected="false">
                                <i class="fas fa-check-circle me-2"></i>Confirmed Reservations
                                <span class="badge bg-success ms-2"><?= $confirmed_reservations->num_rows ?></span>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="reservationTabsContent">
                        <!-- Pending Reservations Tab -->
                        <div class="tab-pane fade show active" id="pending" role="tabpanel"
                            aria-labelledby="pending-tab">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover" id="pendingTable">
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
                                        <?php while ($reservation = $pending_reservations->fetch_assoc()): ?>
                                            <tr data-price="<?= $reservation['price_per_night'] ?>">
                                                <td>#<?= $reservation['reservation_id'] ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="customer-avatar me-3">
                                                            <?= substr($reservation['first_name'], 0, 1) . substr($reservation['last_name'], 0, 1) ?>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?= $reservation['first_name'] ?>
                                                                <?= $reservation['last_name'] ?></h6>
                                                            <small class="text-muted"><?= $reservation['email'] ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?= $reservation['room_number'] ?? 'TBD' ?></strong>
                                                    <div class="text-muted small"><?= $reservation['room_type_name'] ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= date('M j, Y', strtotime($reservation['check_in'])) ?>
                                                    <div class="text-muted small">
                                                        <?= date('g:i A', strtotime($reservation['check_in'])) ?></div>
                                                </td>
                                                <td>
                                                    <?= date('M j, Y', strtotime($reservation['check_out'])) ?>
                                                    <div class="text-muted small">
                                                        <?= date('g:i A', strtotime($reservation['check_out'])) ?></div>
                                                </td>
                                                <td><?= $reservation['num_guests'] ?></td>
                                                <td>
                                                    <span class="status-badge status-pending">
                                                        Pending
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <button class="btn btn-sm btn-success btn-action me-1 confirm-btn"
                                                            data-id="<?= $reservation['reservation_id'] ?>"
                                                            title="Confirm Reservation">
                                                            <i class="fas fa-check"></i>
                                                        </button>

                                                        <button class="btn btn-sm btn-primary btn-action me-1 view-btn"
                                                            data-id="<?= $reservation['reservation_id'] ?>"
                                                            title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>

                                                        <button class="btn btn-sm btn-danger btn-action cancel-btn"
                                                            data-id="<?= $reservation['reservation_id'] ?>"
                                                            title="Cancel Reservation">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <?php if ($pending_reservations->num_rows == 0): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-inbox fa-2x mb-3"></i>
                                                        <p>No pending reservations found.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Confirmed Reservations Tab -->
                        <div class="tab-pane fade" id="confirmed" role="tabpanel" aria-labelledby="confirmed-tab">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover" id="confirmedTable">
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
                                        <?php while ($reservation = $confirmed_reservations->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?= $reservation['reservation_id'] ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="customer-avatar me-3">
                                                            <?= substr($reservation['first_name'], 0, 1) . substr($reservation['last_name'], 0, 1) ?>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?= $reservation['first_name'] ?>
                                                                <?= $reservation['last_name'] ?></h6>
                                                            <small class="text-muted"><?= $reservation['email'] ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?= $reservation['room_number'] ?? 'TBD' ?></strong>
                                                    <div class="text-muted small"><?= $reservation['room_type_name'] ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= date('M j, Y', strtotime($reservation['check_in'])) ?>
                                                    <div class="text-muted small">
                                                        <?= date('g:i A', strtotime($reservation['check_in'])) ?></div>
                                                </td>
                                                <td>
                                                    <?= date('M j, Y', strtotime($reservation['check_out'])) ?>
                                                    <div class="text-muted small">
                                                        <?= date('g:i A', strtotime($reservation['check_out'])) ?></div>
                                                </td>
                                                <td><?= $reservation['num_guests'] ?></td>
                                                <td>
                                                    <span class="status-badge status-confirmed">
                                                        Confirmed
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <button class="btn btn-sm btn-primary btn-action me-1 view-btn"
                                                            data-id="<?= $reservation['reservation_id'] ?>"
                                                            title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>

                                                        <button class="btn btn-sm btn-warning btn-action me-1"
                                                            data-id="<?= $reservation['reservation_id'] ?>" title="Check In"
                                                            onclick="checkInReservation(<?= $reservation['reservation_id'] ?>)">
                                                            <i class="fas fa-sign-in-alt"></i>
                                                        </button>

                                                        <button class="btn btn-sm btn-danger btn-action cancel-btn"
                                                            data-id="<?= $reservation['reservation_id'] ?>"
                                                            title="Cancel Reservation">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <?php if ($confirmed_reservations->num_rows == 0): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-check-circle fa-2x mb-3"></i>
                                                        <p>No confirmed reservations found.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

    <!-- Advance Payment Modal -->
    <div class="modal fade" id="advancePaymentModal" tabindex="-1" aria-labelledby="advancePaymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="advancePaymentModalLabel">
                        <i class="fas fa-credit-card me-2"></i>Confirm Reservation & Collect Advance
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="advancePaymentForm">
                        <input type="hidden" name="reservation_id" id="advanceReservationId">

                        <!-- Reservation Summary -->
                        <div class="reservation-summary mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>Reservation Summary
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="summary-item">
                                        <span class="label">Guest Name:</span>
                                        <span class="value" id="guestName">-</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="label">Room Type:</span>
                                        <span class="value" id="roomType">-</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="label">Check-in:</span>
                                        <span class="value" id="checkInDate">-</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="label">Check-out:</span>
                                        <span class="value" id="checkOutDate">-</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="summary-item">
                                        <span class="label">Number of Guests:</span>
                                        <span class="value" id="numGuests">-</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="label">Room Number:</span>
                                        <span class="value" id="roomNumber">-</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="label">Price per Night:</span>
                                        <span class="value" id="pricePerNight">-</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="label">Total Nights:</span>
                                        <span class="value" id="totalNights">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cost Breakdown -->
                        <div class="cost-breakdown mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-calculator me-2"></i>Cost Breakdown
                            </h6>
                            <div class="breakdown-item">
                                <span class="label">Price per Night:</span>
                                <span class="value" id="breakdownPricePerNight">रु0.00</span>
                            </div>
                            <div class="breakdown-item">
                                <span class="label">Number of Nights:</span>
                                <span class="value" id="breakdownNights">0</span>
                            </div>
                            <div class="breakdown-item total">
                                <span class="label">Total Estimated Cost:</span>
                                <span class="value" id="totalEstimatedCost">रु0.00</span>
                            </div>
                        </div>

                        <!-- Advance Payment Section -->
                        <div class="advance-payment-section">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-hand-holding-usd me-2"></i>Advance Payment
                            </h6>
                            <div class="mb-3">
                                <label for="advanceAmount" class="form-label fw-bold">Advance Amount Required</label>
                                <div class="input-group">
                                    <span class="input-group-text">रु</span>
                                    <input type="number" class="form-control form-control-lg" id="advanceAmount"
                                        name="advance_amount" min="0" step="0.01" required>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Recommended: 30% of total cost (minimum $50)
                                </div>
                            </div>

                            <div class="payment-summary">
                                <div class="summary-row">
                                    <span>Total Estimated Cost:</span>
                                    <span id="summaryTotalCost">रु0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span>Advance Payment:</span>
                                    <span id="summaryAdvance">रु0.00</span>
                                </div>
                                <div class="summary-row remaining">
                                    <span>Remaining Balance:</span>
                                    <span id="summaryRemaining">रु0.00</span>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check me-1"></i>Confirm Reservation & Collect Advance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize DataTables for both tables only if they have data
            const pendingTable = $('#pendingTable');
            const confirmedTable = $('#confirmedTable');

            // Initialize DataTables using the safe function
            const pendingDataTable = initializeDataTable('#pendingTable', {
                responsive: true,
                order: [[3, 'asc']], // Sort by check-in date
                pageLength: 10,
                language: {
                    search: "Search pending reservations:",
                    lengthMenu: "Show _MENU_ pending reservations per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ pending reservations"
                }
            });

            const confirmedDataTable = initializeDataTable('#confirmedTable', {
                responsive: true,
                order: [[3, 'asc']], // Sort by check-in date
                pageLength: 10,
                language: {
                    search: "Search confirmed reservations:",
                    lengthMenu: "Show _MENU_ confirmed reservations per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ confirmed reservations"
                }
            });

            // View reservation details
            // $(document).on('click', '.view-btn', function() {
            //     const reservationId = $(this).data('id');

            //     $.ajax({
            //         url: 'get_reservation_details.php',
            //         method: 'POST',
            //         data: { reservation_id: reservationId },
            //         success: function(response) {
            //             $('#reservationDetails').html(response);
            //             $('#viewReservationModal').modal('show');
            //         },
            //         error: function(xhr) {
            //             alert('Error loading reservation details: ' + xhr.responseText);
            //         }
            //     });
            // });

            // Confirm reservation (with advance payment)
            $(document).on('click', '.confirm-btn', function () {
                const reservationId = $(this).data('id');
                const row = $(this).closest('tr');

                // Get reservation data from the table row
                const guestName = row.find('td:eq(1) h6').text();
                const roomType = row.find('td:eq(2) .text-muted').text();
                const roomNumber = row.find('td:eq(2) strong').text();
                const checkIn = row.find('td:eq(3)').text().trim();
                const checkOut = row.find('td:eq(4)').text().trim();
                const numGuests = row.find('td:eq(5)').text();

                // Get price from data attribute
                const pricePerNight = parseFloat(row.data('price')) || 0;

                // Calculate total nights
                const checkInDate = new Date(checkIn);
                const checkOutDate = new Date(checkOut);
                const totalNights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));

                // Calculate total cost
                const totalCost = pricePerNight * totalNights;

                // Populate modal with reservation details
                $('#guestName').text(guestName);
                $('#roomType').text(roomType);
                $('#roomNumber').text(roomNumber);
                $('#checkInDate').text(checkIn);
                $('#checkOutDate').text(checkOut);
                $('#numGuests').text(numGuests);
                $('#pricePerNight').text('$' + pricePerNight.toFixed(2));
                $('#totalNights').text(totalNights);

                // Populate cost breakdown
                $('#breakdownPricePerNight').text('$' + pricePerNight.toFixed(2));
                $('#breakdownNights').text(totalNights);
                $('#totalEstimatedCost').text('$' + totalCost.toFixed(2));

                // Set recommended advance amount (30% of total cost, minimum $50)
                const recommendedAdvance = Math.max(totalCost * 0.3, 50);
                $('#advanceAmount').val(recommendedAdvance.toFixed(2));

                // Update payment summary
                updatePaymentSummary(totalCost, recommendedAdvance);

                // Store reservation ID
                $('#advanceReservationId').val(reservationId);

                // Show modal
                $('#advancePaymentModal').modal('show');
            });

            $('#advancePaymentForm').submit(function (e) {
                e.preventDefault();
                const reservationId = $('#advanceReservationId').val();
                const advanceAmount = parseFloat($('#advanceAmount').val()) || 0;
                const totalCost = parseFloat($('#totalEstimatedCost').text().replace('$', '')) || 0;
                const row = $("button.confirm-btn[data-id='" + reservationId + "']").closest('tr');

                // Validate advance amount
                if (advanceAmount <= 0) {
                    showAlert('Please enter a valid advance amount.', 'error');
                    return;
                }

                if (advanceAmount > totalCost) {
                    showAlert('Advance amount cannot exceed total cost.', 'error');
                    return;
                }

                $.ajax({
                    url: 'book_reservation.php',
                    method: 'POST',
                    data: {
                        reservation_id: reservationId,
                        status: 'confirmed',
                        advance_amount: advanceAmount,
                        total_cost: totalCost
                    },
                    success: function (response) {
                        if (response.trim() === 'success') {
                            // Remove the row from pending table
                            row.fadeOut(300, function () {
                                $(this).remove();
                                // Update pending count
                                updateTabCounts();
                            });

                            $('#advancePaymentModal').modal('hide');

                            // Show success message
                            showAlert('Reservation confirmed and advance payment of $' + advanceAmount.toFixed(2) + ' collected!', 'success');

                            // Refresh the page after a short delay to update both tabs
                            setTimeout(function () {
                                location.reload();
                            }, 1500);
                        } else {
                            showAlert('Error: ' + response, 'error');
                        }
                    },
                    error: function (xhr) {
                        showAlert('AJAX error: ' + xhr.responseText, 'error');
                    }
                });
            });

            // Cancel reservation
            $(document).on('click', '.cancel-btn', function () {
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
                        success: function () {
                            // Remove the row
                            row.fadeOut(300, function () {
                                $(this).remove();
                                // Update counts
                                updateTabCounts();
                            });

                            // Show success message
                            showAlert('Reservation cancelled successfully!', 'success');
                        },
                        error: function (xhr) {
                            showAlert('Error cancelling reservation: ' + xhr.responseText, 'error');
                        }
                    });
                }
            });

            // Print reservation
            $('.print-btn').click(function () {
                window.print();
            });

            // Function to update tab counts
            function updateTabCounts() {
                // Count only actual data rows (excluding empty state rows)
                const pendingCount = $('#pendingTable tbody tr:not(:has(td[colspan]))').length;
                const confirmedCount = $('#confirmedTable tbody tr:not(:has(td[colspan]))').length;

                $('#pending-tab .badge').text(pendingCount);
                $('#confirmed-tab .badge').text(confirmedCount);
            }

            // Function to show alerts
            function showAlert(message, type) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;

                // Insert alert at the top of the main content
                $('.main-content').prepend(alertHtml);

                // Auto-dismiss after 5 seconds
                setTimeout(function () {
                    $('.alert').fadeOut(300, function () {
                        $(this).remove();
                    });
                }, 5000);
            }

            // Function to safely initialize DataTable
            function initializeDataTable(tableId, options) {
                const table = $(tableId);

                // Destroy existing DataTable if it exists
                if ($.fn.DataTable.isDataTable(tableId)) {
                    table.DataTable().destroy();
                }

                // Remove any existing DataTable classes
                table.removeClass('dataTable');

                // Check if table has actual data rows
                const hasData = table.find('tbody tr:not(:has(td[colspan]))').length > 0;

                if (hasData) {
                    return table.DataTable(options);
                } else {
                    // If no data, just make it responsive
                    table.addClass('table-responsive');
                    return null;
                }
            }

            // Function to update payment summary
            function updatePaymentSummary(totalCost, advanceAmount) {
                $('#summaryTotalCost').text('$' + totalCost.toFixed(2));
                $('#summaryAdvance').text('$' + advanceAmount.toFixed(2));
                const remaining = totalCost - advanceAmount;
                $('#summaryRemaining').text('$' + remaining.toFixed(2));
            }

            // Handle advance amount changes
            $('#advanceAmount').on('input', function () {
                const advanceAmount = parseFloat($(this).val()) || 0;
                const totalCost = parseFloat($('#totalEstimatedCost').text().replace('$', '')) || 0;
                updatePaymentSummary(totalCost, advanceAmount);
            });
        });

        // Function to handle check-in (can be called from confirmed reservations)
        function checkInReservation(reservationId) {
            if (confirm('Are you sure you want to check in this guest?')) {
                $.ajax({
                    url: 'check_in_reservation.php',
                    method: 'POST',
                    data: { reservation_id: reservationId },
                    success: function (response) {
                        if (response.trim() === 'success') {
                            alert('Guest checked in successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + response);
                        }
                    },
                    error: function (xhr) {
                        alert('Error checking in guest: ' + xhr.responseText);
                    }
                });
            }
        }
    </script>
</body>

</html>