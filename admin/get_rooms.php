<?php
 // your DB connection
include_once '../config/configdatabse.php';


$typeId = $_GET['room_type_id'] ?? null;
$rooms = [];

if ($typeId) {
    $sql = "SELECT room_id, room_number 
            FROM Room 
            WHERE room_type = ?  AND status = 'available'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $typeId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($rooms);
