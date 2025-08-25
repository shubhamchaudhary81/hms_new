<?php
session_start();
include_once '../config/configdatabse.php';

if (!isset($_SESSION['customer_id'])) {
    die("You must be logged in to reserve.");
}
$customer_id = (int) $_SESSION['customer_id'];

if (!isset($_GET['room_id'])) {
    die("Room ID is missing.");
}
$room_id = (int) $_GET['room_id'];

// Get room + room_type details
$stmt = $conn->prepare("
    SELECT r.room_id, r.room_number, r.price_per_night, r.capacity,
           rt.room_type_id, rt.room_type_name
    FROM Room r
    JOIN RoomType rt ON r.room_type = rt.room_type_id
    WHERE r.room_id = ?
");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

if (!$room) {
    die("Room not found.");
}

// Get customer info
$stmt2 = $conn->prepare("SELECT * FROM Customers WHERE id = ?");
$stmt2->bind_param("i", $customer_id);
$stmt2->execute();
$customer = $stmt2->get_result()->fetch_assoc();
?>
<body>
<?php include '../include/header.php'; ?>

<div class="reservation-header text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Reserve Your Stay</h1>
        <p class="lead">Room <?= htmlspecialchars($room['room_number']) ?> - <?= htmlspecialchars($room['room_type_name']) ?></p>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="form-container">
                <h2 class="section-title">Reservation Details</h2>
                
                <form method="POST" action="save_reservation.php" id="reservationForm">
                    <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
                    <input type="hidden" name="room_type_id" value="<?= $room['room_type_id'] ?>">
                    <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">
                    <input type="hidden" name="estimated_total_amount" id="estimated_total_amount_input" value="0">

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold">First Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($customer['first_name']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Last Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($customer['last_name']) ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold">Check-In Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                <input type="datetime-local" name="check_in" class="form-control" id="check_in" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Check-Out Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-times"></i></span>
                                <input type="datetime-local" name="check_out" class="form-control" id="check_out" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Number of Guests</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-users"></i></span>
                            <input type="number" name="num_guests" class="form-control" min="1" max="<?= $room['capacity'] ?>" value="1" required>
                        </div>
                        <small class="text-muted">Maximum capacity: <?= $room['capacity'] ?> guests</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Special Requests</label>
                        <textarea name="special_requests" class="form-control" rows="4" placeholder="Any special requirements or preferences..."></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-plus me-2"></i> Confirm Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="room-card">
                <div class="card-body p-4">
                    <h3 class="card-title mb-3">Your Selection</h3>
                    
                    <div class="mb-4">
                        <h4 class="h5"><?= htmlspecialchars($room['room_type_name']) ?></h4>
                        <p class="text-muted">Room <?= htmlspecialchars($room['room_number']) ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="mb-3">Room Features</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-bed feature-icon"></i> <?= $room['capacity'] > 1 ? $room['capacity'].' person capacity' : 'Single occupancy' ?></li>
                            <li class="mb-2"><i class="fas fa-ruler-combined feature-icon"></i> Spacious layout</li>
                            <li class="mb-2"><i class="fas fa-wifi feature-icon"></i> Free high-speed WiFi</li>
                            <li class="mb-2"><i class="fas fa-tv feature-icon"></i> Flat-screen TV</li>
                            <li class="mb-2"><i class="fas fa-snowflake feature-icon"></i> Air conditioning</li>
                        </ul>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Price per night:</h5>
                        <span class="price-display">NPR <?= number_format($room['price_per_night'], 2) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Estimated stay duration:</h5>
                        <span id="stayDuration">0 nights</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Estimated Total:</h4>
                        <span class="price-display" name="estimated_total_amount" id="estimated_total_amount">NPR 0.00</span>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 p-4 bg-light rounded">
                <h5 class="mb-3"><i class="fas fa-shield-alt text-primary me-2"></i> Booking Protection</h5>
                <p class="small">Your reservation is protected by our flexible cancellation policy. Free cancellation up to 48 hours before check-in.</p>
            </div>
        </div>
    </div>
</div>

<?php include '../include/footer_guest.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const checkIn = document.getElementById("check_in");
const checkOut = document.getElementById("check_out");
const totalField = document.getElementById("estimated_total_amount");
const stayDuration = document.getElementById("stayDuration");
const pricePerNight = <?= json_encode((float)$room['price_per_night']) ?>;
const today = new Date().toISOString().slice(0, 16);

// Set min date/time to today
checkIn.min = today;
checkOut.min = today;

function calculateTotal() {
    if (!checkIn.value || !checkOut.value) {
        totalField.textContent = "NPR 0.00";
        stayDuration.textContent = "0 nights";
        return;
    }

    const checkInDate = new Date(checkIn.value);
    const checkOutDate = new Date(checkOut.value);
    const diffTime = checkOutDate - checkInDate;

    if (diffTime <= 0) {
        totalField.textContent = "NPR 0.00";
        stayDuration.textContent = "0 nights";
        return;
    }

    const days = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    const total = days * pricePerNight;
    
    totalField.textContent = "NPR " + total.toFixed(2);
    document.getElementById("estimated_total_amount_input").value = total.toFixed(2);
    stayDuration.textContent = days + (days === 1 ? " night" : " nights");
    
    // Update check-out min date when check-in changes
    if (checkIn.value) {
        checkOut.min = checkIn.value;
    }
}

checkIn.addEventListener("change", calculateTotal);
checkOut.addEventListener("change", calculateTotal);

// Initialize date inputs with tomorrow's date as default
window.addEventListener('DOMContentLoaded', (event) => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().slice(0, 16);
    
    checkIn.value = tomorrowStr;
    
    const dayAfter = new Date(tomorrow);
    dayAfter.setDate(dayAfter.getDate() + 1);
    checkOut.value = dayAfter.toISOString().slice(0, 16);
    
    calculateTotal();
});
</script>
</body>
</html>