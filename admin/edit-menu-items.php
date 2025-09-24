<?php
include_once '../config/configdatabse.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM MenuItems WHERE menu_item_id=$id");
    if ($res->num_rows > 0) {
        echo json_encode($res->fetch_assoc());
    } else {
        echo json_encode(null);
    }
}
?>