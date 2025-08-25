<?php
include_once '../config/configdatabse.php';

if (!isset($_POST['reservation_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo "Missing required parameters.";
    exit;
}

$reservation_id = intval($_POST['reservation_id']);
$status = trim($_POST['status']);

// Validate status
$allowed_statuses = ['pending', 'confirmed', 'cancelled', 'checked_in', 'checked_out'];
if (!in_array(strtolower($status), $allowed_statuses)) {
    http_response_code(400);
    echo "Invalid status.";
    exit;
}

// Update reservation status
$stmt = $conn->prepare("UPDATE Reservations SET status = ? WHERE reservation_id = ?");
$stmt->bind_param("si", $status, $reservation_id);

if ($stmt->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "Failed to update status: " . $conn->error;
}

$stmt->close();
?>
