<?php
include_once '../config/configdatabse.php';
session_start();

if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login.php?redirect=room_details');
    exit();
}

$room_number = isset($_GET['room_id']) ? $_GET['room_id'] : '';
if (!$room_number) {
    die('Room not specified.');
}

// Fetch room details
$stmt = $conn->prepare("SELECT r.*, rt.room_type_name FROM Room r JOIN RoomType rt ON r.room_type = rt.room_type_id WHERE r.room_number = ?");
$stmt->bind_param('s', $room_number);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$room) {
    die('Room not found.');
}

// Find current booking
$currentBooking = null;
$customer_id = $_SESSION['customer_id'];

$stmt = $conn->prepare("SELECT b.booking_id 
    FROM bookings b 
    JOIN Room rm ON b.room_id = rm.room_id 
    WHERE rm.room_number = ? 
    AND b.status = 'confirmed' 
    AND b.actual_check_out IS NULL 
    AND b.reservation_id IN (
        SELECT reservation_id FROM reservations WHERE customer_id = ?
    )");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param('si', $room_number, $customer_id);
$stmt->execute();
$bookingResult = $stmt->get_result();
if ($bookingResult && $bookingResult->num_rows > 0) {
    $currentBooking = $bookingResult->fetch_assoc();
}
$stmt->close();

// Handle service request form
$serviceSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['services']) && $currentBooking) {
    $booking_id = $currentBooking['booking_id'];
    $selectedServices = $_POST['services'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['price'];

    $insertStmt = $conn->prepare("INSERT INTO BookingRoomService (booking_id, room_service_id, quantity, charge_amount, status) VALUES (?, ?, ?, ?, 'Requested')");

    if (!$insertStmt) {
        die("Prepare failed: " . $conn->error);
    }

    foreach ($selectedServices as $service_id) {
        $room_service_id = (int)$service_id;
        $quantity = isset($quantities[$service_id]) ? (int)$quantities[$service_id] : 1;
        $price = isset($prices[$service_id]) ? (float)$prices[$service_id] : 0.0;
        $charge_amount = $price * $quantity;

        $insertStmt->bind_param('iiid', $booking_id, $room_service_id, $quantity, $charge_amount);
        $insertStmt->execute();
    }

    $insertStmt->close();
    $serviceSuccess = true;
}

// Fetch services
$services = [];
$serviceResult = $conn->query("SELECT * FROM RoomService WHERE availability_status = 'available'");
while ($row = $serviceResult->fetch_assoc()) {
    $services[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room <?= htmlspecialchars($room['room_number']) ?> Details | Himalaya Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../include/header.php'; ?>

<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Room <?= htmlspecialchars($room['room_number']) ?></h1>
        <p class="lead">Experience luxury redefined</p>
    </div>
</section>

<div class="container">
  
     <div class="room-header mb-5">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="room-title"><?= htmlspecialchars($room['room_type_name']) ?> Suite</h1>
                <div class="room-meta">
                    <span class="meta-item"><i class="bi bi-building"></i> Floor <?= htmlspecialchars($room['floor_number']) ?></span>
                    <span class="meta-item"><i class="bi bi-people-fill"></i> Capacity: <?= htmlspecialchars($room['capacity']) ?></span>
                    <span class="meta-item"><i class="bi bi-currency-exchange"></i> <span class="price-highlight">रु<?= number_format($room['price_per_night'], 2) ?></span>/night</span>
                </div>
                <p class="room-description"><?= htmlspecialchars($room['description']) ?></p>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <?php
                    $roomImg = $room['image'] ? $room['image'] : '../assets/images/room.jpg';
                    // If not an absolute path, prepend the uploads/room_images/ directory
                    if ($room['image'] && strpos($room['image'], 'uploads/') === false && strpos($room['image'], 'assets/images/') === false) {
                        $roomImg = '../uploads/room_images/' . $room['image'];
                    }
                    ?>
                    <img src="<?= htmlspecialchars($roomImg) ?>" alt="Room Image" class="img-fluid rounded shadow" style="max-height: 250px;">
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <h5 class="fw-bold mb-3">Room Amenities</h5>
            <div class="amenities-grid">
                <div class="amenity-item">
                    <i class="bi bi-wifi amenity-icon"></i>
                    <span>High-speed WiFi</span>
                </div>
                <div class="amenity-item">
                    <i class="bi bi-tv amenity-icon"></i>
                    <span>Smart TV</span>
                </div>
                <div class="amenity-item">
                    <i class="bi bi-thermometer-snow amenity-icon"></i>
                    <span>Air Conditioning</span>
                </div>
                <div class="amenity-item">
                    <i class="bi bi-cup-hot amenity-icon"></i>
                    <span>Tea/Coffee Maker</span>
                </div>
                <div class="amenity-item">
                    <i class="bi bi-safe amenity-icon"></i>
                    <span>In-room Safe</span>
                </div>
                <div class="amenity-item">
                    <i class="bi bi-droplet amenity-icon"></i>
                    <span>Rain Shower</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="section-title">Enhance Your Stay</h3>
            <p class="text-muted mb-4">Select from our premium services to make your experience even more memorable</p>
            <div id="serviceError" class="alert d-none mb-3" style="background: linear-gradient(90deg, #fbeee6, #f5d7b7); border: 1.5px solid #d4a762; color: #5d4037; font-weight: 500; box-shadow: 0 2px 8px rgba(212, 167, 98, 0.10);">
                <i class="bi bi-exclamation-triangle-fill me-2" style="color: #d4a762;"></i>
                <span id="serviceErrorText"></span>
            </div>
            <?php if ($serviceSuccess): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-success-subtle rounded-3 px-4 py-3" role="alert" style="background: linear-gradient(90deg, #d4edda, #c3e6cb); font-weight: 500;">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Success!</strong> Your service requests have been submitted successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <form method="post" action="" id="serviceForm">
                <div class="row">
                    <?php foreach ($services as $service): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="service-card h-100">
                            <div class="d-flex align-items-start">
                                <input type="checkbox" class="form-check-input service-checkbox mt-1" name="services[]" value="<?= $service['room_service_id'] ?>" id="service<?= $service['room_service_id'] ?>" onchange="updateServiceTotal(<?= $service['room_service_id'] ?>)">
                                <div class="ms-3 w-100">
                                    <h5 class="service-name"><?= htmlspecialchars($service['service_name']) ?></h5>
                                    <div class="service-price mb-2">रु<?= number_format($service['price'], 2) ?></div>
                                    <input type="hidden" name="price[<?= $service['room_service_id'] ?>]" value="<?= $service['price'] ?>" id="price<?= $service['room_service_id'] ?>">
                                    <div class="input-group input-group-sm mb-2" style="max-width: 120px;">
                                        <span class="input-group-text">Qty</span>
                                        <input type="number" class="form-control service-qty" name="quantity[<?= $service['room_service_id'] ?>]" value="1" min="1" max="99" id="qty<?= $service['room_service_id'] ?>" onchange="updateServiceTotal(<?= $service['room_service_id'] ?>)">
                                    </div>
                                    <div class="text-muted small mb-2">Total: <span class="service-total" id="total<?= $service['room_service_id'] ?>">रु<?= number_format($service['price'], 2) ?></span></div>
                                    <p class="service-description mb-0"><?= htmlspecialchars($service['description']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-4">
                    <div class="mb-3 fs-5 fw-bold">Total Amount: <span id="overallTotal">रु0.00</span></div>
                    <button type="button" id="confirmServiceBtn" class="btn btn-premium px-5 py-3" <?= $currentBooking ? '' : 'disabled' ?>>
                        <i class="bi bi-check-circle-fill me-2"></i> Request Selected Services
                    </button>
                    <?php if (!$currentBooking): ?>
                        <div class="text-danger mt-2">You do not have an active booking for this room.</div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Service Confirmation Modal -->
    <div class="modal fade" id="serviceConfirmModal" tabindex="-1" aria-labelledby="serviceConfirmModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white" >
            <h5 class="modal-title" id="serviceConfirmModalLabel"><i class="bi bi-check2-circle me-2"></i>Confirm Room Service Request</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <p class="fs-5 mb-3">Are you sure you want to request the selected room services?</p>
            <div class="mb-2">
              <i class="bi bi-bell-fill text-success" style="font-size: 2.5rem;"></i>
            </div>
            <div class="fw-bold">Total Amount: <span id="modalTotalAmount" class="text-success">रु0.00</span></div>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success px-4" id="modalConfirmBtn"><i class="bi bi-check-circle me-1"></i>Confirm</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Gallery and Footer (unchanged, keep as is) -->
</div>

<?php include '../include/footer_guest.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateServiceTotal(serviceId) {
    const checkbox = document.getElementById('service' + serviceId);
    const qtyInput = document.getElementById('qty' + serviceId);
    const price = parseFloat(document.getElementById('price' + serviceId).value);
    const qty = parseInt(qtyInput.value) || 1;
    const totalSpan = document.getElementById('total' + serviceId);
    let total = 0;
    if (checkbox.checked) {
        total = price * qty;
        qtyInput.disabled = false;
    } else {
        qtyInput.disabled = true;
        total = 0;
    }
    totalSpan.textContent = 'रु' + total.toFixed(2);
    updateOverallTotal();
}

function updateOverallTotal() {
    let overall = 0;
    document.querySelectorAll('.service-checkbox').forEach(cb => {
        const id = cb.value;
        const checked = cb.checked;
        const qty = parseInt(document.getElementById('qty' + id).value) || 1;
        const price = parseFloat(document.getElementById('price' + id).value);
        if (checked) {
            overall += price * qty;
        }
    });
    document.getElementById('overallTotal').textContent = 'रु' + overall.toFixed(2);
    // Also update modal total if modal exists
    var modalTotal = document.getElementById('modalTotalAmount');
    if (modalTotal) modalTotal.textContent = 'रु' + overall.toFixed(2);
}

// Confirmation before submitting form
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.service-checkbox').forEach(cb => updateServiceTotal(cb.value));
    document.querySelectorAll('.service-qty').forEach(qtyInput => {
        qtyInput.addEventListener('change', function () {
            const id = qtyInput.name.match(/\d+/)[0];
            updateServiceTotal(id);
        });
    });

    var confirmBtn = document.getElementById('confirmServiceBtn');
    var modal = new bootstrap.Modal(document.getElementById('serviceConfirmModal'));
    var modalConfirmBtn = document.getElementById('modalConfirmBtn');
    var form = document.getElementById('serviceForm');

    confirmBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const checked = document.querySelectorAll('.service-checkbox:checked');
        if (checked.length === 0) {
            var errorDiv = document.getElementById('serviceError');
            var errorText = document.getElementById('serviceErrorText');
            errorText.textContent = "Please select at least one service to request.";
            errorDiv.classList.remove('d-none');
            setTimeout(() => errorDiv.classList.add('d-none'), 3000);
            return;
        }
        updateOverallTotal(); // update modal total
        modal.show();
    });
    modalConfirmBtn.addEventListener('click', function () {
        form.submit();
    });
});
</script>
</body>
</html>
