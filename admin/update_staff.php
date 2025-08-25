<?php
// Database connection
include_once '../config/configdatabse.php';

// Set header to return JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffId = (int)$_POST['staffId'];
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $gender = $_POST['gender'];
    $roleId = (int)$_POST['roleId'];
    $isActive = $_POST['isActive'];
    
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phoneNumber) || empty($gender) || empty($roleId) || empty($staffId)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Check if email already exists for other staff members
    $checkEmailQuery = "SELECT staff_id FROM Staffs WHERE email = ? AND staff_id != ?";
    $checkEmailStmt = $conn->prepare($checkEmailQuery);
    $checkEmailStmt->bind_param("si", $email, $staffId);
    $checkEmailStmt->execute();
    $emailResult = $checkEmailStmt->get_result();
    
    if ($emailResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists for another staff member']);
        exit;
    }
    
    // Check if phone number already exists for other staff members
    $checkPhoneQuery = "SELECT staff_id FROM Staffs WHERE phone_number = ? AND staff_id != ?";
    $checkPhoneStmt = $conn->prepare($checkPhoneQuery);
    $checkPhoneStmt->bind_param("si", $phoneNumber, $staffId);
    $checkPhoneStmt->execute();
    $phoneResult = $checkPhoneStmt->get_result();
    
    if ($phoneResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Phone number already exists for another staff member']);
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
    
    // Verify staff member exists
    $checkStaffQuery = "SELECT staff_id FROM Staffs WHERE staff_id = ?";
    $checkStaffStmt = $conn->prepare($checkStaffQuery);
    $checkStaffStmt->bind_param("i", $staffId);
    $checkStaffStmt->execute();
    $staffResult = $checkStaffStmt->get_result();
    
    if ($staffResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Staff member not found']);
        exit;
    }
    
    // Update staff member
    $updateQuery = "UPDATE Staffs SET first_name = ?, last_name = ?, email = ?, phone_number = ?, gender = ?, role_id = ?, is_active = ? WHERE staff_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssssssi", $firstName, $lastName, $email, $phoneNumber, $gender, $roleId, $isActive, $staffId);
    
    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Staff member updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating staff member: ' . $conn->error]);
    }
    
    $updateStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
