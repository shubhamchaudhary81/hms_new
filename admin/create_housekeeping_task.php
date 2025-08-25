<?php
include_once '../config/configdatabse.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }

    $bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $roomServiceId = isset($_POST['room_service_id']) ? (int)$_POST['room_service_id'] : 0;
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Requested';
    $assignedStaffId = isset($_POST['assigned_staff_id']) && $_POST['assigned_staff_id'] !== '' ? (int)$_POST['assigned_staff_id'] : null;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    if ($bookingId <= 0 || $roomServiceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Booking and room service are required']);
        exit;
    }

    // Validate booking exists
    $stmt = $conn->prepare('SELECT booking_id FROM Bookings WHERE booking_id = ?');
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking']);
        exit;
    }

    // Get service price
    $stmt = $conn->prepare('SELECT price FROM RoomService WHERE room_service_id = ? AND availability_status = "available"');
    $stmt->bind_param('i', $roomServiceId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid or unavailable service']);
        exit;
    }
    $priceRow = $res->fetch_assoc();
    $unitPrice = (float)$priceRow['price'];
    $chargeAmount = $unitPrice * $quantity;

    // Ensure optional column assigned_staff_id exists if we want to save staff assignment
    if ($assignedStaffId !== null) {
        $colRes = $conn->query("SHOW COLUMNS FROM BookingRoomService LIKE 'assigned_staff_id'");
        if ($colRes && $colRes->num_rows === 0) {
            // Try to add the column
            $conn->query("ALTER TABLE BookingRoomService ADD COLUMN assigned_staff_id INT NULL, ADD CONSTRAINT fk_brs_staff FOREIGN KEY (assigned_staff_id) REFERENCES Staffs(staff_id) ON DELETE SET NULL");
        }
    }

    // Build insert
    $hasAssigned = false;
    if ($col = $conn->query("SHOW COLUMNS FROM BookingRoomService LIKE 'assigned_staff_id'")) {
        $hasAssigned = $col->num_rows > 0;
    }

    if ($hasAssigned && $assignedStaffId !== null) {
        $stmt = $conn->prepare('INSERT INTO BookingRoomService (booking_id, room_service_id, quantity, service_date, charge_amount, status, assigned_staff_id, notes) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)');
        $stmt->bind_param('iiidssis', $bookingId, $roomServiceId, $quantity, $chargeAmount, $status, $assignedStaffId, $notes);
    } else {
        $stmt = $conn->prepare('INSERT INTO BookingRoomService (booking_id, room_service_id, quantity, service_date, charge_amount, status, notes) VALUES (?, ?, ?, NOW(), ?, ?, ?)');
        $stmt->bind_param('iiidss', $bookingId, $roomServiceId, $quantity, $chargeAmount, $status, $notes);
    }

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task created', 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $stmt->error]);
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
