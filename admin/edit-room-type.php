<?php
include_once '../config/configdatabse.php';
header('Content-Type: application/json');

// Optional: suppress notices in production
error_reporting(E_ERROR | E_PARSE);

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM RoomType WHERE room_type_id = $id");

    if ($result && $result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(null);
    }
    exit;
}
?>