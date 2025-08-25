<?php
session_start();
include_once '../config/configdatabse.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login.php?redirect=guestdash');
    exit();
}

$customerId = $_SESSION['customer_id'];
$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if (!$bookingId) {
    die('Invalid booking ID.');
}

// Fetch booking details, ensuring it belongs to the logged-in customer
$sql = "SELECT b.*, r.*, rm.*, rt.room_type_name, c.first_name, c.last_name, c.email, c.number
        FROM Bookings b
        JOIN Reservations r ON b.reservation_id = r.reservation_id
        JOIN Room rm ON b.room_id = rm.room_id
        JOIN RoomType rt ON rm.room_type = rt.room_type_id
        JOIN Customers c ON r.customer_id = c.id
        WHERE b.booking_id = ? AND r.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $bookingId, $customerId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    die('Booking not found or access denied.');
}

function formatDateTime($dt) {
    return $dt ? date('M j, Y, g:i A', strtotime($dt)) : '-';
}

function getStatusBadge($status) {
    $status = strtolower($status);
    switch($status) {
        case 'confirmed':
            return 'bg-success';
        case 'pending':
            return 'bg-warning text-dark';
        case 'cancelled':
            return 'bg-danger';
        case 'completed':
            return 'bg-primary';
        default:
            return 'bg-info text-dark';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details | Himalaya Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #8a5a44;
            --secondary-color: #8d6e63;
            --accent-color: #e9c46a;
            --dark-accent: #264653;
            --light-accent: #f4a261;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-accent));
            border-bottom: none;
            padding: 1.5rem;
        }
        
        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 1.25rem 1.5rem;
            transition: background-color 0.2s;
        }
        
        .list-group-item:hover {
            background-color: rgba(0,0,0,0.03);
        }
        
        .section-title {
            position: relative;
            padding-bottom: 10px;
            color: var(--primary-color);
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: var(--accent-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
        }
        
        .status-badge {
            font-size: 0.85rem;
            padding: 0.5em 0.8em;
            border-radius: 50px;
        }
        
        .price-highlight {
            color: #2a9d8f;
            font-weight: 600;
        }
        
        .back-btn {
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            transform: translateX(-5px);
        }
        
        .info-icon {
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(0,0,0,0.1), transparent);
            margin: 2rem 0;
        }
    </style>
</head>
<body>
<?php include '../include/header.php'; ?>
<div class="container py-5 animate__animated animate__fadeIn">
    <a href="guestdash.php" class="btn btn-link mb-4 back-btn text-decoration-none d-inline-flex align-items-center">
        <i class="bi bi-arrow-left-circle-fill me-2 fs-5"></i> Back to Dashboard
    </a>
    
    <div class="card shadow-lg mb-4 border-0">
        <div class="card-header text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bold" style= "color: white;"><i class="bi bi-journal-text me-2"></i>Booking #H<?= htmlspecialchars($booking['booking_id']) ?></h3>
                <span class="status-badge <?= getStatusBadge($booking['status']) ?> animate__animated animate__pulse animate__infinite">
                    <?= htmlspecialchars($booking['status']) ?>
                </span>
            </div>
        </div>
        
        <div class="card-body p-4">
            <div class="row mb-4 g-4">
                <!-- Room Information -->
                <div class="col-lg-6">
                    <div class="h-100 p-4 bg-white rounded-3 shadow-sm">
                        <h5 class="section-title mb-4"><i class="bi bi-door-open info-icon"></i>Room Information</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-hash text-muted me-2"></i><strong>Room Number</strong></span>
                                <span class="badge bg-light text-dark"><?= htmlspecialchars($booking['room_number']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-grid-1x2 text-muted me-2"></i><strong>Room Type</strong></span>
                                <span><?= htmlspecialchars($booking['room_type_name']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-people text-muted me-2"></i><strong>Capacity</strong></span>
                                <span><?= htmlspecialchars($booking['capacity']) ?> <small class="text-muted">guests</small></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-currency-rupee text-muted me-2"></i><strong>Price per Night</strong></span>
                                <span class="price-highlight">रु<?= number_format($booking['price_per_night'], 2) ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Reservation & Stay -->
                <div class="col-lg-6">
                    <div class="h-100 p-4 bg-white rounded-3 shadow-sm">
                        <h5 class="section-title mb-4"><i class="bi bi-calendar-range info-icon"></i>Reservation & Stay</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="bi bi-box-arrow-in-right text-muted me-2"></i>
                                    <strong>Check-in</strong>
                                    <div class="text-muted small">Requested</div>
                                </div>
                                <span><?= formatDateTime($booking['requested_check_in_date']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="bi bi-box-arrow-left text-muted me-2"></i>
                                    <strong>Check-out</strong>
                                    <div class="text-muted small">Requested</div>
                                </div>
                                <span><?= formatDateTime($booking['requested_check_out_date']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="bi bi-check-circle text-muted me-2"></i>
                                    <strong>Check-in</strong>
                                    <div class="text-muted small">Actual</div>
                                </div>
                                <span><?= formatDateTime($booking['actual_check_in']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="bi bi-check-circle-fill text-muted me-2"></i>
                                    <strong>Check-out</strong>
                                    <div class="text-muted small">Actual</div>
                                </div>
                                <span><?= formatDateTime($booking['actual_check_out']) ?></span>
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-chat-square-text text-muted me-2"></i>
                                <strong>Special Requests:</strong> 
                                <?= htmlspecialchars($booking['special_requests']) ?: '<span class="text-muted">None</span>' ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <div class="row g-4">
                <!-- Guest Information -->
                <div class="col-lg-6">
                    <div class="h-100 p-4 bg-white rounded-3 shadow-sm">
                        <h5 class="section-title mb-4"><i class="bi bi-person-circle info-icon"></i>Guest Information</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-person-badge fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></h6>
                                        <small class="text-muted">Primary Guest</small>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-envelope text-muted me-2"></i><strong>Email</strong></span>
                                <span><?= htmlspecialchars($booking['email']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-telephone text-muted me-2"></i><strong>Phone</strong></span>
                                <span><?= htmlspecialchars($booking['number']) ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Booking Summary -->
                <div class="col-lg-6">
                    <div class="h-100 p-4 bg-white rounded-3 shadow-sm">
                        <h5 class="section-title mb-4"><i class="bi bi-receipt info-icon"></i>Booking Summary</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-journal-code text-muted me-2"></i><strong>Booking ID</strong></span>
                                <span class="badge bg-light text-dark">#H<?= htmlspecialchars($booking['booking_id']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-journal-bookmark text-muted me-2"></i><strong>Reservation ID</strong></span>
                                <span><?= htmlspecialchars($booking['reservation_id']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-calendar-event text-muted me-2"></i><strong>Booking Date</strong></span>
                                <span><?= formatDateTime($booking['created_at']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-cash-coin text-muted me-2"></i><strong>Advance Paid</strong></span>
                                <span class="price-highlight">रु<?= number_format($booking['advance_amount'], 2) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                                <span><i class="bi bi-wallet2 text-muted me-2"></i><strong>Estimated Total</strong></span>
                                <span class="fs-5 fw-bold text-primary">रु<?= number_format($booking['estimated_total_amount'], 2) ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="mt-5 text-center">
                <a href="mybookings.php" class="btn btn-primary px-4 py-2 me-3">
                    <i class="bi bi-list-ul me-2"></i>View All Bookings
                </a>
                <button class="btn btn-outline-primary px-4 py-2" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>Print Details
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../include/footer_guest.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add animation when scrolling to elements
    document.addEventListener('DOMContentLoaded', function() {
        const animateElements = document.querySelectorAll('.list-group-item, .section-title');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        }, { threshold: 0.1 });
        
        animateElements.forEach(el => observer.observe(el));
    });
</script>
</body>
</html>