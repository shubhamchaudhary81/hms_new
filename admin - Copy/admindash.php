<?php
session_start();
include '../config/configdatabse.php';

// Set admin name from session or fallback
if (!isset($_SESSION['name'])) {
    $_SESSION['name'] = "Admin";
}

// Total Rooms
$roomCount = 0;
$roomCountResult = $conn->query("SELECT COUNT(*) AS total FROM Room");
if ($roomCountResult && $row = $roomCountResult->fetch_assoc()) {
    $roomCount = $row['total'];
}

// Bookings Today
$bookingsToday = 0;
$today = date('Y-m-d');
$bookingsTodayResult = $conn->query("SELECT COUNT(*) AS total FROM Bookings WHERE DATE(actual_check_in) = '$today'");
if ($bookingsTodayResult && $row = $bookingsTodayResult->fetch_assoc()) {
    $bookingsToday = $row['total'];
}

// Customers
$customerCount = 0;
$customerResult = $conn->query("SELECT COUNT(*) AS total FROM Customers");
if ($customerResult && $row = $customerResult->fetch_assoc()) {
    $customerCount = $row['total'];
}

// Revenue (sum of all payments)
$revenue = 0;
$revenueResult = $conn->query("SELECT SUM(amount) AS total FROM Payments");
if ($revenueResult && $row = $revenueResult->fetch_assoc()) {
    $revenue = $row['total'] ?? 0;
}

// Recent Bookings (latest 5)
$recentBookings = $conn->query("SELECT b.booking_id, b.actual_check_in, b.status, c.first_name, c.last_name, rm.room_number, rt.room_type_name FROM Bookings b JOIN Reservations r ON b.reservation_id = r.reservation_id JOIN Customers c ON r.customer_id = c.id JOIN Room rm ON b.room_id = rm.room_id JOIN RoomType rt ON rm.room_type = rt.room_type_id ORDER BY b.actual_check_in DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admindash.css">
    <link rel="stylesheet" href="adminsidebar.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div class="content">
    <div class="header">
        <h2>Welcome, <?php echo $_SESSION['name']; ?> <span style="opacity: 0.7;">👋</span></h2>
        <div class="text-muted"><?php echo date('l, F j, Y'); ?></div>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                            <i class="bi bi-door-open text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <div class="card-title">Total Rooms</div>
                            <h3 class="card-value"><?php echo $roomCount; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                            <i class="bi bi-calendar-check text-success" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <div class="card-title">Bookings Today</div>
                            <h3 class="card-value"><?php echo $bookingsToday; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-3 rounded me-3">
                            <i class="bi bi-people text-info" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <div class="card-title">Customers</div>
                            <h3 class="card-value"><?php echo $customerCount; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded me-3">
                            <i class="bi bi-currency-dollar text-warning" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <div class="card-title">Revenue</div>
                            <h3 class="card-value">रु<?php echo number_format($revenue,2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container mt-4">
        <h5 class="mb-3" style="color: var(--primary);">Recent Bookings</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($recentBookings && $recentBookings->num_rows > 0):
                    while ($row = $recentBookings->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($row['booking_id']) ?></td>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['room_number']) ?> (<?= htmlspecialchars($row['room_type_name']) ?>)</td>
                        <td><?= htmlspecialchars($row['actual_check_in']) ?></td>
                        <td>
                            <?php
                            $status = strtolower($row['status']);
                            $badgeClass = 'bg-secondary';
                            if ($status === 'confirmed') $badgeClass = 'bg-primary';
                            elseif ($status === 'completed') $badgeClass = 'bg-secondary';
                            elseif ($status === 'cancelled') $badgeClass = 'bg-danger';
                            elseif ($status === 'pending') $badgeClass = 'bg-warning text-dark';
                            elseif ($status === 'checked in') $badgeClass = 'bg-success';
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?php echo ucwords($status); ?></span>
                        </td>
                    </tr>
                <?php endwhile;
                else: ?>
                    <tr><td colspan="5" class="text-center text-muted">No recent bookings found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>