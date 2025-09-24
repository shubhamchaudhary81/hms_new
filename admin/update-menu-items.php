<?php
include_once '../config/configdatabse.php';

header('Content-Type: text/plain');

$menu_item_id = isset($_POST['menu_item_id']) ? intval($_POST['menu_item_id']) : 0;
$name = $conn->real_escape_string($_POST['item_name']);
$description = $conn->real_escape_string($_POST['item_description']);
$price = floatval($_POST['price']);
$type = $conn->real_escape_string($_POST['item_type']);
$category = $conn->real_escape_string($_POST['category']);
$availability = intval($_POST['is_available']);
$imagePath = '';

// Handle image upload
if (isset($_FILES['menu_image_file']) && $_FILES['menu_image_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/menu_items/'; // create this folder if it doesn't exist
    if (!is_dir($uploadDir))
        mkdir($uploadDir, 0755, true);

    $filename = time() . '_' . basename($_FILES['menu_image_file']['name']);
    $targetFile = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['menu_image_file']['tmp_name'], $targetFile)) {
        // Save relative path for database
        $imagePath = '../uploads/menu_items/' . $filename;
    }
}

// Update or insert
if ($menu_item_id > 0) {
    $sql = "UPDATE MenuItems SET 
            item_name='$name',
            item_description='$description',
            price=$price,
            item_type='$type',
            category='$category',
            is_available=$availability";
    if ($imagePath !== '') {
        $sql .= ", menu_image='$imagePath'";
    }
    $sql .= " WHERE menu_item_id=$menu_item_id";
} else {
    $sql = "INSERT INTO MenuItems (item_name,item_description,price,item_type,category,is_available,menu_image)
            VALUES ('$name','$description',$price,'$type','$category',$availability,'$imagePath')";
}

echo $conn->query($sql) ? 'success' : $conn->error;
?>