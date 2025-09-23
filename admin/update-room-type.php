<?php
include_once '../config/configdatabse.php';
header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['room_type_id']);
    $name = $conn->real_escape_string($_POST['room_type_name']);
    $description = $conn->real_escape_string($_POST['description']);

    $sql = "UPDATE RoomType 
            SET room_type_name='$name',
                description='$description'
            WHERE room_type_id=$id";

    if ($conn->query($sql)) {
        echo "success";
    } else {
        echo "error: " . $conn->error; // Show MySQL error for debugging
    }
}
?>