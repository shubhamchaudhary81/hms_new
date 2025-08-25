<?php
// Database connection
include_once '../config/configdatabse.php';

// Set header to return JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleName = trim($_POST['roleName']);
    $roleDescription = trim($_POST['roleDescription'] ?? '');
    
    // Validate input
    if (empty($roleName)) {
        echo json_encode(['success' => false, 'message' => 'Role name is required']);
        exit;
    }
    
    // Check if role already exists
    $checkQuery = "SELECT role_id FROM Roles WHERE role_name = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $roleName);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Role already exists']);
        exit;
    }
    
    // Insert new role
    $insertQuery = "INSERT INTO Roles (role_name, description) VALUES (?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("ss", $roleName, $roleDescription);
    
    if ($insertStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Role added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding role: ' . $conn->error]);
    }
    
    $insertStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
