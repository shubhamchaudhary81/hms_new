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

// Handle room deletion
if (isset($_GET['delete_room']) && is_numeric($_GET['delete_room'])) {
    $room_id = (int)$_GET['delete_room'];
    $stmt = $conn->prepare("DELETE FROM Room WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Room deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting room: " . $stmt->error;
    }
    $stmt->close();
    header("Location: rooms.php");
    exit();
}

// Handle room status update
if (isset($_POST['update_status'])) {
    $room_id = (int)$_POST['room_id'];
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE Room SET status = ? WHERE room_id = ?");
    $stmt->bind_param("si", $new_status, $room_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Room status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating room status: " . $stmt->error;
    }
    $stmt->close();
    header("Location: rooms.php");
    exit();
}

// Handle room deletion
if (isset($_GET['delete_room']) && is_numeric($_GET['delete_room'])) {
    $room_id = (int)$_GET['delete_room'];
    $stmt = $conn->prepare("DELETE FROM Room WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Room deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting room: " . $stmt->error;
    }
    $stmt->close();
    header("Location: rooms.php");
    exit();
}

// Handle room status update
if (isset($_POST['update_status'])) {
    $room_id = (int)$_POST['room_id'];
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE Room SET status = ? WHERE room_id = ?");
    $stmt->bind_param("si", $new_status, $room_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Room status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating room status: " . $stmt->error;
    }
    $stmt->close();
    header("Location: rooms.php");
    exit();
}

// Add Room Type
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room_type'])) {
    $room_type_name = trim($_POST['room_type_name']);
    $description = trim($_POST['description']);
    if (!empty($room_type_name) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO RoomType (room_type_name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $room_type_name, $description);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Room Type added successfully!";
            header("Location: rooms.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
            header("Location: rooms.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['warning_message'] = "Please fill all required fields properly.";
        header("Location: rooms.php");
        exit();
    }
}

// Add Amenity
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_amenity'])) {
    $amenity_name = trim($_POST['amenity_name']);
    $description = trim($_POST['description']);
    $icon_url = isset($_POST['icon_url']) ? trim($_POST['icon_url']) : null;
    if (!empty($amenity_name)) {
        $stmt = $conn->prepare("INSERT INTO Amenity (amenity_name, description, icon_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $amenity_name, $description, $icon_url);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Amenity added successfully!";
            header("Location: rooms.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
            header("Location: rooms.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['warning_message'] = "Please enter an amenity name.";
        header("Location: rooms.php");
        exit();
    }
}

// Add Room Service
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_service'])) {
    $service_name = trim($_POST['service_name']);
    $description = trim($_POST['description']);
    $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
    $availability_status = isset($_POST['availability_status']) ? trim($_POST['availability_status']) : 'available';
    if (!empty($service_name) && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO RoomService (service_name, description, price, availability_status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $service_name, $description, $price, $availability_status);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Room Service added successfully!";
            header("Location: rooms.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
            header("Location: rooms.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['warning_message'] = "Please fill all required fields properly.";
        header("Location: rooms.php");
        exit();
    }
}

// Add Amenities (icon_url)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_amenities'])) {
    $amenity_name = trim($_POST['amenity_name']);
    $description = trim($_POST['description']);
    $icon_url = trim($_POST['icon_url']);
    if (!empty($amenity_name)) {
        $stmt = $conn->prepare("INSERT INTO Amenity (amenity_name, description, icon_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $amenity_name, $description, $icon_url);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Amenity added successfully!";
            header("Location: rooms.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
            header("Location: rooms.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['warning_message'] = "Please enter an amenity name.";
        header("Location: rooms.php");
        exit();
    }
}

// Add Room Amenity
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room_amenity'])) {
    $room_id = (int) $_POST['room_id'];
    $amenity_id = (int) $_POST['amenity_id'];
    $quantity = (int) $_POST['quantity'];
    if ($room_id > 0 && $amenity_id > 0 && $quantity > 0) {
        $stmt = $conn->prepare("INSERT INTO RoomAmenity (room_id, amenity_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $room_id, $amenity_id, $quantity);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Room Amenity added successfully!";
            header("Location: rooms.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
            header("Location: rooms.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['warning_message'] = "Please fill all required fields properly.";
        header("Location: rooms.php");
        exit();
    }
}

// Pagination setup
$rooms_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rooms_per_page;

// Get total number of rooms
$total_rooms_result = $conn->query("SELECT COUNT(*) as total FROM Room");
$total_rooms_row = $total_rooms_result->fetch_assoc();
$total_rooms = $total_rooms_row['total'];
$total_pages = ceil($total_rooms / $rooms_per_page);

// Fetch rooms for current page with enhanced data
$sql = "SELECT r.*, rt.room_type_name, rt.description as type_description,
        (SELECT COUNT(*) FROM RoomAmenity ra WHERE ra.room_id = r.room_id) as amenity_count,
        (SELECT COUNT(*) FROM reviews rev WHERE rev.room_id = r.room_id) as review_count,
        (SELECT AVG(rating) FROM reviews rev WHERE rev.room_id = r.room_id) as avg_rating
        FROM Room r
        JOIN RoomType rt ON r.room_type = rt.room_type_id
        ORDER BY r.room_number ASC
        LIMIT $rooms_per_page OFFSET $offset";
$result = $conn->query($sql);

$rooms = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Get amenities for this room
        $amenity_sql = "SELECT a.amenity_name FROM RoomAmenity ra 
                        JOIN Amenity a ON ra.amenity_id = a.amenity_id 
                        WHERE ra.room_id = {$row['room_id']} LIMIT 3";
        $amenity_res = $conn->query($amenity_sql);
        $amenities = [];
        if ($amenity_res) {
            while ($amenity = $amenity_res->fetch_assoc()) {
                $amenities[] = $amenity['amenity_name'];
            }
        }
        $row['amenities'] = $amenities;
        $rooms[] = $row;
    }
}

// Get room types for dropdown
$room_types = [];
$room_types_result = $conn->query("SELECT room_type_id, room_type_name FROM RoomType ORDER BY room_type_name");
if ($room_types_result) {
    while ($type = $room_types_result->fetch_assoc()) {
        $room_types[] = $type;
    }
}

// Get amenities for dropdown
$amenities = [];
$amenities_result = $conn->query("SELECT amenity_id, amenity_name FROM Amenity ORDER BY amenity_name");
if ($amenities_result) {
    while ($amenity = $amenities_result->fetch_assoc()) {
        $amenities[] = $amenity;
    }
}
?>
   
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room Management - Admin | Hotel System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color:  #5d4037;
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

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .table-container {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-light);
        }

        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }

        .table {
            margin: 0;
        }

        .table th {
            background: var(--bg-light);
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background: var(--bg-light);
        }

        .room-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .room-image {
            width: 60px;
            height: 60px;
            border-radius: 0.5rem;
            object-fit: cover;
            background: var(--bg-light);
        }

        .room-details h6 {
            font-weight: 600;
            color: var(--text-dark);
            margin: 0 0 0.25rem 0;
        }

        .room-details p {
            color: var(--text-light);
            font-size: 0.875rem;
            margin: 0;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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
            font-weight: 700;
            color: var(--accent-color);
            font-size: 1.125rem;
        }

        .amenities-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .amenity-tag {
            background: rgba(30, 58, 138, 0.1);
            color: var(--primary-color);
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
        }

        .btn-edit {
            background: rgba(30, 58, 138, 0.1);
            color: var(--primary-color);
        }

        .btn-edit:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .btn-delete:hover {
            background: var(--danger-color);
            color: white;
            transform: translateY(-1px);
        }

        .btn-view {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-color);
        }

        .btn-view:hover {
            background: var(--accent-color);
            color: white;
            transform: translateY(-1px);
        }

        .btn-status {
            background: rgba(245, 158, 11, 0.1);
            color: var(--secondary-color);
        }

        .btn-status:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-1px);
        }

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-light);
        }

        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }

        .table {
            margin: 0;
        }

        .table th {
            background: var(--bg-light);
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background: var(--bg-light);
        }

        .room-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .room-image {
            width: 60px;
            height: 60px;
            border-radius: 0.5rem;
            object-fit: cover;
            background: var(--bg-light);
        }

        .room-details h6 {
            font-weight: 600;
            color: var(--text-dark);
            margin: 0 0 0.25rem 0;
        }

        .room-details p {
            color: var(--text-light);
            font-size: 0.875rem;
            margin: 0;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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
            font-weight: 700;
            color: var(--accent-color);
            font-size: 1.125rem;
        }

        .amenities-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .amenity-tag {
            background: rgba(30, 58, 138, 0.1);
            color: var(--primary-color);
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
        }

        .btn-edit {
            background: rgba(30, 58, 138, 0.1);
            color: var(--primary-color);
        }

        .btn-edit:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .btn-delete:hover {
            background: var(--danger-color);
            color: white;
            transform: translateY(-1px);
        }

        .btn-view {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-color);
        }

        .btn-view:hover {
            background: var(--accent-color);
            color: white;
            transform: translateY(-1px);
        }

        .btn-status {
            background: rgba(245, 158, 11, 0.1);
            color: var(--secondary-color);
        }

        .btn-status:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-1px);
        }

        .pagination-container {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
            background: var(--bg-light);
        }

        .page-info {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .page-info strong {
            color: var(--text-dark);
        }

        .pagination .page-link {
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            padding: 0.5rem 0.75rem;
            margin: 0 0.125rem;
            border-radius: 0.375rem;
        }

        .pagination .page-link:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .pagination .page-item.disabled .page-link {
            color: var(--text-light);
            background: var(--bg-light);
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

        .modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            border-radius: 1rem 1rem 0 0;
            border-bottom: none;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-close-white {
            filter: invert(1);
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
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
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
        <h2><i class="bi bi-door-open"></i>Room Management</h2>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="addDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-plus-circle me-2"></i>Add New
            </button>
            <ul class="dropdown-menu" aria-labelledby="addDropdown">
                <li><a class="dropdown-item" href="addroom.php"><i class="bi bi-plus-square me-2"></i>Add Room</a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addRoomTypeModal"><i class="bi bi-house me-2"></i>Add Room Type</a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addAmenityModal"><i class="bi bi-stars me-2"></i>Add Amenity</a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addServiceModal"><i class="bi bi-cone-striped me-2"></i>Add Service</a></li>
            </ul>
        </div>
    </div>

    <!-- Statistics Cards -->
    <!-- <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon text-primary">
                <i class="bi bi-door-open"></i>
            </div>
            <div class="stat-number"><?= $total_rooms ?></div>
            <div class="stat-label">Total Rooms</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-success">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-number">
                <?php 
                $available_count = $conn->query("SELECT COUNT(*) as count FROM Room WHERE status = 'available'")->fetch_assoc()['count'];
                echo $available_count;
                ?>
            </div>
            <div class="stat-label">Available</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-warning">
                <i class="bi bi-exclamation-circle"></i>
            </div>
            <div class="stat-number">
                <?php 
                $occupied_count = $conn->query("SELECT COUNT(*) as count FROM Room WHERE status = 'occupied'")->fetch_assoc()['count'];
                echo $occupied_count;
                ?>
            </div>
            <div class="stat-label">Occupied</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-secondary">
                <i class="bi bi-tools"></i>
            </div>
            <div class="stat-number">
                <?php 
                $maintenance_count = $conn->query("SELECT COUNT(*) as count FROM Room WHERE status = 'maintenance'")->fetch_assoc()['count'];
                echo $maintenance_count;
                ?>
            </div>
            <div class="stat-label">Maintenance</div>
        </div>
    </div> -->

    <!-- Statistics Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon text-primary">
                <i class="bi bi-door-open"></i>
            </div>
            <div class="stat-number"><?= $total_rooms ?></div>
            <div class="stat-label">Total Rooms</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-success">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-number">
                <?php 
                $available_count = $conn->query("SELECT COUNT(*) as count FROM Room WHERE status = 'available'")->fetch_assoc()['count'];
                echo $available_count;
                ?>
            </div>
            <div class="stat-label">Available</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-warning">
                <i class="bi bi-exclamation-circle"></i>
            </div>
            <div class="stat-number">
                <?php 
                $occupied_count = $conn->query("SELECT COUNT(*) as count FROM Room WHERE status = 'occupied'")->fetch_assoc()['count'];
                echo $occupied_count;
                ?>
            </div>
            <div class="stat-label">Occupied</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon text-secondary">
                <i class="bi bi-tools"></i>
            </div>
            <div class="stat-number">
                <?php 
                $maintenance_count = $conn->query("SELECT COUNT(*) as count FROM Room WHERE status = 'maintenance'")->fetch_assoc()['count'];
                echo $maintenance_count;
                ?>
            </div>
            <div class="stat-label">Maintenance</div>
        </div>
    </div>
    
    <div class="table-container">
        <div class="table-header">
            <h5 class="table-title"><i class="bi bi-list-ul me-2"></i>Room Inventory</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Type & Details</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Amenities</th>
                        <th>Rating</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td>
                                <div class="room-info">
                                    <?php
                                    $imageFile = $room['image'] ?? '';
                                    $imgSrc = '../assets/images/hotel.jpg'; // Default fallback
                                    
                                    if (!empty($imageFile)) {
                                        // Check if image exists in room_images folder
                                        $roomImagePath = '../uploads/room_images/' . $imageFile;
                                        $fullImagePath = __DIR__ . '/../uploads/room_images/' . $imageFile;
                                        
                                        if (file_exists($fullImagePath)) {
                                            $imgSrc = $roomImagePath;
                                        } else {
                                            // Fallback to assets/images
                                            $assetsImagePath = '../assets/images/' . $imageFile;
                                            $fullAssetsPath = __DIR__ . '/../assets/images/' . $imageFile;
                                            
                                            if (file_exists($fullAssetsPath)) {
                                                $imgSrc = $assetsImagePath;
                                            } else {
                                                // Use room type-specific default
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
                                        // No image specified, use room type-specific default
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
                                         class="room-image" 
                                         alt="Room <?= $room['room_number'] ?>"
                                         onerror="this.src='../assets/images/hotel.jpg'">
                                    <div class="room-details">
                                        <h6>Room <?= htmlspecialchars($room['room_number']) ?></h6>
                                        <p>Floor <?= htmlspecialchars($room['floor_number'] ?? 'N/A') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="room-details">
                                    <h6><?= htmlspecialchars($room['room_type_name']) ?></h6>
                                    <p><?= htmlspecialchars($room['type_description'] ?? 'Luxury accommodation') ?></p>
                                    <small class="text-muted">Capacity: <?= htmlspecialchars($room['capacity'] ?? 'N/A') ?> guests</small>
                                </div>
                            </td>
                            <td>
                                <?php
                                    $status = strtolower($room['status']);
                                    $statusClass = 'status-available';
                                    $statusIcon = 'bi-check-circle-fill';
                                    if ($status == 'occupied') {
                                        $statusClass = 'status-occupied';
                                        $statusIcon = 'bi-exclamation-circle-fill';
                                    } elseif ($status == 'maintenance') {
                                        $statusClass = 'status-maintenance';
                                        $statusIcon = 'bi-tools';
                                    }
                                ?>
                                <span class="status-badge <?= $statusClass ?>">
                                    <i class="bi <?= $statusIcon ?> me-1"></i><?= ucfirst($room['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="price-display">₹<?= number_format($room['price_per_night'], 0) ?></div>
                                <small class="text-muted">per night</small>
                            </td>
                            <td>
                                <?php if (!empty($room['amenities'])): ?>
                                    <div class="amenities-list">
                                        <?php foreach (array_slice($room['amenities'], 0, 2) as $amenity): ?>
                                            <span class="amenity-tag"><?= htmlspecialchars($amenity) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($room['amenities']) > 2): ?>
                                            <span class="amenity-tag">+<?= count($room['amenities']) - 2 ?> more</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No amenities</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($room['avg_rating'] > 0): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-star-fill text-warning me-1"></i>
                                        <span class="fw-bold"><?= number_format($room['avg_rating'], 1) ?></span>
                                        <small class="text-muted ms-1">(<?= $room['review_count'] ?>)</small>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No reviews</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" onclick="viewRoom(<?= $room['room_id'] ?>)" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editRoom(<?= $room['room_id'] ?>)" title="Edit Room">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn-action btn-status" onclick="changeStatus(<?= $room['room_id'] ?>, '<?= $room['status'] ?>')" title="Change Status">
                                        <i class="bi bi-gear"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteRoom(<?= $room['room_id'] ?>)" title="Delete Room">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="page-info">
                    Showing <strong><?= $offset + 1 ?></strong>
                    to <strong><?= min($offset + $rooms_per_page, $total_rooms) ?></strong>
                    of <strong><?= $total_rooms ?></strong> rooms
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Room Type Modal -->
<div class="modal fade" id="addRoomTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-house me-2"></i>Add Room Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="add_room_type" value="1">
                    <div class="mb-3">
                        <label class="form-label">Room Type Name</label>
                        <input type="text" class="form-control" name="room_type_name" placeholder="e.g. Deluxe, Suite, Standard" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" name="description" placeholder="Type description" required></textarea>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Room Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Amenity Modal -->
<div class="modal fade" id="addAmenityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-stars me-2"></i>Add Amenity</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="add_amenity" value="1">
                    <div class="mb-3">
                        <label class="form-label">Amenity Name</label>
                        <input type="text" class="form-control" name="amenity_name" placeholder="e.g. Free WiFi, TV, AC" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" name="description" placeholder="Amenity description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon URL</label>
                        <input type="text" class="form-control" name="icon_url" placeholder="e.g. https://example.com/icon.png">
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Amenity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cone-striped me-2"></i>Add Room Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="add_service" value="1">
                    <div class="mb-3">
                        <label class="form-label">Service Name</label>
                        <input type="text" class="form-control" name="service_name" placeholder="e.g. Laundry, Room Cleaning" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" name="description" placeholder="Service description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" placeholder="Enter price" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Availability Status</label>
                        <select class="form-select" name="availability_status">
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-gear me-2"></i>Change Room Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="room_id" id="statusRoomId">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select class="form-select" name="status" required>
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
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

    // Room action functions
    function viewRoom(roomId) {
        // Redirect to room details page
        window.open(`room_details.php?id=${roomId}`, '_blank');
    }

    function editRoom(roomId) {
        // Redirect to edit room page
        window.location.href = `edit_room.php?id=${roomId}`;
    }

    function changeStatus(roomId, currentStatus) {
        // Set the room ID and current status in the modal
        document.getElementById('statusRoomId').value = roomId;
        
        // Set the current status as selected
        const statusSelect = document.querySelector('#statusModal select[name="status"]');
        statusSelect.value = currentStatus;
        
        // Show the modal
        const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        statusModal.show();
    }

    function deleteRoom(roomId) {
        if (confirm('Are you sure you want to delete this room? This action cannot be undone.')) {
            window.location.href = `?delete_room=${roomId}`;
        }
    }

    // Handle image loading and errors
    document.addEventListener('DOMContentLoaded', function() {
        const roomImages = document.querySelectorAll('.room-image');
        
        roomImages.forEach(img => {
            // Add loading class initially
            img.classList.add('loading');
            
            // Handle successful load
            img.addEventListener('load', function() {
                this.classList.remove('loading');
                this.classList.remove('error');
            });
            
            // Handle error
            img.addEventListener('error', function() {
                console.log('Image failed to load:', this.src);
                this.classList.remove('loading');
                this.classList.add('error');
                this.src = '../assets/images/hotel.jpg';
            });
            
            // If image is already loaded (cached), remove loading class
            if (img.complete) {
                img.classList.remove('loading');
            }
        });
    });
</script>
</body>
</html>