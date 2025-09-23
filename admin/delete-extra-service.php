<?php
include_once '../config/configdatabse.php';
header('Content-Type: text/plain');

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])){
    $id = intval($_POST['id']);
    $sql = "DELETE FROM ExtraServices WHERE service_id=$id";
    echo $conn->query($sql) ? 'success' : 'error';
}
