<?php
session_start();
include '../config/configdatabse.php';

// Get room ID from URL
$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($room_id <= 0) {
    header("Location: rooms.php");
    exit();
}

// Fetch comprehensive room data
$stmt = $conn->prepare("SELECT r.*, rt.room_type_name, rt.description as type_description,
                        (SELECT COUNT(*) FROM Reviews rev WHERE rev.room_id = r.room_id) as review_count,
                        (SELECT AVG(rating) FROM Reviews rev WHERE rev.room_id = r.room_id) as avg_rating
                        FROM Room r 
                        JOIN RoomType rt ON r.room_type = rt.room_type_id 
                        WHERE r.room_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: rooms.php");
        exit();
    }

    $room = $result->fetch_assoc();
    $stmt->close();
} else {
    // Fallback query without subqueries if the main query fails
    $stmt = $conn->prepare("SELECT r.*, rt.room_type_name, rt.description as type_description
                            FROM Room r 
                            JOIN RoomType rt ON r.room_type = rt.room_type_id 
                            WHERE r.room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: rooms.php");
        exit();
    }
    
    $room = $result->fetch_assoc();
    $room['review_count'] = 0;
    $room['avg_rating'] = 0;
    $stmt->close();
}

// Fetch room amenities
$amenities = [];
$amenity_sql = "SELECT a.amenity_id, a.amenity_name, a.description as amenity_description, ra.quantity 
                FROM RoomAmenity ra 
                JOIN Amenity a ON ra.amenity_id = a.amenity_id 
                WHERE ra.room_id = ?";
$amenity_stmt = $conn->prepare($amenity_sql);
if ($amenity_stmt) {
    $amenity_stmt->bind_param("i", $room_id);
    $amenity_stmt->execute();
    $amenity_result = $amenity_stmt->get_result();
    while ($amenity = $amenity_result->fetch_assoc()) {
        $amenities[] = $amenity;
    }
    $amenity_stmt->close();
}

// Fetch recent reviews
$reviews = [];
$review_sql = "SELECT r.*, c.first_name, c.last_name, c.email 
               FROM Reviews r 
               JOIN Customers c ON r.customer_id = c.id 
               WHERE r.room_id = ? 
               ORDER BY r.review_date DESC 
               LIMIT 5";
$review_stmt = $conn->prepare($review_sql);
if ($review_stmt) {
    $review_stmt->bind_param("i", $room_id);
    $review_stmt->execute();
    $review_result = $review_stmt->get_result();
    while ($review = $review_result->fetch_assoc()) {
        $reviews[] = $review;
    }
    $review_stmt->close();
}

// Fetch booking history
$bookings = [];
$booking_sql = "SELECT b.*, c.first_name, c.last_name, c.email 
                FROM Bookings b 
                JOIN Reservations res ON b.reservation_id = res.reservation_id
                JOIN Customers c ON res.customer_id = c.id 
                WHERE b.room_id = ? 
                ORDER BY b.actual_check_in DESC 
                LIMIT 10";
$booking_stmt = $conn->prepare($booking_sql);
if ($booking_stmt) {
    $booking_stmt->bind_param("i", $room_id);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    while ($booking = $booking_result->fetch_assoc()) {
        $bookings[] = $booking;
    }
    $booking_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room Details - Admin | Hotel System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #f59e0b;
            --accent-color: #10b981;
            --danger-color: #ef4444;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f8fafc;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg-light); 
            color: var(--text-dark);
        }

        .content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .room-hero {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .room-hero-content {
            display: flex;
            align-items: center;
            padding: 2rem;
        }

        .room-image-large {
            width: 300px;
            height: 200px;
            border-radius: 1rem;
            object-fit: cover;
            background: var(--bg-light);
            border: 2px solid var(--border-color);
            margin-right: 2rem;
        }

        .room-info-main h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .room-type-badge {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3730a3 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }

        .status-available {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-color);
        }

        .status-occupied {
            background: rgba(245, 158, 11, 0.1);
            color: var(--secondary-color);
        }

        .status-maintenance {
            background: rgba(107, 114, 128, 0.1);
            color: var(--text-light);
        }

        .price-display {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .detail-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-light);
        }

        .detail-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-card-body {
            padding: 1.5rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--text-dark);
        }

        .info-value {
            color: var(--text-light);
        }

        .amenity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: var(--bg-light);
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .amenity-name {
            font-weight: 500;
            color: var(--text-dark);
        }

        .amenity-quantity {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .review-item {
            padding: 1rem;
            background: var(--bg-light);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .reviewer-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .review-date {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .review-rating {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-bottom: 0.5rem;
        }

        .star {
            color: var(--secondary-color);
        }

        .review-text {
            color: var(--text-dark);
            line-height: 1.6;
        }

        .booking-item {
            padding: 1rem;
            background: var(--bg-light);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .guest-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .booking-dates {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .booking-status {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-confirmed {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-color);
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--secondary-color);
        }

        .status-cancelled {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3730a3 100%);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--text-light);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: var(--text-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .room-hero-content {
                flex-direction: column;
                text-align: center;
            }
            
            .room-image-large {
                margin-right: 0;
                margin-bottom: 1rem;
                width: 100%;
                max-width: 300px;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="header">
        <h2><i class="bi bi-door-open"></i>Room Details</h2>
        <div>
            <a href="edit_room.php?id=<?= $room_id ?>" class="btn btn-primary me-2">
                <i class="bi bi-pencil me-2"></i>Edit Room
            </a>
            <a href="rooms.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Rooms
            </a>
        </div>
    </div>

    <!-- Room Hero Section -->
    <div class="room-hero">
        <div class="room-hero-content">
            <?php
            $imageFile = $room['image'] ?? '';
            $imgSrc = '../assets/images/hotel.jpg'; // Default fallback
            
            if (!empty($imageFile)) {
                $roomImagePath = '../uploads/room_images/' . $imageFile;
                $fullImagePath = __DIR__ . '/../uploads/room_images/' . $imageFile;
                
                if (file_exists($fullImagePath)) {
                    $imgSrc = $roomImagePath;
                } else {
                    $assetsImagePath = '../assets/images/' . $imageFile;
                    $fullAssetsPath = __DIR__ . '/../assets/images/' . $imageFile;
                    
                    if (file_exists($fullAssetsPath)) {
                        $imgSrc = $assetsImagePath;
                    } else {
                        $roomType = strtolower($room['room_type_name']);
                        if (strpos($roomType, 'deluxe') !== false || strpos($roomType, 'delux') !== false) {
                            $imgSrc = '../assets/images/delux.jpg';
                        } elseif (strpos($roomType, 'suite') !== false) {
                            $imgSrc = '../assets/images/suite.jpg';
                        } elseif (strpos($roomType, 'standard') !== false) {
                            $imgSrc = '../assets/images/standard.jpg';
                        } else {
                            $imgSrc = '../assets/images/hotel.jpg';
                        }
                    }
                }
            } else {
                $roomType = strtolower($room['room_type_name']);
                if (strpos($roomType, 'deluxe') !== false || strpos($roomType, 'delux') !== false) {
                    $imgSrc = '../assets/images/delux.jpg';
                } elseif (strpos($roomType, 'suite') !== false) {
                    $imgSrc = '../assets/images/suite.jpg';
                } elseif (strpos($roomType, 'standard') !== false) {
                    $imgSrc = '../assets/images/standard.jpg';
                } else {
                    $imgSrc = '../assets/images/hotel.jpg';
                }
            }
            ?>
            <img src="<?= htmlspecialchars($imgSrc) ?>" 
                 class="room-image-large" 
                 alt="Room <?= $room['room_number'] ?>"
                 onerror="this.src='../assets/images/hotel.jpg'">
            
            <div class="room-info-main">
                <h3>Room <?= htmlspecialchars($room['room_number']) ?></h3>
                <span class="room-type-badge"><?= htmlspecialchars($room['room_type_name']) ?></span>
                
                <?php
                    $status = strtolower($room['status']);
                    $statusClass = 'status-available';
                    if ($status == 'occupied') {
                        $statusClass = 'status-occupied';
                    } elseif ($status == 'maintenance') {
                        $statusClass = 'status-maintenance';
                    }
                ?>
                <div class="status-badge <?= $statusClass ?>">
                    <?= ucfirst($room['status']) ?>
                </div>
                
                <div class="price-display">₹<?= number_format($room['price_per_night'], 0) ?> <small>/night</small></div>
                
                <p class="text-muted mb-0"><?= htmlspecialchars($room['description'] ?? $room['type_description'] ?? 'Luxury accommodation with modern amenities.') ?></p>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="details-grid">
        <!-- Room Information -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h5 class="detail-card-title">
                    <i class="bi bi-info-circle"></i>Room Information
                </h5>
            </div>
            <div class="detail-card-body">
                <div class="info-item">
                    <span class="info-label">Room Number</span>
                    <span class="info-value"><?= htmlspecialchars($room['room_number']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Room Type</span>
                    <span class="info-value"><?= htmlspecialchars($room['room_type_name']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Floor</span>
                    <span class="info-value"><?= htmlspecialchars($room['floor_number'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Capacity</span>
                    <span class="info-value"><?= htmlspecialchars($room['capacity'] ?? 'N/A') ?> guests</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Price per Night</span>
                    <span class="info-value">₹<?= number_format($room['price_per_night'], 0) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value"><?= ucfirst($room['status']) ?></span>
                </div>
            </div>
        </div>

        <!-- Amenities -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h5 class="detail-card-title">
                    <i class="bi bi-stars"></i>Amenities (<?= count($amenities) ?>)
                </h5>
            </div>
            <div class="detail-card-body">
                <?php if (!empty($amenities)): ?>
                    <?php foreach ($amenities as $amenity): ?>
                        <div class="amenity-item">
                            <div>
                                <div class="amenity-name"><?= htmlspecialchars($amenity['amenity_name']) ?></div>
                                <?php if (!empty($amenity['amenity_description'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($amenity['amenity_description']) ?></small>
                                <?php endif; ?>
                            </div>
                            <span class="amenity-quantity"><?= $amenity['quantity'] ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">No amenities assigned to this room.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviews -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h5 class="detail-card-title">
                    <i class="bi bi-star"></i>Reviews (<?= $room['review_count'] ?>)
                </h5>
            </div>
            <div class="detail-card-body">
                <?php if ($room['avg_rating'] > 0): ?>
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <span class="h4 mb-0 me-2"><?= number_format($room['avg_rating'], 1) ?></span>
                            <div class="d-flex">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star-fill star <?= $i <= $room['avg_rating'] ? '' : 'text-muted' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <small class="text-muted">Based on <?= $room['review_count'] ?> reviews</small>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <span class="reviewer-name"><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></span>
                                <span class="review-date"><?= date('M d, Y', strtotime($review['review_date'])) ?></span>
                            </div>
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star-fill star <?= $i <= $review['rating'] ? '' : 'text-muted' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="review-text"><?= htmlspecialchars($review['comment']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">No reviews yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h5 class="detail-card-title">
                    <i class="bi bi-calendar-check"></i>Recent Bookings
                </h5>
            </div>
            <div class="detail-card-body">
                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-item">
                            <div class="booking-header">
                                <span class="guest-name"><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></span>
                                <span class="booking-status status-<?= strtolower($booking['status']) ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </div>
                                                         <div class="booking-dates">
                                 <?= date('M d, Y', strtotime($booking['actual_check_in'])) ?> - 
                                 <?= $booking['actual_check_out'] ? date('M d, Y', strtotime($booking['actual_check_out'])) : 'Ongoing' ?>
                             </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">No booking history available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 