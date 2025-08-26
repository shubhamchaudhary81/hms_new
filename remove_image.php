<?php
include 'config/configdatabse.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Soft delete → just mark as deleted
    $sql = "UPDATE gallery SET is_deleted = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: gallery.php?msg=removed");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
