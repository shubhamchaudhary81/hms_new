<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hotel Admin</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');
    const toggleIcon = toggleBtn.querySelector('i');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      toggleIcon.classList.toggle('fa-chevron-left');
      toggleIcon.classList.toggle('fa-chevron-right');
    });
  </script>
  <style>
    :root {
      --primary-color: #8b7355;
      --secondary-color: #a0896b;
      --light-color: #f8f6f3;
      --border-color: #e8e2db;
      --text-color: #5a4a3a;
      --sidebar-width: 280px;
      --sidebar-collapsed-width: 80px;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #fafafa;
      color: #333;
      overflow-x: hidden;
    }

    /* Sidebar Styles */
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      width: var(--sidebar-width);
      height: 100vh;
      background: linear-gradient(135deg, #ffffff 0%, var(--light-color) 100%);
      box-shadow: 2px 0 15px rgba(139, 115, 85, 0.1);
      transition: all 0.3s ease;
      z-index: 1000;
      border-right: 1px solid var(--border-color);
    }

    .sidebar.collapsed {
      width: var(--sidebar-collapsed-width);
    }

    .sidebar-header {
      padding: 1.25rem 1rem;
      border-bottom: 1px solid var(--border-color);
      background: var(--primary-color);
      color: white;
      position: relative;
      height: 80px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
      transition: all 0.3s ease;
    }

    .logo i {
      font-size: 1.75rem;
      color: #f4f1eb;
    }

    .logo-text {
      font-size: 1.25rem;
      font-weight: 600;
      letter-spacing: -0.5px;
      opacity: 1;
      transition: opacity 0.3s ease;
    }

    .sidebar.collapsed .logo-text {
      opacity: 0;
      width: 0;
    }

    .toggle-btn {
      position: absolute;
      right: -15px;
      top: 50%;
      transform: translateY(-50%);
      width: 30px;
      height: 30px;
      background: var(--primary-color);
      border: 2px solid #f4f1eb;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 0.75rem;
      transition: all 0.3s ease;
      z-index: 1001;
    }

    .toggle-btn:hover {
      background: #6d5a42;
      transform: translateY(-50%) scale(1.1);
    }

    .nav-menu {
      padding: 1.25rem 0;
      list-style: none;
      height: calc(100vh - 160px);
      overflow-y: auto;
    }

    .nav-link {
      display: flex;
      align-items: center;
      padding: 0.75rem 1.25rem;
      color: var(--text-color);
      text-decoration: none;
      transition: all 0.3s ease;
      border-radius: 0 25px 25px 0;
      margin-right: 1.25rem;
    }

    .nav-link:hover, 
    .nav-link.active {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      color: white;
    }

    .nav-icon {
      font-size: 1.125rem;
      width: 24px;
      text-align: center;
      margin-right: 0.9375rem;
      transition: all 0.3s ease;
    }

    .nav-text {
      font-size: 0.875rem;
      font-weight: 500;
      white-space: nowrap;
      transition: opacity 0.3s ease;
    }



    /* Smooth transition for submenu toggle arrow */
.nav-link .fa-chevron-down {
  transition: transform 0.3s ease;
}

.nav-link[aria-expanded="true"] .fa-chevron-down {
  transform: rotate(180deg);
}

/* Remove ugly flash effect */
.nav-link:focus, 
.nav-link:active {
  outline: none;
  box-shadow: none;
}

    .sidebar.collapsed .nav-text {
      opacity: 0;
      width: 0;
    }

    .sidebar.collapsed .nav-link {
      justify-content: center;
      margin-right: 0;
      border-radius: 10px;
      margin: 0.25rem 0.625rem;
      padding: 0.75rem;
    }

    .sidebar.collapsed .nav-icon {
      margin-right: 0;
      font-size: 1.25rem;
    }

    /* Dropdown Styles */
    .collapse .dropdown-link {
      display: flex;
      align-items: center;
      padding: 10px 20px 10px 50px;
      color: #6d5a42;
      text-decoration: none;
      transition: all 0.3s ease;
      font-size: 13px;
      border-radius: 0 20px 20px 0;
    }

    .collapse .dropdown-link:hover {
      background: linear-gradient(135deg, #a0896b 0%, #8b7355 100%);
      color: white;
      transform: translateX(3px);
    }

    .dropdown-toggle::after {
      margin-left: auto;
      transition: transform 0.3s;
    }

    .dropdown-toggle[aria-expanded="true"]::after {
      transform: rotate(180deg);
    }
        /* .nav-item{
        padding: 1.25rem 0;
      list-style: none;
      height: calc(100vh - 160px);
      overflow-y: auto;
    } */

    /* Main content */
    .main-content {
      margin-left: var(--sidebar-width);
      padding: 1.875rem;
      transition: margin-left 0.3s ease;
      min-height: 100vh;
    }

    .sidebar.collapsed + .main-content {
      margin-left: var(--sidebar-collapsed-width);
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.mobile-open {
        transform: translateX(0);
      }
      .main-content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="logo">
        <i class="fas fa-hotel"></i>
        <span class="logo-text">HotelAdmin</span>
      </div>
      <button class="toggle-btn" id="toggleBtn">
        <i class="fas fa-chevron-left"></i>
      </button>
    </div>

    <ul class="nav-menu">
      <li>
        <a href="dashboard.php" class="nav-link">
          <i class="nav-icon fas fa-tachometer-alt"></i>
          <span class="nav-text">Dashboard</span>
        </a>
      </li>

      <!-- Bootstrap Collapse Dropdown -->
      <li class="nav-item">
  <a class="nav-link d-flex justify-content-between align-items-center"
     data-bs-toggle="collapse" 
     data-bs-target="#setupsMenu" 
     role="button" 
     aria-expanded="false" 
     aria-controls="setupsMenu"
     href="javascript:void(0)">
    <span><i class="fas fa-cogs"></i> Setups</span>
    <i class="fas fa-chevron-down small transition"></i>
  </a>
  <div class="collapse" id="setupsMenu">
    <ul class="nav flex-column ms-3">
      <li><a class="nav-link" href="addroomtype.php">Add Room Type</a></li>
      <li><a class="nav-link" href="addamenities.php">Add Amenities</a></li>
      <li><a class="nav-link" href="addroom.php">Add Room</a></li>
    </ul>
  </div>
</li>

      <li><a href="rooms.php" class="nav-link"><i class="nav-icon fas fa-bed"></i><span class="nav-text">Rooms</span></a></li>
      <li><a href="reservations.php" class="nav-link"><i class="nav-icon fas fa-calendar-check"></i><span class="nav-text">Reservations</span></a></li>
      <li><a href="guests.php" class="nav-link"><i class="nav-icon fas fa-users"></i><span class="nav-text">Guests</span></a></li>
      <li><a href="housekeeping.php" class="nav-link"><i class="nav-icon fas fa-broom"></i><span class="nav-text">Housekeeping</span></a></li>
      <li><a href="staff.php" class="nav-link"><i class="nav-icon fas fa-user-tie"></i><span class="nav-text">Staff</span></a></li>
      <li><a href="billing.php" class="nav-link"><i class="nav-icon fas fa-receipt"></i><span class="nav-text">Billing</span></a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <h1 class="h3">Dashboard</h1>
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Welcome to Hotel Admin Panel</h5>
        <p class="card-text">This is a demo content area that would be replaced with your actual content.</p>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
 
</body>
</html>
