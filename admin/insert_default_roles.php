<?php
// Database connection
include_once '../config/configdatabse.php';

// Default roles to insert
$defaultRoles = [
    ['role_name' => 'Hotel Manager', 'description' => 'Overall hotel management and operations'],
    ['role_name' => 'Front Desk Staff', 'description' => 'Guest check-in/check-out and front desk operations'],
    ['role_name' => 'Housekeeping Staff', 'description' => 'Room cleaning and maintenance'],
    ['role_name' => 'Maintenance Technician', 'description' => 'Building and equipment maintenance'],
    ['role_name' => 'Security Guard', 'description' => 'Hotel security and safety'],
    ['role_name' => 'Chef', 'description' => 'Kitchen and food preparation'],
    ['role_name' => 'Waiter/Waitress', 'description' => 'Restaurant service staff'],
    ['role_name' => 'Concierge', 'description' => 'Guest services and assistance'],
    ['role_name' => 'Night Auditor', 'description' => 'Night shift operations and accounting'],
    ['role_name' => 'Receptionist', 'description' => 'Guest reception and phone handling']
];

echo "Inserting default roles...\n";

foreach ($defaultRoles as $role) {
    // Check if role already exists
    $checkQuery = "SELECT role_id FROM Roles WHERE role_name = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $role['role_name']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        // Insert role
        $insertQuery = "INSERT INTO Roles (role_name, description) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ss", $role['role_name'], $role['description']);
        
        if ($insertStmt->execute()) {
            echo "✓ Added role: " . $role['role_name'] . "\n";
        } else {
            echo "✗ Error adding role " . $role['role_name'] . ": " . $conn->error . "\n";
        }
        
        $insertStmt->close();
    } else {
        echo "• Role already exists: " . $role['role_name'] . "\n";
    }
    
    $checkStmt->close();
}

echo "\nDefault roles insertion completed!\n";
$conn->close();
?>
