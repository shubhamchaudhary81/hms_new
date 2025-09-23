<?php
include_once '../config/configdatabse.php';
header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM RoomType WHERE room_type_id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "success";
    } else {
        // Return the actual MySQL error for debugging
        echo "error: " . $conn->error;
    }
    exit;
}
?>
