<?php
session_start();
include_once '../config/configdatabse.php';

if (!isset($_SESSION['customer_id'])) {
    die("Access denied. Please login first.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int) $_SESSION['customer_id'];
    $room_type_id = (int) $_POST['room_type_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $num_guests = (int) $_POST['num_guests'];
    $estimated_total = (float) $_POST['estimated_total_amount'];
    $special_requests = trim($_POST['special_requests']);
    $status = 'pending';

    if (strtotime($check_in) >= strtotime($check_out)) {
        die("Check-out must be after check-in.");
    }

    $stmt = $conn->prepare("INSERT INTO Reservations 
        (customer_id, room_type_id, requested_check_in_date, requested_check_out_date, num_guests, estimated_total_amount, status, special_requests)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissidss", $customer_id, $room_type_id, $check_in, $check_out, $num_guests, $estimated_total, $status, $special_requests);

    if ($stmt->execute()) {
        echo "<script>alert('Reservation successful!'); window.location.href='book.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
