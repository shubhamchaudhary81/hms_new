<?php
include_once '../config/configdatabse.php';

if (!isset($_POST['reservation_id'])) {
    http_response_code(400);
    echo "Missing reservation ID.";
    exit;
}

$reservation_id = intval($_POST['reservation_id']);

// Get reservation details
$stmt = $conn->prepare("SELECT room_type_id FROM Reservations WHERE reservation_id = ? AND status = 'confirmed'");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo "Reservation not found or not confirmed.";
    exit;
}

$reservation = $result->fetch_assoc();
$room_type_id = $reservation['room_type_id'];
$stmt->close();

// Find an available room of the requested type
$room_stmt = $conn->prepare("SELECT room_id FROM Room WHERE room_type = ? AND status = 'available' LIMIT 1");
$room_stmt->bind_param("i", $room_type_id);
$room_stmt->execute();
$room_result = $room_stmt->get_result();

if ($room_result->num_rows === 0) {
    http_response_code(409);
    echo "No available room for this type.";
    exit;
}

$room = $room_result->fetch_assoc();
$room_id = $room['room_id'];
$room_stmt->close();

// Check if already has a booking
$check_stmt = $conn->prepare("SELECT booking_id FROM Bookings WHERE reservation_id = ?");
$check_stmt->bind_param("i", $reservation_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing booking
    $update_stmt = $conn->prepare("UPDATE Bookings SET status = 'checked_in' WHERE reservation_id = ?");
    $update_stmt->bind_param("i", $reservation_id);
    if ($update_stmt->execute()) {
        // Update room status
        $conn->query("UPDATE Room SET status = 'occupied' WHERE room_id = $room_id");
        // Update reservation status
        $conn->query("UPDATE Reservations SET status = 'checked_in' WHERE reservation_id = $reservation_id");
        echo "success";
    } else {
        http_response_code(500);
        echo "Failed to update booking.";
    }
    $update_stmt->close();
} else {
    // Create new booking
    $insert_stmt = $conn->prepare("INSERT INTO Bookings (reservation_id, room_id, actual_check_in, status) VALUES (?, ?, NOW(), 'checked_in')");
    $insert_stmt->bind_param("ii", $reservation_id, $room_id);
    if ($insert_stmt->execute()) {
        // Update room status
        $conn->query("UPDATE Room SET status = 'occupied' WHERE room_id = $room_id");
        // Update reservation status
        $conn->query("UPDATE Reservations SET status = 'checked_in' WHERE reservation_id = $reservation_id");
        echo "success";
    } else {
        http_response_code(500);
        echo "Failed to create booking.";
    }
    $insert_stmt->close();
}

$check_stmt->close();
?>
