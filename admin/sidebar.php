

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .nav-item {
            margin: 0.25rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            border-radius: 0 25px 25px 0;
            margin-right: 1.25rem;
        }

        .nav-link:hover {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #f4f1eb;
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
            opacity: 1;
            transition: opacity 0.3s ease;
            white-space: nowrap;
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

        .user-profile {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.25rem;
            border-top: 1px solid var(--border-color);
            background: rgba(139, 115, 85, 0.05);
            height: 80px;
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }

        .profile-details {
            flex: 1;
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        .profile-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.125rem;
        }

        .profile-role {
            font-size: 0.75rem;
            color: var(--primary-color);
        }

        .sidebar.collapsed .profile-details {
            opacity: 0;
            width: 0;
        }

        /* Group Title */
        .group-label {
            font-size: 1rem;
            font-weight: 500;
            opacity: 1;
            margin-left: 1.25rem;
            transition: opacity 0.3s ease;
            white-space: nowrap;
            color: var(--text-color);
        }

        .group-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Sub-links under groups */
        .sub-link {
            padding-left: 3rem !important;
            font-size: 0.85rem;
            color: var(--text-color);
        }

        .sub-link:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white !important;
        }

        /* Main content area */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.875rem;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        .sidebar.collapsed + .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1.25rem;
            }
        }

        /* Custom scrollbar for sidebar */
        .nav-menu::-webkit-scrollbar {
            width: 6px;
        }

        .nav-menu::-webkit-scrollbar-track {
            background: rgba(139, 115, 85, 0.05);
        }

        .nav-menu::-webkit-scrollbar-thumb {
            background-color: rgba(139, 115, 85, 0.2);
            border-radius: 3px;
        }

        .nav-menu::-webkit-scrollbar-thumb:hover {
            background-color: rgba(139, 115, 85, 0.3);
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
            <li class="nav-item" data-tooltip="Dashboard">
                <a href="dashboard.php" class="nav-link">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <!-- SETUPS GROUP (no dropdown now) -->
            <li class="nav-item group-label">
                <span class="group-title">
                    <i class="fas fa-cogs"></i> Setups
                </span>
            </li>
            <li class="nav-item">
                <a href="add-room-type.php" class="nav-link sub-link">
                    <i class="fas fa-door-open"></i>
                    <span>Add Room Type</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="add-amenities.php" class="nav-link sub-link">
                    <i class="fas fa-star"></i>
                    <span>Add Amenities</span>
                </a>
            </li>
            <!-- <li class="nav-item">
                <a href="#" class="nav-link sub-link">
                    <i class="fas fa-bed"></i>
                    <span>Add Room Amenity</span>
                </a>
            </li> -->
            <li class="nav-item">
                <a href="add-roomservices.php" class="nav-link sub-link">
                    <i class="fas fa-concierge-bell"></i>
                    <span>Add Room Services</span>
                </a>
            </li>

            <!-- OTHER MAIN LINKS -->
            <li class="nav-item" data-tooltip="Rooms">
                <a href="rooms.php" class="nav-link">
                    <i class="nav-icon fas fa-bed"></i>
                    <span class="nav-text">Rooms</span>
                </a>
            </li>
            <li class="nav-item" data-tooltip="Reservations">
                <a href="reservations.php" class="nav-link">
                    <i class="nav-icon fas fa-calendar-check"></i>
                    <span class="nav-text">Reservations</span>
                </a>
            </li>
            <li class="nav-item" data-tooltip="Reservations">
                <a href="bookings.php" class="nav-link">
                  <i class="nav-icon fas fa-book"></i>
                    <span class="nav-text">Bookings</span>
                </a>
            </li>
            <li class="nav-item" data-tooltip="Guests">
                <a href="guests.php" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                    <span class="nav-text">Guests</span>
                </a>
            </li>
            <li class="nav-item" data-tooltip="Housekeeping">
                <a href="housekeeping.php" class="nav-link">
                    <i class="nav-icon fas fa-broom"></i>
                    <span class="nav-text">Housekeeping</span>
                </a>
            </li>
            <li class="nav-item" data-tooltip="Staff">
                <a href="staff.php" class="nav-link">
                    <i class="nav-icon fas fa-user-tie"></i>
                    <span class="nav-text">Staff</span>
                </a>
            </li>
            <!-- <li class="nav-item" data-tooltip="Billing">
                <a href="billing.php" class="nav-link">
                    <i class="nav-icon fas fa-receipt"></i>
                    <span class="nav-text">Billing</span>
                </a>
            </li> -->
            <li class="nav-item" data-tooltip="Billing">
                <a href="../guest/logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>

        <div class="user-profile"></div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const toggleIcon = toggleBtn?.querySelector('i');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                
                if (sidebar.classList.contains('collapsed')) {
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                } else {
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-chevron-left');
                }
            });
        }

        // Mobile responsiveness
        function handleResize() {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('mobile-open');
            }
        }
        window.addEventListener('resize', handleResize);
        handleResize();

        // Active link management
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                e.currentTarget.classList.add('active');
            });
        });
    </script>
</body>
</html>
