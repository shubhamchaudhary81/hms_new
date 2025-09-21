<?php
$headerTitle = "Hotel Facilities";
$headerSubtitle = "Manage and charge guests for facility usage";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Facilities Management</title>
  <link rel="stylesheet" href="../css/admin/content.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #8b7355;
      --primary-light: #a58c6c;
      --primary-dark: #725b40;
      --accent-color: #d4c1a0;
      --bg-light: #f8f9fa;
      --text-dark: #2c3e50;
      --text-light: #6c757d;
      --card-shadow: 0 10px 30px rgba(139, 115, 85, 0.15);
    }
    
    body {
      background: var(--bg-light);
      color: var(--text-dark);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .main-content {
      margin-left: 250px;
      padding: 30px;
      transition: all 0.3s ease;
    }
    
    @media (max-width: 992px) {
      .main-content {
        margin-left: 0;
      }
    }
    
    .page-header {
      margin-bottom: 2.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid rgba(139, 115, 85, 0.2);
    }
    
    .page-title {
      font-weight: 600;
      color: var(--primary-dark);
      margin-bottom: 0.5rem;
    }
    
    .page-subtitle {
      color: var(--text-light);
      font-size: 1.1rem;
    }
    
    .facility-card {
      transition: all 0.3s ease;
      border: none;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      height: 100%;
      background: linear-gradient(145deg, #ffffff, #f5f5f5);
    }
    
    .facility-card:hover {
      transform: translateY(-8px);
      box-shadow: var(--card-shadow);
    }
    
    .card-inner {
      padding: 2rem 1.5rem;
      text-align: center;
      position: relative;
      z-index: 1;
    }
    
    .facility-icon {
      font-size: 2.8rem;
      margin-bottom: 1.2rem;
      color: var(--primary-color);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: rgba(139, 115, 85, 0.1);
      transition: all 0.3s ease;
    }
    
    .facility-card:hover .facility-icon {
      background: var(--primary-color);
      color: white;
      transform: scale(1.1);
    }
    
    .facility-title {
      font-weight: 600;
      color: var(--primary-dark);
      margin-bottom: 0.5rem;
      font-size: 1.25rem;
    }
    
    .facility-desc {
      color: var(--text-light);
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
    }
    
    .card-hover-bg {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 0;
      background: var(--primary-color);
      opacity: 0;
      transition: all 0.4s ease;
      z-index: 0;
    }
    
    .facility-card:hover .card-hover-bg {
      height: 5px;
      opacity: 1;
    }
    
    .stats-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: var(--accent-color);
      color: var(--primary-dark);
      border-radius: 20px;
      padding: 4px 10px;
      font-size: 0.75rem;
      font-weight: 600;
    }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
  <!-- <div class="page-header">
    <h1 class="page-title"><?= $headerTitle ?></h1>
    <p class="page-subtitle"><?= $headerSubtitle ?></p>
  </div> -->
    <?php include('header-content.php'); ?>

  <div class="row g-4">
    <!-- Pool -->
    <div class="col-xl-3 col-lg-4 col-md-6">
      <a href="facility/hello.php" class="text-decoration-none">
        <div class="card facility-card">
          <!-- <span class="stats-badge">42 today</span> -->
          <div class="card-inner">
            <div class="facility-icon">
              <i class="fas fa-swimming-pool"></i>
            </div>
            <h4 class="facility-title">Swimming Pool</h4>
            <p class="facility-desc">Charges apply for day passes</p>
            <div class="card-hover-bg"></div>
          </div>
        </div>
      </a>
    </div>

    <!-- Gym -->
    <div class="col-xl-3 col-lg-4 col-md-6">
      <a href="gym.php" class="text-decoration-none">
        <div class="card facility-card">
          <!-- <span class="stats-badge">28 today</span> -->
          <div class="card-inner">
            <div class="facility-icon">
              <i class="fas fa-dumbbell"></i>
            </div>
            <h4 class="facility-title">Fitness Center</h4>
            <p class="facility-desc">24/7 access for registered guests</p>
            <div class="card-hover-bg"></div>
          </div>
        </div>
      </a>
    </div>

    <!-- Sauna -->
    <div class="col-xl-3 col-lg-4 col-md-6">
      <a href="sauna.php" class="text-decoration-none">
        <div class="card facility-card">
          <!-- <span class="stats-badge">15 today</span> -->
          <div class="card-inner">
            <div class="facility-icon">
              <i class="fas fa-hot-tub"></i>
            </div>
            <h4 class="facility-title">Sauna & Spa</h4>
            <p class="facility-desc">Reservations recommended</p>
            <div class="card-hover-bg"></div>
          </div>
        </div>
      </a>
    </div>

    <!-- Restaurant -->
    <div class="col-xl-3 col-lg-4 col-md-6">
      <a href="restaurantorder.php" class="text-decoration-none">
        <div class="card facility-card">
          <!-- <span class="stats-badge">67 today</span> -->
          <div class="card-inner">
            <div class="facility-icon">
              <i class="fas fa-utensils"></i>
            </div>
            <h4 class="facility-title">Restaurant</h4>
            <p class="facility-desc">Fine dining experience</p>
            <div class="card-hover-bg"></div>
          </div>
        </div>
      </a>
    </div>
    
    <!-- Additional Facilities -->
    <!-- Conference Room -->
    <div class="col-xl-3 col-lg-4 col-md-6">
      <a href="conference.php" class="text-decoration-none">
        <div class="card facility-card">
          <!-- <span class="stats-badge">3 today</span> -->
          <div class="card-inner">
            <div class="facility-icon">
              <i class="fas fa-concierge-bell"></i>
            </div>
            <h4 class="facility-title">Conference Room</h4>
            <p class="facility-desc">Book for events & meetings</p>
            <div class="card-hover-bg"></div>
          </div>
        </div>
      </a>
    </div>
    
    <!-- Business Center -->
    <div class="col-xl-3 col-lg-4 col-md-6">
      <a href="business.php" class="text-decoration-none">
        <div class="card facility-card">
          <!-- <span class="stats-badge">12 today</span> -->
          <div class="card-inner">
            <div class="facility-icon">
              <i class="fas fa-laptop"></i>
            </div>
            <h4 class="facility-title">Business Center</h4>
            <p class="facility-desc">Printing and workstation services</p>
            <div class="card-hover-bg"></div>
          </div>
        </div>
      </a>
    </div>
    
    <!-- Parking -->
    <div class="col-xl-3 col-lg-4 col-md-6">
      <a href="parking.php" class="text-decoration-none">
        <div class="card facility-card">
          <!-- <span class="stats-badge">38 today</span> -->
          <div class="card-inner">
            <div class="facility-icon">
              <i class="fas fa-parking"></i>
            </div>
            <h4 class="facility-title">Valet Parking</h4>
            <p class="facility-desc">Daily and hourly rates available</p>
            <div class="card-hover-bg"></div>
          </div>
        </div>
      </a>
    </div>
    
    <!-- Laundry -->
    <div class="col-xl-3 col-lg-4 col-md-6">
      <a href="laundry.php" class="text-decoration-none">
        <div class="card facility-card">
          <!-- <span class="stats-badge">24 today</span> -->
          <div class="card-inner">
            <div class="facility-icon">
              <i class="fas fa-tshirt"></i>
            </div>
            <h4 class="facility-title">Laundry Service</h4>
            <p class="facility-desc">Next-day service available</p>
            <div class="card-hover-bg"></div>
          </div>
        </div>
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>