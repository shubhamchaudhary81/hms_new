<?php
session_start();
include_once '../config/configdatabse.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get POST data
$room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
$room_number = isset($_POST['room_number']) ? trim($_POST['room_number']) : '';
$floor_number = isset($_POST['floor_number']) ? (int)$_POST['floor_number'] : null;
$room_type = isset($_POST['room_type']) ? (int)$_POST['room_type'] : 0;
$capacity = isset($_POST['capacity']) ? (int)$_POST['capacity'] : null;
$price_per_night = isset($_POST['price_per_night']) ? (float)$_POST['price_per_night'] : 0;
$weekend_price = isset($_POST['weekend_price']) ? (float)$_POST['weekend_price'] : null;
$season_price = isset($_POST['season_price']) ? (float)$_POST['season_price'] : null;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Validate required fields
if ($room_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit();
}

if (empty($room_number)) {
    echo json_encode(['success' => false, 'message' => 'Room number is required']);
    exit();
}

if ($room_type <= 0) {
    echo json_encode(['success' => false, 'message' => 'Room type is required']);
    exit();
}

if ($price_per_night <= 0) {
    echo json_encode(['success' => false, 'message' => 'Price per night must be greater than 0']);
    exit();
}

if (empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Status is required']);
    exit();
}

// Validate status values
$valid_statuses = ['available', 'booked', 'maintenance'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit();
}

try {
    // Check if room exists
    $check_query = "SELECT room_id, room_number FROM Room WHERE room_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $room_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit();
    }
    
    $room = $result->fetch_assoc();
    
    // Check if room number already exists (excluding current room)
    $duplicate_query = "SELECT room_id FROM Room WHERE room_number = ? AND room_id != ?";
    $duplicate_stmt = $conn->prepare($duplicate_query);
    $duplicate_stmt->bind_param("si", $room_number, $room_id);
    $duplicate_stmt->execute();
    $duplicate_result = $duplicate_stmt->get_result();
    
    if ($duplicate_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Room number already exists']);
        exit();
    }
    
    // Update room
    $update_query = "UPDATE Room SET 
        room_number = ?, 
        floor_number = ?, 
        room_type = ?, 
        capacity = ?, 
        price_per_night = ?, 
        weekend_price = ?, 
        season_price = ?, 
        status = ?, 
        description = ? 
        WHERE room_id = ?";
    
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("siidddsssi", 
        $room_number, 
        $floor_number, 
        $room_type, 
        $capacity, 
        $price_per_night, 
        $weekend_price, 
        $season_price, 
        $status, 
        $description, 
        $room_id
    );
    
    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => "Room {$room_number} updated successfully"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update room']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?> 