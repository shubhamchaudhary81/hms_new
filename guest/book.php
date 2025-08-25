<?php
session_start();
include_once '../config/configdatabse.php';

// Check if the user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login.php?redirect=guestdash');
    exit();
}



// Fetch all room types for dropdowns
$roomTypes = [];
$typeResult = $conn->query("SELECT room_type_id, room_type_name FROM RoomType");
while ($row = $typeResult->fetch_assoc()) {
  $roomTypes[] = $row;
}
// Handle filters
$selectedType = isset($_POST['room_type']) ? $_POST['room_type'] : '';
$filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$checkIn = isset($_POST['check_in']) ? $_POST['check_in'] : '';
$checkOut = isset($_POST['check_out']) ? $_POST['check_out'] : '';
$guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;
// Build the room query with filters
$where = ["Room.status = 'available'"];
if ($selectedType) {
  $where[] = "RoomType.room_type_name = '" . $conn->real_escape_string($selectedType) . "'";
} elseif ($filterType) {
  $where[] = "RoomType.room_type_name = '" . $conn->real_escape_string($filterType) . "'";
}
if ($guests > 0) {
  $where[] = "Room.capacity >= $guests";
}
$sql = "SELECT Room.*, RoomType.room_type_name FROM Room JOIN RoomType ON Room.room_type = RoomType.room_type_id";
if ($where) {
  $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY Room.price_per_night ASC";
$result = $conn->query($sql);
?>

<body>
  <?php include '../include/header.php'; ?>
  <div class="container py-5">
    <h2 class="section-title">Book a Room</h2>
    
    <div class="booking-form">
      <form class="row g-3" method="POST" action="">
        <div class="col-12 col-md-4">
          <label class="form-label">Room Type</label>
          <select class="form-select" name="room_type">
            <option value="">All Types</option>
            <?php foreach ($roomTypes as $type): ?>
              <option value="<?= htmlspecialchars($type['room_type_name']) ?>" <?= ($selectedType == $type['room_type_name']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($type['room_type_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Check In</label>
          <input type="date" class="form-control" name="check_in" value="<?= isset($_POST['check_in']) ? htmlspecialchars($_POST['check_in']) : '' ?>">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Check Out</label>
          <input type="date" class="form-control" name="check_out" value="<?= isset($_POST['check_out']) ? htmlspecialchars($_POST['check_out']) : '' ?>">
        </div>
        <div class="col-6 col-md-1">
          <label class="form-label">Guests</label>
          <input type="number" class="form-control" min="1" name="guests" value="<?= isset($_POST['guests']) ? htmlspecialchars($_POST['guests']) : '1' ?>">
        </div>
        <div class="col-6 col-md-1 d-flex align-items-end">
          <button type="submit" class="btn btn-premium w-98">Search</button>
        </div>
      </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0">Available Rooms</h3>
      <form method="get" class="filter-control">
        <select class="form-select border-0 p-0" name="filter_type" onchange="this.form.submit()">
          <option value="">All Types</option>
          <?php foreach ($roomTypes as $type): ?>
            <option value="<?= htmlspecialchars($type['room_type_name']) ?>" <?= ($filterType == $type['room_type_name']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($type['room_type_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <noscript><button class="btn btn-premium btn-sm ms-2">Filter</button></noscript>
      </form>
    </div>

    <div class="row g-4">
      <?php
      if ($result && $result->num_rows > 0) {
        while ($room = $result->fetch_assoc()) {
          $img = $room['image'] ? $room['image'] : '../assets/images/room1.jpg';
          $desc = $room['description'] ? $room['description'] : 'No description.';
      ?>
       <div class="col-md-4">
  <div class="room-card h-100">
    <img src="<?= htmlspecialchars($img) ?>" class="room-img" alt="<?= htmlspecialchars($room['room_type_name']) ?> Room">
    <div class="room-body">
      <h4 class="room-title"><?= htmlspecialchars($room['room_type_name']) ?></h4>
      <p class="text-muted mb-1">Room Number: <?= htmlspecialchars($room['room_number']) ?></p>
      <p class="text-muted mb-2"><?= htmlspecialchars($desc) ?></p>
      <span class="badge bg-success mb-3 px-3 py-2 rounded-pill">
        <i class="bi bi-check-circle me-1"></i>Available
      </span>
      <div>
        <a href="reservation_form.php?room_id=<?= $room['room_id'] ?>" class="btn btn-premium w-100">
          <i class="bi bi-calendar-check me-1"></i> Reserve Now
        </a>
      </div>
    </div>
  </div>
</div>
      <?php
        }
      } 
      else
      {
      ?>
        <div class="col-12">
          <div class="no-rooms">
            <div class="no-rooms-icon"><i class="bi bi-calendar-x"></i></div>
            <h4 class="mb-3">No Rooms Available</h4>
            <p class="text-muted">Please try different search criteria.</p>
          </div>
        </div>
      <?php
      }
      ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <?php include '../include/footer_guest.php'; ?>
</body>
</html>