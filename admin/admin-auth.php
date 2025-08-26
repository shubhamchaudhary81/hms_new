<?php
if ($_SESSION['admin_id'] == "" || $_SESSION['admin_name'] == "") {
    header("Location: ../login.php");
    exit();
}   