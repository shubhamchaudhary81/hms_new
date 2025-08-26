<?php
session_start();
include 'config/configdatabse.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $category = trim($_POST['category']);
    $alt_text = trim($_POST['alt_text']);

    // // Validate inputs
    // if (empty($category) || empty($alt_text)) {
    //     die("Category and Alt Text are required.");
    // }

    // File upload handling
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {

        // Allowed MIME types
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES["image"]["tmp_name"]);

        if (!in_array($fileType, $allowedTypes)) {
            die("Only JPG, PNG, and GIF images are allowed.");
        }

        // Max file size 5MB
        $maxSize = 5 * 1024 * 1024;
        if ($_FILES["image"]["size"] > $maxSize) {
            die("File size must be less than 5MB.");
        }

        // Create uploads folder if not exists
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Generate unique file name
        $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $fileName = time() . "_" . bin2hex(random_bytes(5)) . "." . $ext;
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {

            // Insert into database
            $stmt = $conn->prepare("INSERT INTO gallery (category, image_path, alt_text) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $category, $targetFilePath, $alt_text);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success_message'] = "Image uploaded successfully!";
            header("Location: gallery.php");
            exit();
        } else {
            die("Error uploading image.");
        }
    } else {
        die("No file uploaded or upload error occurred.");
    }
}
?>
