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
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

// Validate input
if ($room_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
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
    $check_query = "SELECT room_id, room_number, status FROM Room WHERE room_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $room_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit();
    }
    
    $room = $result->fetch_assoc();
    $old_status = $room['status'];
    
    // Update room status
    $update_query = "UPDATE Room SET status = ? WHERE room_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $status, $room_id);
    
    if ($update_stmt->execute()) {
        // Log the status change if note is provided
        if (!empty($note)) {
            $log_query = "INSERT INTO RoomStatusLog (room_id, old_status, new_status, note, updated_at) VALUES (?, ?, ?, ?, NOW())";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("isss", $room_id, $old_status, $status, $note);
            $log_stmt->execute();
        }
        
        echo json_encode([
            'success' => true, 
            'message' => "Room {$room['room_number']} status updated successfully from {$old_status} to {$status}"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update room status']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?> 