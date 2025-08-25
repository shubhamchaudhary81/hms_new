<?php
include_once '../config/configdatabse.php';

// Set default timezone to Nepal
date_default_timezone_set('Asia/Kathmandu');

echo "Database connection test...\n";

// Test 1: Check if we can connect to the database
if ($conn->ping()) {
    echo "✓ Database connection successful\n";
} else {
    echo "✗ Database connection failed: " . $conn->error . "\n";
    exit;
}

// Test 2: Check if Reservations table exists and has data
$reservations_query = "SELECT COUNT(*) as count FROM Reservations";
$result = $conn->query($reservations_query);
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Reservations table exists with " . $row['count'] . " records\n";
} else {
    echo "✗ Reservations table query failed: " . $conn->error . "\n";
}

// Test 3: Check if Room table exists and has data
$room_query = "SELECT COUNT(*) as count FROM Room";
$result = $conn->query($room_query);
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Room table exists with " . $row['count'] . " records\n";
} else {
    echo "✗ Room table query failed: " . $conn->error . "\n";
}

// Test 4: Check if RoomType table exists and has data
$roomtype_query = "SELECT COUNT(*) as count FROM RoomType";
$result = $conn->query($roomtype_query);
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ RoomType table exists with " . $row['count'] . " records\n";
} else {
    echo "✗ RoomType table query failed: " . $conn->error . "\n";
}

// Test 5: Check for pending reservations
$pending_query = "SELECT COUNT(*) as count FROM Reservations WHERE status = 'pending'";
$result = $conn->query($pending_query);
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Pending reservations: " . $row['count'] . "\n";
} else {
    echo "✗ Pending reservations query failed: " . $conn->error . "\n";
}

// Test 6: Check for available rooms
$available_rooms_query = "SELECT COUNT(*) as count FROM Room WHERE status = 'available'";
$result = $conn->query($available_rooms_query);
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Available rooms: " . $row['count'] . "\n";
} else {
    echo "✗ Available rooms query failed: " . $conn->error . "\n";
}

// Test 7: Check room type structure
$room_structure_query = "DESCRIBE Room";
$result = $conn->query($room_structure_query);
if ($result) {
    echo "✓ Room table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "✗ Room table structure query failed: " . $conn->error . "\n";
}

echo "\nDatabase test completed.\n";
?>
