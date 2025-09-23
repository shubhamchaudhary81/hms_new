<?php
include_once '../config/configdatabse.php';
header('Content-Type: text/plain');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $name = $conn->real_escape_string($_POST['service_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);

    if($id > 0){
        // Update
        $sql = "UPDATE ExtraServices SET service_name='$name', description='$description', price=$price WHERE service_id=$id";
    } else {
        // Insert
        $sql = "INSERT INTO ExtraServices (service_name, description, price) VALUES ('$name', '$description', $price)";
    }

    echo $conn->query($sql) ? 'success' : 'error';
}
