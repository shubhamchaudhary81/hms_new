<?php
header('Content-Type: application/json');
include_once '../config/configdatabse.php'; // database connection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Collect POST data
$room_number = $_POST['room_number'] ?? '';
$room_type_id = $_POST['room_type_id'] ?? '';
$capacity = $_POST['capacity'] ?? '';
$price_per_night = $_POST['price_per_night'] ?? '';
$amenities = $_POST['amenities'] ?? [];

// Handle image upload (store only first image in rooms table)
$imagePath = '';
if (!empty($_FILES['images']['tmp_name'][0])) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $firstImageTmp = $_FILES['images']['tmp_name'][0];
    $firstImageName = time() . "_" . basename($_FILES['images']['name'][0]);
    if (move_uploaded_file($firstImageTmp, $targetDir . $firstImageName)) {
        $imagePath = $targetDir . $firstImageName;
    }
}

// Insert into rooms table
$stmt = $conn->prepare("INSERT INTO room (room_number, room_type_id, capacity, price_per_night, image) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiids", $room_number, $room_type_id, $capacity, $price_per_night, $imagePath);

if ($stmt->execute()) {
    $room_id = $stmt->insert_id;

    // Insert amenities
    if (is_array($amenities) && count($amenities) > 0) {
        $aStmt = $conn->prepare("INSERT INTO room_amenities (room_id, amenity_id) VALUES (?, ?)");
        foreach ($amenities as $amenity_id) {
            $aStmt->bind_param("ii", $room_id, $amenity_id);
            $aStmt->execute();
        }
        $aStmt->close();
    }

    echo json_encode(["success" => true, "room_id" => $room_id]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$stmt->close();
$conn->close();
?>
