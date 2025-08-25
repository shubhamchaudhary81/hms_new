<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Himalaya Hotel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../css/book.css">
  <link rel="stylesheet" href="../css/reserve.css">
  <link rel="stylesheet" href="../css/guestdash.css">
  <link rel="stylesheet" href="../css/mybooking.css">
  <link rel="stylesheet" href="../css/guest_profile.css">
  <link rel="stylesheet" href="../css/guest_room_details.css">
  <style>
    :root {
      --primary: #5d4037;
      --secondary: #8d6e63;
      --accent: #d4a762;
      --light: #f5f5f5;
      --dark: #2a2a2a;
      --success: #4caf50;
      --warning: #ff9800;
      --info: #2196f3;
    }

    body {
      font-family: 'Montserrat', sans-serif;
      color: var(--dark);
      background-color: #f9f9f9;
      padding-top: 80px;
    }

    h1, h2, h3, h4, h5 {
      font-family: 'Playfair Display', serif;
      font-weight: 600;
    }

    .navbar {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
      padding: 15px 0;
      transition: all 0.3s ease;
    }

    .navbar-brand {
      font-family: 'Playfair Display', serif;
      font-weight: 700;
      font-size: 1.8rem;
      color: var(--primary) !important;
    }

    .nav-link {
      font-weight: 500;
      color: var(--dark);
      margin: 0 10px;
      position: relative;
    }

    .nav-link:after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 0;
      background-color: var(--accent);
      transition: width 0.3s ease;
    }

    .nav-link:hover:after,
    .nav-link.active:after {
      width: 100%;
    }

    .btn-premium {
      background: var(--accent);
      color: white;
      border: none;
      border-radius: 30px;
      padding: 10px 25px;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(212, 167, 98, 0.3);
    }

    .btn-premium:hover {
      background: #c29555;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(212, 167, 98, 0.4);
    }

    @media (max-width: 991px) {
      .hero-title {
        font-size: 2.5rem;
      }

      .hero-section {
        padding: 100px 0 80px;
      }

      .quick-actions {
        margin-top: 0;
      }

      .action-card {
        margin-bottom: 20px;
      }
    }
  </style>
</head>
<body>

<?php
  $current = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-light fixed-top">
  <div class="container">
    <a class="navbar-brand" href="#">Himalaya Hotel</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link <?= $current == 'guestdash.php' ? 'active' : '' ?>" href="../guest/guestdash.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current == 'book.php' ? 'active' : '' ?>" href="../guest/book.php">Book Room</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current == 'mybookings.php' ? 'active' : '' ?>" href="../guest/mybookings.php">My Bookings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current == 'profile.php' ? 'active' : '' ?>" href="../guest/profile.php">Profile</a>
        </li>
        <li class="nav-item ms-2">
          <a class="nav-link btn btn-premium" href="../guest/logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
