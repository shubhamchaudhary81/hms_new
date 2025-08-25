<?php
session_start();
include '../config/configdatabse.php';

// Initialize message variables from session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$warning_message = isset($_SESSION['warning_message']) ? $_SESSION['warning_message'] : '';

// Clear session messages after retrieving them
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
unset($_SESSION['warning_message']);

// Get room ID from URL
$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($room_id <= 0) {
    $_SESSION['error_message'] = "Invalid room ID.";
    header("Location: rooms.php");
    exit();
}

// Handle form submission for updating room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_room'])) {
    $room_number = trim($_POST['room_number']);
    $room_type = (int)$_POST['room_type'];
    $status = trim($_POST['status']);
    $price_per_night = (float)$_POST['price_per_night'];
    $capacity = (int)$_POST['capacity'];
    $floor_number = (int)$_POST['floor_number'];
    $description = trim($_POST['description']);
    
    if (!empty($room_number) && $room_type > 0 && $price_per_night > 0) {
        $stmt = $conn->prepare("UPDATE Room SET room_number = ?, room_type = ?, status = ?, price_per_night = ?, capacity = ?, floor_number = ?, description = ? WHERE room_id = ?");
        $stmt->bind_param("sisdiisi", $room_number, $room_type, $status, $price_per_night, $capacity, $floor_number, $description, $room_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Room updated successfully!";
            header("Location: rooms.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating room: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['warning_message'] = "Please fill all required fields properly.";
    }
}

// Fetch room data
$stmt = $conn->prepare("SELECT r.*, rt.room_type_name FROM Room r 
                        JOIN RoomType rt ON r.room_type = rt.room_type_id 
                        WHERE r.room_id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Room not found.";
    header("Location: rooms.php");
    exit();
}

$room = $result->fetch_assoc();
$stmt->close();

// Fetch room types for dropdown
$room_types = [];
$room_types_result = $conn->query("SELECT room_type_id, room_type_name FROM RoomType ORDER BY room_type_name");
if ($room_types_result) {
    while ($type = $room_types_result->fetch_assoc()) {
        $room_types[] = $type;
    }
}

// Fetch room amenities
$amenities = [];
$amenity_sql = "SELECT a.amenity_id, a.amenity_name, ra.quantity 
                FROM RoomAmenity ra 
                JOIN Amenity a ON ra.amenity_id = a.amenity_id 
                WHERE ra.room_id = ?";
$amenity_stmt = $conn->prepare($amenity_sql);
$amenity_stmt->bind_param("i", $room_id);
$amenity_stmt->execute();
$amenity_result = $amenity_stmt->get_result();
while ($amenity = $amenity_result->fetch_assoc()) {
    $amenities[] = $amenity;
}
$amenity_stmt->close();

// Fetch all available amenities for adding
$all_amenities = [];
$all_amenities_result = $conn->query("SELECT amenity_id, amenity_name FROM Amenity ORDER BY amenity_name");
if ($all_amenities_result) {
    while ($amenity = $all_amenities_result->fetch_assoc()) {
        $all_amenities[] = $amenity;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Room - Admin | Hotel System</title>
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

        .form-container {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .form-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-light);
        }

        .form-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }

        .form-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
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

        .room-image-preview {
            width: 200px;
            height: 150px;
            border-radius: 0.5rem;
            object-fit: cover;
            background: var(--bg-light);
            border: 2px solid var(--border-color);
        }

        .amenities-section {
            background: var(--bg-light);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .amenity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: white;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            border: 1px solid var(--border-color);
        }

        .amenity-name {
            font-weight: 500;
            color: var(--text-dark);
        }

        .amenity-quantity {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .btn-remove-amenity {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border: none;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            transition: all 0.2s ease;
        }

        .btn-remove-amenity:hover {
            background: var(--danger-color);
            color: white;
        }

        /* Custom Alert Styles */
        .custom-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            border-radius: 0.75rem;
            box-shadow: var(--shadow-lg);
            border: none;
            animation: slideInRight 0.3s ease-out;
        }
        
        .custom-alert.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .custom-alert.error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .custom-alert.warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .custom-alert .alert-icon {
            font-size: 1.2rem;
            margin-right: 10px;
        }
        
        .custom-alert .btn-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: inherit;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .custom-alert .btn-close:hover {
            opacity: 1;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .custom-alert.fade-out {
            animation: slideOutRight 0.3s ease-in forwards;
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
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<!-- Message Display Area -->
<?php if (!empty($success_message)): ?>
    <div class="custom-alert success" id="successAlert">
        <div class="d-flex align-items-center justify-content-between p-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill alert-icon"></i>
                <span><?= htmlspecialchars($success_message) ?></span>
            </div>
            <button type="button" class="btn-close" onclick="closeAlert('successAlert')">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="custom-alert error" id="errorAlert">
        <div class="d-flex align-items-center justify-content-between p-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
                <span><?= htmlspecialchars($error_message) ?></span>
            </div>
            <button type="button" class="btn-close" onclick="closeAlert('errorAlert')">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($warning_message)): ?>
    <div class="custom-alert warning" id="warningAlert">
        <div class="d-flex align-items-center justify-content-between p-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-circle-fill alert-icon"></i>
                <span><?= htmlspecialchars($warning_message) ?></span>
            </div>
            <button type="button" class="btn-close" onclick="closeAlert('warningAlert')">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<div class="content">
    <div class="header">
        <h2><i class="bi bi-pencil-square"></i>Edit Room</h2>
        <div>
            <a href="rooms.php" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Back to Rooms
            </a>
        </div>
    </div>

    <div class="form-container">
        <div class="form-header">
            <h5 class="form-title">Room <?= htmlspecialchars($room['room_number']) ?> - <?= htmlspecialchars($room['room_type_name']) ?></h5>
        </div>
        
        <div class="form-body">
            <form method="POST" action="">
                <input type="hidden" name="update_room" value="1">
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Room Number</label>
                                <input type="text" class="form-control" name="room_number" 
                                       value="<?= htmlspecialchars($room['room_number']) ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Room Type</label>
                                <select class="form-select" name="room_type" required>
                                    <?php foreach ($room_types as $type): ?>
                                        <option value="<?= $type['room_type_id'] ?>" 
                                                <?= $type['room_type_id'] == $room['room_type'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type['room_type_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="available" <?= $room['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="occupied" <?= $room['status'] == 'occupied' ? 'selected' : '' ?>>Occupied</option>
                                    <option value="maintenance" <?= $room['status'] == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Price per Night (₹)</label>
                                <input type="number" class="form-control" name="price_per_night" 
                                       value="<?= htmlspecialchars($room['price_per_night']) ?>" 
                                       min="0" step="0.01" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Capacity</label>
                                <input type="number" class="form-control" name="capacity" 
                                       value="<?= htmlspecialchars($room['capacity'] ?? 2) ?>" 
                                       min="1" max="10" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Floor Number</label>
                                <input type="number" class="form-control" name="floor_number" 
                                       value="<?= htmlspecialchars($room['floor_number'] ?? 1) ?>" 
                                       min="1" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4" 
                                          placeholder="Enter room description"><?= htmlspecialchars($room['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="text-center mb-3">
                            <label class="form-label">Room Image</label>
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
                                 class="room-image-preview" 
                                 alt="Room <?= $room['room_number'] ?>"
                                 onerror="this.src='../assets/images/hotel.jpg'">
                        </div>
                        
                        <div class="amenities-section">
                            <h6 class="mb-3"><i class="bi bi-stars me-2"></i>Current Amenities</h6>
                            <?php if (!empty($amenities)): ?>
                                <?php foreach ($amenities as $amenity): ?>
                                    <div class="amenity-item">
                                        <div>
                                            <div class="amenity-name"><?= htmlspecialchars($amenity['amenity_name']) ?></div>
                                            <div class="amenity-quantity">Quantity: <?= $amenity['quantity'] ?></div>
                                        </div>
                                        <button type="button" class="btn-remove-amenity" 
                                                onclick="removeAmenity(<?= $amenity['amenity_id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0">No amenities assigned</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="text-end mt-4">
                    <a href="rooms.php" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Update Room
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Function to close alerts
    function closeAlert(alertId) {
        const alert = document.getElementById(alertId);
        if (alert) {
            alert.classList.add('fade-out');
            setTimeout(() => {
                alert.remove();
            }, 300);
        }
    }
    
    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.custom-alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    alert.classList.add('fade-out');
                    setTimeout(() => {
                        if (alert && alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                }
            }, 5000);
        });
    });

    function removeAmenity(amenityId) {
        if (confirm('Are you sure you want to remove this amenity from the room?')) {
            // You can implement AJAX call here to remove amenity
            // For now, we'll redirect to a remove amenity page
            window.location.href = `remove_room_amenity.php?room_id=<?= $room_id ?>&amenity_id=${amenityId}`;
        }
    }
</script>
</body>
</html> 