<?php
include_once '../config/configdatabse.php';
header('Content-Type: application/json');

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM ExtraServices WHERE service_id=$id";
    $result = $conn->query($sql);
    if($result->num_rows > 0){
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
