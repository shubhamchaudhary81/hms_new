<?php
include_once '../config/configdatabse.php';

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM RoomService WHERE room_service_id=$id");
    if($res->num_rows > 0){
        echo json_encode($res->fetch_assoc());
    } else {
        echo json_encode(null);
    }
}
?>
