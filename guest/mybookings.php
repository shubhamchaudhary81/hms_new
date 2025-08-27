<?php
include_once '../config/configdatabse.php';
session_start();
$customerId = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 0;
if (!$customerId) {
    header('Location: ../login.php?redirect=mybookings');
    exit();
}
$bookings = [];
$totalBookings = 0;
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
  $totalBookings = count($bookings);
  $stmt->close();
}
$currentRoom = null;
foreach ($bookings as $b) {
  if (strtolower($b['status']) === 'confirmed' && empty($b['actual_check_out'])) {
    $currentRoom = $b;
    break;
  }
}
?>


<body>
  <?php include '../include/header.php'; ?>
  
  <section class="hero-section">
    <div class="container text-center">
      <h1 class="display-4 fw-bold mb-3" style="color: white;">My Bookings</h1>
      <p class="lead opacity-60">Your journey with Himalaya Hotel</p>
    </div>
  </section>

  <div class="container mb-5">
    <?php if ($currentRoom): ?>
      <div class="current-stay-card">
        <h5 class="mb-4 text-uppercase" style="letter-spacing: 1px; color: var(--accent-color);">Current Stay</h5>
        <a href="room_details.php?room_id=<?= urlencode($currentRoom['room_number']) ?>" class="text-decoration-none">
          <div class="d-flex align-items-center">
            <div class="room-icon me-4">
              <i class="bi bi-door-open"></i>
            </div>
            <div>
              <h4 class="mb-2" style="color: var(--primary-color);">Room <?= htmlspecialchars($currentRoom['room_number']) ?> - <?= htmlspecialchars($currentRoom['room_type_name']) ?></h4>
              <div class="text-muted">Click to view room details & services</div>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>
    
    <div class="summary-card text-center position-relative">
      <div class="position-relative z-index-1">
        <h4 class="mb-3" style="color: white;">Booking Summary</h4>
        <h2 class="mb-3" style="font-size: 3.5rem; font-weight: 700; color: white;"><?= $totalBookings ?></h2>
        <p class="mb-0 opacity-75">Total bookings with Himalaya Hotel</p>
      </div>
    </div>
    
    <h3 class="section-title">Booking History</h3>
    
    <div class="booking-table">
      <?php if ($totalBookings > 0): ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Room</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Status</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $booking): ?>
                <tr>
                  <td>
                    <div class="fw-bold"><?= htmlspecialchars($booking['room_type_name']) ?></div>
                    <div class="text-muted small">Room <?= htmlspecialchars($booking['room_number']) ?></div>
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
                      $badgeClass = 'badge-secondary';
                      if ($status === 'confirmed') $badgeClass = 'badge-confirmed';
                      elseif ($status === 'completed') $badgeClass = 'badge-completed';
                      elseif ($status === 'cancelled') $badgeClass = 'badge-cancelled';
                      elseif ($status === 'pending') $badgeClass = 'badge-pending';
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                  </td>
                  <td class="price-highlight">रु<?= number_format($booking['advance_amount'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="bi bi-calendar-x"></i>
          </div>
          <h4 class="mb-3">No Bookings Found</h4>
          <p class="text-muted mb-4">You haven't made any bookings with us yet.</p>
          <a href="../rooms.php" class="btn btn-premium">
            <i class="bi bi-search me-2"></i> Explore Our Rooms
          </a>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="text-center mt-5">
      <a href="guestdash.php" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to Home
      </a>
    </div>
  </div>

  <?php include '../include/footer_guest.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Animation for cards when they come into view
    document.addEventListener('DOMContentLoaded', function() {
      const animateOnScroll = function() {
        const elements = document.querySelectorAll('.current-stay-card, .summary-card, .booking-table');
        
        elements.forEach(element => {
          const elementPosition = element.getBoundingClientRect().top;
          const windowHeight = window.innerHeight;
          
          if (elementPosition < windowHeight - 100) {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
          }
        });
      };
      
      // Set initial state
      const cards = document.querySelectorAll('.current-stay-card, .summary-card, .booking-table');
      cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease-out';
      });
      
      // Animate on load and scroll
      animateOnScroll();
      window.addEventListener('scroll', animateOnScroll);
    });
  </script>
</body>
</html>