<?php
// Database connection
include_once '../config/configdatabse.php';

// Set header to return JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $gender = $_POST['gender'];
    $roleId = (int)$_POST['roleId'];
    $isActive = $_POST['isActive'];
    
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phoneNumber) || empty($gender) || empty($roleId)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Check if email already exists
    $checkEmailQuery = "SELECT staff_id FROM Staffs WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailQuery);
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $emailResult = $checkEmailStmt->get_result();
    
    if ($emailResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    
    // Check if phone number already exists
    $checkPhoneQuery = "SELECT staff_id FROM Staffs WHERE phone_number = ?";
    $checkPhoneStmt = $conn->prepare($checkPhoneQuery);
    $checkPhoneStmt->bind_param("s", $phoneNumber);
    $checkPhoneStmt->execute();
    $phoneResult = $checkPhoneStmt->get_result();
    
    if ($phoneResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Phone number already exists']);
        exit;
    }
    
    // Verify role exists
    $checkRoleQuery = "SELECT role_id FROM Roles WHERE role_id = ?";
    $checkRoleStmt = $conn->prepare($checkRoleQuery);
    $checkRoleStmt->bind_param("i", $roleId);
    $checkRoleStmt->execute();
    $roleResult = $checkRoleStmt->get_result();
    
    if ($roleResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid role selected']);
        exit;
    }
    
    // Insert new staff member
    $insertQuery = "INSERT INTO Staffs (first_name, last_name, email, phone_number, gender, role_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("sssssss", $firstName, $lastName, $email, $phoneNumber, $gender, $roleId, $isActive);
    
    if ($insertStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Staff member added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding staff member: ' . $conn->error]);
    }
    
    $insertStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
