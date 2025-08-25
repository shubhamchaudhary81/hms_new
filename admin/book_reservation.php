<?php
include_once '../config/configdatabse.php';

// Set default timezone to Nepal
date_default_timezone_set('Asia/Kathmandu');

// Get current date and time (Nepali time)
$currentDateTime = date('Y-m-d H:i:s');

if (!isset($_POST['reservation_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo "Invalid request.";
    exit;
}

$reservation_id = intval($_POST['reservation_id']);
$status = $_POST['status'];

if ($status === 'confirmed') {
    // Get reservation details
    $stmt = $conn->prepare("SELECT requested_check_in_date, room_type_id FROM Reservations WHERE reservation_id = ?");
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $stmt->bind_result($requested_check_in, $room_type_id);
    $stmt->fetch();
    $stmt->close();

    // Find an available room of the requested type
    $room_stmt = $conn->prepare("SELECT room_id FROM Room WHERE room_type = ? AND status = 'available' LIMIT 1");
    $room_stmt->bind_param("i", $room_type_id);
    $room_stmt->execute();
    $room_stmt->bind_result($room_id);
    $room_stmt->fetch();
    $room_stmt->close();

    if (empty($room_id)) {
        http_response_code(409);
        echo "No available room for this type.";
        exit;
    }

    // Use the current Nepal time for booking
    $actual_check_in = date('Y-m-d H:i:s'); // accurate to the second

    // Check if already booked
    $check_stmt = $conn->prepare("SELECT booking_id FROM Bookings WHERE reservation_id = ?");
    $check_stmt->bind_param("i", $reservation_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
        $check_stmt->close();
        echo "Already booked.";
        exit;
    }
    $check_stmt->close();

    // Get advance amount from POST (default 0)
    $advance_amount = isset($_POST['advance_amount']) ? floatval($_POST['advance_amount']) : 0;
    // Insert into Bookings
    $insert_stmt = $conn->prepare("INSERT INTO Bookings (reservation_id, room_id, actual_check_in, advance_amount, status, notes) VALUES (?, ?, ?, ?, 'confirmed', NULL)");
    $insert_stmt->bind_param("iisd", $reservation_id, $room_id, $actual_check_in, $advance_amount);
    if ($insert_stmt->execute()) {
        // Optionally, update room status to 'booked'
        $conn->query("UPDATE Room SET status = 'booked' WHERE room_id = $room_id");
        // Update reservation status to 'confirmed'
        $update_res_stmt = $conn->prepare("UPDATE Reservations SET status = 'confirmed' WHERE reservation_id = ?");
        $update_res_stmt->bind_param("i", $reservation_id);
        $update_res_stmt->execute();
        $update_res_stmt->close();
        echo "success";
    } else {
        http_response_code(500);
        echo "Failed to insert booking.";
    }
    $insert_stmt->close();
} else {
    // For cancel, just update reservation status
    $stmt = $conn->prepare("UPDATE Reservations SET status = ? WHERE reservation_id = ?");
    $stmt->bind_param("si", $status, $reservation_id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Failed to update status.";
    }
    $stmt->close();
}
?>
