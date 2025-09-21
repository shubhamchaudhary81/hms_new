<?php
// db_connect.php should contain your database connection
include_once '../config/configdatabse.php';

if (isset($_GET['delete_room'])) {
    $roomId = intval($_GET['delete_room']); // sanitize input

    // 1️⃣ Check if room has bookings
    $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ?");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $stmt->bind_result($bookingCount);
    $stmt->fetch();
    $stmt->close();

    if ($bookingCount > 0) {
        // Room has bookings → cannot delete
        echo "Cannot delete room. It has active bookings.";
    } else {
        // 2️⃣ No bookings → delete the room
        $stmt = $conn->prepare("DELETE FROM Room WHERE room_id = ?");
        $stmt->bind_param("i", $roomId);

        if ($stmt->execute()) {
            echo "Room deleted successfully.";
            // Optional: redirect back to rooms list
            header("Location: rooms.php");
            exit;
        } else {
            echo "Failed to delete room. Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
