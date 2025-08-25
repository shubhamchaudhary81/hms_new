<?php
include_once '../config/configdatabse.php';

if (!isset($_POST['reservation_id'])) {
    http_response_code(400);
    echo "Missing reservation ID.";
    exit;
}

$reservation_id = intval($_POST['reservation_id']);

// Get detailed reservation information
$query = "
SELECT 
    r.reservation_id, r.requested_check_in_date, r.requested_check_out_date, 
    r.num_guests, r.status, r.estimated_total_amount, r.special_requests,
    c.first_name, c.last_name, c.email, c.number, c.address,
    rt.room_type_name, rt.base_price,
    rm.room_number, rm.price_per_night,
    b.advance_amount, b.actual_check_in, b.notes
FROM Reservations r
JOIN Customers c ON r.customer_id = c.id
JOIN RoomType rt ON r.room_type_id = rt.room_type_id
LEFT JOIN Bookings b ON b.reservation_id = r.reservation_id
LEFT JOIN Room rm ON b.room_id = rm.room_id
WHERE r.reservation_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-warning'>Reservation not found.</div>";
    exit;
}

$reservation = $result->fetch_assoc();
$stmt->close();

// Calculate nights
$check_in = new DateTime($reservation['requested_check_in_date']);
$check_out = new DateTime($reservation['requested_check_out_date']);
$nights = $check_in->diff($check_out)->days;

// Calculate total cost
$price_per_night = $reservation['price_per_night'] ?? $reservation['base_price'];
$total_cost = $price_per_night * $nights;
$remaining_amount = $total_cost - ($reservation['advance_amount'] ?? 0);
?>

<div class="reservation-details">
    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-bold text-primary mb-3">Guest Information</h6>
            <table class="table table-borderless">
                <tr>
                    <td class="fw-bold">Name:</td>
                    <td><?= htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Email:</td>
                    <td><?= htmlspecialchars($reservation['email']) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Phone:</td>
                    <td><?= htmlspecialchars($reservation['number']) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Address:</td>
                    <td><?= htmlspecialchars($reservation['address'] ?? 'Not provided') ?></td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h6 class="fw-bold text-primary mb-3">Reservation Details</h6>
            <table class="table table-borderless">
                <tr>
                    <td class="fw-bold">Reservation ID:</td>
                    <td>#<?= $reservation['reservation_id'] ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Room Type:</td>
                    <td><?= htmlspecialchars($reservation['room_type_name']) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Room Number:</td>
                    <td><?= htmlspecialchars($reservation['room_number'] ?? 'TBD') ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Status:</td>
                    <td><span class="badge bg-<?= $reservation['status'] === 'pending' ? 'warning' : ($reservation['status'] === 'confirmed' ? 'success' : 'info') ?>"><?= ucfirst($reservation['status']) ?></span></td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <h6 class="fw-bold text-primary mb-3">Stay Information</h6>
            <table class="table table-borderless">
                <tr>
                    <td class="fw-bold">Check-in:</td>
                    <td><?= date('M j, Y', strtotime($reservation['requested_check_in_date'])) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Check-out:</td>
                    <td><?= date('M j, Y', strtotime($reservation['requested_check_out_date'])) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Duration:</td>
                    <td><?= $nights ?> night(s)</td>
                </tr>
                <tr>
                    <td class="fw-bold">Guests:</td>
                    <td><?= $reservation['num_guests'] ?> adult(s)</td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h6 class="fw-bold text-primary mb-3">Financial Details</h6>
            <table class="table table-borderless">
                <tr>
                    <td class="fw-bold">Price per night:</td>
                    <td>रु<?= number_format($price_per_night, 0) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Total cost:</td>
                    <td>रु<?= number_format($total_cost, 0) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Advance paid:</td>
                    <td>रु<?= number_format($reservation['advance_amount'] ?? 0, 0) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Remaining:</td>
                    <td class="fw-bold text-<?= $remaining_amount > 0 ? 'danger' : 'success' ?>">रु<?= number_format($remaining_amount, 0) ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <?php if ($reservation['special_requests']): ?>
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="fw-bold text-primary mb-3">Special Requests</h6>
            <div class="alert alert-info">
                <?= htmlspecialchars($reservation['special_requests']) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($reservation['notes']): ?>
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="fw-bold text-primary mb-3">Notes</h6>
            <div class="alert alert-secondary">
                <?= htmlspecialchars($reservation['notes']) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
