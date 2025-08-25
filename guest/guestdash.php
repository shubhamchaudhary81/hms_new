<?php
session_start();
include("../config/configdatabse.php");
// Check if the user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login.php?redirect=guestdash');
    exit();
}

$firstName = 'Guest';
if (isset($_SESSION['customer_name'])) {
    $firstName = $_SESSION['customer_name'];
}

// Fetch bookings for the logged-in customer
$customerId = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 0;
$bookings = [];
if ($customerId) {
  $sql = "SELECT b.booking_id, b.actual_check_in, b.actual_check_out, b.status, b.advance_amount, r.requested_check_in_date, r.requested_check_out_date, rm.room_number, rt.room_type_name
      FROM Bookings b
      JOIN Reservations r ON b.reservation_id = r.reservation_id
      JOIN Room rm ON b.room_id = rm.room_id
      JOIN RoomType rt ON rm.room_type = rt.room_type_id
      WHERE r.customer_id = ?
      ORDER BY b.booking_id DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $customerId);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
  }
  $stmt->close();
}
?>

<body>
  <?php include '../include/header.php'; ?>
  <!-- Premium Navigation -->
  <!-- <nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">Himalaya Hotel</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="#">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="bookroom.php">Book Room</a></li>
          <li class="nav-item"><a class="nav-link" href="mybookings.php">My Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item ms-2"><a class="nav-link btn btn-premium" href="#">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav> -->

  <!-- Luxury Hero Section -->
  <section class="hero-section">
    <div class="container">
      <div class="hero-content text-center">
        <h1 class="hero-title">Welcome Back,<?php echo " $firstName"; ?>
</h1>
        <p class="hero-subtitle">Your comfort is our priority. Experience unparalleled luxury and service during your stay at Himalaya Hotel.</p>
        <a href="book.php" class="btn btn-premium btn-lg">Book Your Next Stay</a>
      </div>
    </div>
  </section>

  <!-- Quick Actions -->
  <section class="container quick-actions">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="action-card text-center">
          <div class="action-icon">
            <i class="bi bi-calendar2-plus"></i>
          </div>
          <h3 class="action-title">Book a Room</h3>
          <p class="mb-4">Reserve your perfect stay with our curated selection of luxury rooms and suites.</p>
          <a href="book.php" class="btn btn-premium">Book Now</a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="action-card text-center">
          <div class="action-icon">
            <i class="bi bi-journal-check"></i>
          </div>
          <h3 class="action-title">My Bookings</h3>
          <p class="mb-4">Manage your current and upcoming reservations with our easy-to-use interface.</p>
          <a href="mybookings.php" class="btn btn-premium">View Bookings</a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="action-card text-center">
          <div class="action-icon">
            <i class="bi bi-person-circle"></i>
          </div>
          <h3 class="action-title">My Profile</h3>
          <p class="mb-4">Personalize your experience by updating your preferences and account details.</p>
          <a href="profile.php" class="btn btn-premium">Edit Profile</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Booking Summary -->
  <section id="bookings" class="container booking-section">
    <h2 class="section-title text-center">My Booking Summary</h2>
    <div class="booking-table">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Booking ID</th>
              <th>Room</th>
              <th>Check-in</th>
              <th>Check-out</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
<?php if (count($bookings) > 0): ?>
  <?php foreach ($bookings as $booking): ?>
            <tr>
      <td class="fw-bold">#H<?= htmlspecialchars($booking['booking_id']) ?></td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="me-3" style="width: 60px; height: 60px; background: #f5f5f5; border-radius: 8px; overflow: hidden;">
            <img src="../assets/images/hotel-exterior.jpg" style="width: 100%; height: 100%; object-fit: cover;">
                  </div>
                  <div>
            <div class="fw-bold"><?= htmlspecialchars($booking['room_type_name']) ?></div>
            <small class="text-muted">Room <?= htmlspecialchars($booking['room_number']) ?></small>
                  </div>
                </div>
              </td>
      <td>
        <?= $booking['actual_check_in'] ? date('M j, Y', strtotime($booking['actual_check_in'])) : '-' ?>
        <?php if ($booking['requested_check_in_date'] && !$booking['actual_check_in']): ?>
          <div class="text-muted small">Requested: <?= date('M j, Y', strtotime($booking['requested_check_in_date'])) ?></div>
        <?php endif; ?>
      </td>
      <td>
        <?= $booking['actual_check_out'] ? date('M j, Y', strtotime($booking['actual_check_out'])) : '-' ?>
        <?php if ($booking['requested_check_out_date'] && !$booking['actual_check_out']): ?>
          <div class="text-muted small">Requested: <?= date('M j, Y', strtotime($booking['requested_check_out_date'])) ?></div>
        <?php endif; ?>
      </td>
      <td>
        <?php
          $status = strtolower($booking['status']);
          $badgeClass = 'bg-secondary';
          if ($status === 'confirmed') $badgeClass = 'bg-success';
          elseif ($status === 'completed') $badgeClass = 'bg-primary';
          elseif ($status === 'cancelled') $badgeClass = 'bg-danger';
          elseif ($status === 'pending') $badgeClass = 'bg-warning text-dark';
        ?>
        <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
      </td>
      <td><a href="booking_details.php?booking_id=<?= urlencode($booking['booking_id']) ?>" class="btn btn-sm btn-outline-secondary">Details</a></td>
            </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr>
    <td colspan="6" class="text-center text-muted py-4">
      <div class="empty-state-icon mb-2"><i class="bi bi-calendar-x" style="font-size:2rem;"></i></div>
      <div>No bookings found. <a href="book.php">Book your first stay!</a></div>
              </td>
            </tr>
<?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Feedback Section -->
  <section class="container feedback-section">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h2 class="feedback-title text-center">Share Your Experience</h2>
        <form>
          <div class="mb-4">
            <label for="feedbackText" class="form-label">Your Feedback</label>
            <textarea class="form-control" id="feedbackText" rows="5" placeholder="Tell us about your stay..."></textarea>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-premium">Submit Feedback</button>
          </div>
        </form>
      </div>
    </div>
  </section>

 

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <?php include '../include/footer_guest.php'; ?>
</body>
</html>