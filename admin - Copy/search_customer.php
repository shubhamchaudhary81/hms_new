<?php
include_once '../config/configdatabse.php';
header('Content-Type: application/json');

if (isset($_GET['term'])) {
    $term = '%' . $conn->real_escape_string($_GET['term']) . '%';
    $result = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM Customers WHERE first_name LIKE '$term' OR last_name LIKE '$term' OR CONCAT(first_name, ' ', last_name) LIKE '$term' LIMIT 10");
    $out = [];
    while ($row = $result->fetch_assoc()) {
        $out[] = [
            'id' => $row['id'],
            'label' => $row['name'],
            'value' => $row['name']
        ];
    }
    echo json_encode($out);
    exit;
}

if (isset($_GET['customer_id'])) {
    $customer_id = intval($_GET['customer_id']);
    // Remove status filter for debugging
    $result = $conn->query("SELECT booking_id, status FROM Bookings WHERE customer_id = $customer_id");
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    // Debug output
    // file_put_contents('debug.log', print_r($bookings, true));
    echo json_encode(['bookings' => $bookings]);
    exit;
}

echo json_encode([]); 