<?php
    include 'config/configdatabse.php';
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Himalaya Hotel </title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/mainindex.css">
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
          <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
          <li class="nav-item"><a class="nav-link" href="#gallery">Gallery</a></li>
          <li class="nav-item"><a class="nav-link" href="rooms.php">Rooms</a></li>
          <li class="nav-item ms-3"><a href="login.php" class="btn btn-premium">Login / Sign Up</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Luxury Hero Section -->
  <section class="hero-section">
    <div class="container text-center">
      <h1 class="hero-title">Experience Himalayan Luxury</h1>
      <p class="hero-subtitle">Discover unmatched comfort, thoughtful service, and true relaxation at our premier luxury hotel—your perfect getaway starts here</p>
      <a href="rooms.php" class="btn btn-premium btn-lg">Explore Our Rooms</a>
    </div>
  </section>

  <!-- Booking Form
  <section class="container booking-form-container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="booking-form">
          <form class="row g-3">
            <div class="col-md-3">
              <label for="checkIn" class="form-label">Check In</label>
              <input type="date" class="form-control" id="checkIn">
            </div>
            <div class="col-md-3">
              <label for="checkOut" class="form-label">Check Out</label>
              <input type="date" class="form-control" id="checkOut">
            </div>
            <div class="col-md-2">
              <label for="adults" class="form-label">Adults</label>
              <select class="form-select" id="adults">
                <option selected>1</option>
                <option>2</option>
                <option>3</option>
                <option>4+</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="children" class="form-label">Children</label>
              <select class="form-select" id="children">
                <option selected>0</option>
                <option>1</option>
                <option>2</option>
                <option>3+</option>
              </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-premium w-100">Check Availability</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section> -->

  <!-- Welcome Section -->
  <section id="about" class="welcome-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 text-center mb-5">
          <h2 class="section-title">Welcome to Himalaya Hotel</h2>
          <p class="lead">Nestled in the heart of the mountains, Himalaya Hotel offers a perfect blend of traditional hospitality and modern luxury. Our commitment to excellence ensures every guest experiences the warmth of our service and the comfort of our accommodations.</p>
        </div>
      </div>
      
      <div class="row g-4">
        <div class="col-md-4">
          <div class="room-card">
            <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" class="room-img" alt="Suite Room">
            <div class="room-body">
              <h3 class="room-title">Luxury Suite</h3>
              <p>Luxury suite with stunning views, premium amenities, and personalized service.</p>
              <a href="#" class="btn btn-outline-primary">View Details</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="room-card">
            <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" class="room-img" alt="Deluxe Room">
            <div class="room-body">
              <h3 class="room-title">Deluxe Room</h3>
              <p>Elegant room with modern comforts, perfect for both business and leisure travelers.</p>
              <a href="#" class="btn btn-outline-primary">View Details</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="room-card">
            <img src="assets/images/standard.jpg" class="room-img" alt="Standard Room">
            <div class="room-body">
              <h3 class="room-title">Standard Room</h3>
              <p>Comfortable and cozy accommodation with all essential amenities for a pleasant stay.</p>
              <a href="#" class="btn btn-outline-primary">View Details</a>
            </div>
          </div>
        </div>
      </div>
      
      <div class="text-center mt-4">
        <a href="#rooms" class="btn btn-premium">View All Rooms</a>
      </div>
    </div>
  </section>

  <!-- Why Choose Us Section -->
  <section id="services" class="why-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 text-center mb-5">
          <h2 class="section-title text-white">Why Choose Himalaya Hotel?</h2>
          <p class="lead text-white-50">We go above and beyond to ensure your stay is nothing short of perfect.</p>
        </div>
      </div>
      
      <div class="row g-4">
        <div class="col-md-3">
          <div class="why-card text-center">
            <div class="why-icon">
              <i class="bi bi-geo-alt"></i>
            </div>
            <h4>Prime Location</h4>
            <p>Centrally located with breathtaking views of the Himalayan range.</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="why-card text-center">
            <div class="why-icon">
              <i class="bi bi-emoji-smile"></i>
            </div>
            <h4>Personalized Service</h4>
            <p>Our dedicated staff ensures your every need is met with care.</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="why-card text-center">
            <div class="why-icon">
              <i class="bi bi-cup-hot"></i>
            </div>
            <h4>Gourmet Dining</h4>
            <p>Experience exquisite local and international cuisine.</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="why-card text-center">
            <div class="why-icon">
             <i class="bi bi-bounding-box-circles"></i>
            </div>
            <h4>Luxury Amenities</h4>
            <p>From spa services to guided tours, we have it all.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Premium Footer -->
  <footer class="footer" id="contact">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4">
          <h3 class="footer-title">Himalaya Hotel</h3>
          <p>Experience the pinnacle of luxury hospitality in the heart of the mountains. Our commitment to excellence ensures memorable stays for all our guests.</p>
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>