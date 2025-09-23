<?php
include_once '../config/configdatabse.php';
header('Content-Type: application/json');

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM Amenity WHERE amenity_id=$id";
    $result = $conn->query($sql);
    echo $result->num_rows > 0 ? json_encode($result->fetch_assoc()) : json_encode([]);
} else {
    echo json_encode([]);
}
