<?php
include_once '../config/configdatabse.php';
header('Content-Type: text/plain');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = isset($_POST['amenity_id']) ? intval($_POST['amenity_id']) : 0;
    $name = $conn->real_escape_string($_POST['amenity_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $icon_url = isset($_POST['icon_url']) ? $conn->real_escape_string($_POST['icon_url']) : '';

    if($id > 0){
        // Update
        $sql = "UPDATE Amenity SET amenity_name='$name', description='$description', icon_url='$icon_url' WHERE amenity_id=$id";
    } else {
        // Insert
        $sql = "INSERT INTO Amenity (amenity_name, description, icon_url) VALUES ('$name','$description','$icon_url')";
    }

    echo $conn->query($sql) ? 'success' : 'error';
}
