<?php
include_once '../config/configdatabse.php';

if (!isset($_POST['id'])) {
    echo "Invalid request";
    exit;
}

$id = intval($_POST['id']);

// Optionally, delete related reservations first if you want to maintain referential integrity
$conn->query("DELETE FROM Reservations WHERE customer_id = $id");

// Delete the customer
$stmt = $conn->prepare("DELETE FROM Customers WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo "success";
} else {
    echo "Failed: " . $conn->error;
}
$stmt->close();
?> 