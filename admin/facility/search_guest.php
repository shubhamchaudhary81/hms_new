<?php
include_once '../config/configdatabse.php';

if (isset($_GET['term'])) {
    $search = "%" . $conn->real_escape_string($_GET['term']) . "%";

    $sql = "
        SELECT 
            c.id AS customer_id,
            CONCAT(c.first_name, ' ', c.last_name) AS guest_name,
            r.room_number,
            b.booking_id
        FROM Customers c
        JOIN Reservations res ON c.id = res.customer_id
        JOIN Bookings b ON res.reservation_id = b.reservation_id
        JOIN Room r ON b.room_id = r.room_id
        WHERE CONCAT(c.first_name, ' ', c.last_name) LIKE ?
        LIMIT 10
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'label' => $row['guest_name'] . " (Room: " . $row['room_number'] . ")",
            'value' => $row['guest_name'],
            'customer_id' => $row['customer_id'],
            'booking_id' => $row['booking_id']
        ];
    }

    echo json_encode($data);
}
?>
