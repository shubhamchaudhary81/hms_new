<?php
include 'config/configdatabse.php';
session_start();  // <-- add this line (important!)

include 'config/configdatabse.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}



// Fetch all images
$result = $conn->query("SELECT * FROM gallery WHERE is_deleted = 0 ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Photo Gallery - Hotel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/mainindex.css">
  <style>
    body {
      background: #f8f9fa;
    }

    .gallery-item {
      position: relative;
      overflow: hidden;
      border-radius: 10px;
      cursor: pointer;
    }

    .gallery-item img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      transition: transform 0.4s ease;
      border-radius: 10px;
    }

    .gallery-item:hover img {
      transform: scale(1.08);
    }
  </style>
</head>

<body>
  <!-- Premium Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">Himalaya Hotel</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#gallery">Gallery</a></li>
          <li class="nav-item"><a class="nav-link" href="rooms.php">Rooms</a></li>
          <li class="nav-item ms-3"><a href="login.php" class="btn btn-premium">Login / Sign Up</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Header -->
  <div class="container">
    <div class="container text-center py-5 mt-5">
      <h2 class="section-title">Photo Gallery</h2>
      <p class="lead text-muted">Browse our complete collection of photos from rooms, dining, events, and wellness.</p>
    </div>

    <!-- Filter Buttons -->
    <div class="text-center mb-4">
      <button class="btn btn-premium me-2 filter-btn active" data-filter="all">All</button>
      <button class="btn btn-premium me-2 filter-btn" data-filter="rooms">Rooms</button>
      <button class="btn btn-premium me-2 filter-btn" data-filter="dining">Dining</button>
      <button class="btn btn-premium me-2 filter-btn" data-filter="hall">Event Halls</button>
      <button class="btn btn-premium me-2 filter-btn" data-filter="wellness">Wellness</button>
    </div>

    <!-- Full Gallery -->
    <div class="container">
      <div class="row g-4">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-md-4 gallery-item" data-category="<?= htmlspecialchars($row['category']); ?>">
            <img src="<?= htmlspecialchars($row['image_path']); ?>" alt="<?= htmlspecialchars($row['alt_text']); ?>">
            <!-- Soft Delete Button -->
            <form action="remove_image.php" method="post" class="mt-2">
              <input type="hidden" name="id" value="<?= $row['id']; ?>">
              <!-- <button type="submit" class="btn btn-danger btn-sm">Remove</button> -->
            </form>
          </div>
        <?php endwhile; ?>

      </div>
    </div>

    <!-- Add Image Button -->
    <div class="text-center py-4">
      <!-- <button class="btn btn-premium px-4 py-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#addImageModal">
        + Add Image
      </button> -->
      <!-- <a href="index.php" class="btn btn-premium px-4 py-2 rounded-pill">
        ← Back to Homepage
      </a> -->
    </div>
  </div>

  <!-- Add Image Modal -->
  <div class="modal fade" id="addImageModal" tabindex="-1" aria-labelledby="addImageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form action="upload.php" method="post" enctype="multipart/form-data" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addImageModalLabel">Add New Image</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

          <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select" required>
              <option value="rooms">Rooms</option>
              <option value="dining">Dining</option>
              <option value="hall">Event Halls</option>
              <option value="wellness">Wellness</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Alt Text</label>
            <input type="text" name="alt_text" class="form-control" placeholder="Description (optional)">
          </div>
          <div class="mb-3">
            <label class="form-label">Select Image</label>
            <input type="file" name="image" class="form-control" accept="image/*" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-premium">Upload</button>
        </div>
      </form>
    </div>
  </div>

  
  <!-- Premium Footer -->
  <footer class="footer" id="contact">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4">
          <h3 class="footer-title">Himalaya Hotel</h3>
          <p>Experience the pinnacle of luxury hospitality in the heart of the mountains. Our commitment to excellence
            ensures memorable stays for all our guests.</p>
          <div class="mt-3">
            <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-icon"><i class="bi bi-twitter"></i></a>
            <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
          </div>
        </div>
        <div class="col-lg-2 col-md-4">
          <h3 class="footer-title">Quick Links</h3>
          <div class="footer-links">
            <a href="#">Home</a>
            <a href="#about">About Us</a>
            <a href="#rooms">Rooms</a>
            <a href="#services">Services</a>
            <a href="#contact">Contact</a>
          </div>
        </div>
        <div class="col-lg-3 col-md-4">
          <h3 class="footer-title">Contact Us</h3>
          <p><i class="bi bi-geo-alt me-2"></i> College Road, Biratnagar</p>
          <p><i class="bi bi-telephone me-2"></i> +977 9819096819</p>
          <p><i class="bi bi-envelope me-2"></i> info@himalayahotel.com</p>
        </div>
        <!-- <div class="col-lg-3 col-md-4">
          <h3 class="footer-title">Newsletter</h3>
          <p>Subscribe to receive updates and special offers.</p>
          <div class="input-group mb-3">
            <input type="email" class="form-control" placeholder="Your Email">
            <button class="btn btn-danger" type="button">Subscribe</button>
          </div>
        </div> -->
      </div>
      <hr class="my-4 bg-light opacity-10">
      <div class="text-center">
        <p class="mb-0">&copy; 2025 Passion Chasers. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Filter Script -->
  <script>
    const filterButtons = document.querySelectorAll(".filter-btn");
    const galleryItems = document.querySelectorAll(".gallery-item");

    filterButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        filterButtons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");

        const filter = btn.getAttribute("data-filter");

        galleryItems.forEach(item => {
          if (filter === "all" || item.getAttribute("data-category") === filter) {
            item.style.display = "block";
          } else {
            item.style.display = "none";
          }
        });
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>