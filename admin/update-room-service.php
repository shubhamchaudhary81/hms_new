<?php
include_once '../config/configdatabse.php';
header('Content-Type: text/plain');

if($_SERVER['REQUEST_METHOD']==='POST'){
    $id = isset($_POST['room_service_id']) ? intval($_POST['room_service_id']) : 0;
    $name = $conn->real_escape_string($_POST['service_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $availability = $conn->real_escape_string($_POST['availability_status']);

    if($id > 0){
        // Update existing
        $sql = "UPDATE RoomService SET 
                service_name='$name',
                description='$description',
                price=$price,
                availability_status='$availability'
                WHERE room_service_id=$id";
    } else {
        // Insert new
        $sql = "INSERT INTO RoomService (service_name, description, price, availability_status)
                VALUES ('$name', '$description', $price, '$availability')";
    }

    echo $conn->query($sql) ? "success" : $conn->error;
}
?>
