<?php
// Database connection
include_once '../config/configdatabse.php';

// Sample staff members to insert
$sampleStaff = [
    [
        'first_name' => 'Maria',
        'last_name' => 'Garcia',
        'email' => 'maria.garcia@hotel.com',
        'phone_number' => '+1-555-123-4567',
        'gender' => 'Female',
        'role_name' => 'Senior Housekeeper',
        'is_active' => 'Active'
    ],
    [
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'john.smith@hotel.com',
        'phone_number' => '+1-555-987-6543',
        'gender' => 'Male',
        'role_name' => 'Front Desk Manager',
        'is_active' => 'Active'
    ],
    [
        'first_name' => 'Carlos',
        'last_name' => 'Martinez',
        'email' => 'carlos.martinez@hotel.com',
        'phone_number' => '+1-555-456-7890',
        'gender' => 'Male',
        'role_name' => 'Maintenance Technician',
        'is_active' => 'Active'
    ],
    [
        'first_name' => 'Ana',
        'last_name' => 'Rodriguez',
        'email' => 'ana.rodriguez@hotel.com',
        'phone_number' => '+1-555-321-0987',
        'gender' => 'Female',
        'role_name' => 'Housekeeping Staff',
        'is_active' => 'Active'
    ],
    [
        'first_name' => 'Lisa',
        'last_name' => 'Chen',
        'email' => 'lisa.chen@hotel.com',
        'phone_number' => '+1-555-654-3210',
        'gender' => 'Female',
        'role_name' => 'Night Auditor',
        'is_active' => 'Inactive'
    ],
    [
        'first_name' => 'David',
        'last_name' => 'Kim',
        'email' => 'david.kim@hotel.com',
        'phone_number' => '+1-555-789-0123',
        'gender' => 'Male',
        'role_name' => 'Security Guard',
        'is_active' => 'Active'
    ],
    [
        'first_name' => 'Sarah',
        'last_name' => 'Brown',
        'email' => 'sarah.brown@hotel.com',
        'phone_number' => '+1-555-234-5678',
        'gender' => 'Female',
        'role_name' => 'Chef',
        'is_active' => 'Active'
    ],
    [
        'first_name' => 'Robert',
        'last_name' => 'Taylor',
        'email' => 'robert.taylor@hotel.com',
        'phone_number' => '+1-555-876-5432',
        'gender' => 'Male',
        'role_name' => 'Concierge',
        'is_active' => 'Active'
    ]
];

echo "Inserting sample staff members...\n";

foreach ($sampleStaff as $staff) {
    // Get role_id from role_name
    $roleQuery = "SELECT role_id FROM Roles WHERE role_name = ?";
    $roleStmt = $conn->prepare($roleQuery);
    $roleStmt->bind_param("s", $staff['role_name']);
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();
    
    if ($roleResult->num_rows > 0) {
        $roleRow = $roleResult->fetch_assoc();
        $roleId = $roleRow['role_id'];
        
        // Check if staff already exists
        $checkQuery = "SELECT staff_id FROM Staffs WHERE email = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $staff['email']);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            // Insert staff member
            $insertQuery = "INSERT INTO Staffs (first_name, last_name, email, phone_number, gender, role_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("sssssss", $staff['first_name'], $staff['last_name'], $staff['email'], $staff['phone_number'], $staff['gender'], $roleId, $staff['is_active']);
            
            if ($insertStmt->execute()) {
                echo "✓ Added staff: " . $staff['first_name'] . " " . $staff['last_name'] . " (" . $staff['role_name'] . ")\n";
            } else {
                echo "✗ Error adding staff " . $staff['first_name'] . " " . $staff['last_name'] . ": " . $conn->error . "\n";
            }
            
            $insertStmt->close();
        } else {
            echo "• Staff already exists: " . $staff['first_name'] . " " . $staff['last_name'] . "\n";
        }
        
        $checkStmt->close();
    } else {
        echo "✗ Role not found: " . $staff['role_name'] . " - Skipping staff member\n";
    }
    
    $roleStmt->close();
}

echo "\nSample staff insertion completed!\n";
$conn->close();
?>
