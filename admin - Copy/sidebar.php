<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="adminsidebar.css">

<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="bi bi-building"></i> Hotel Admin</h4>
    </div>
    
    <div class="sidebar-nav">
        <a href="admindash.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admindash.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="rooms.php" class="<?= basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : '' ?>">
            <i class="bi bi-door-closed"></i>
            <span>Rooms</span>
        </a>
        <a href="menu_item.php" class="<?= basename($_SERVER['PHP_SELF']) == 'menu_item.php' ? 'active' : '' ?>">
            <i class="bi bi-list-ul"></i>
            <span>Menu</span>
        </a>
        
        <a href="invoice.php" class="<?= basename($_SERVER['PHP_SELF']) == 'invoice.php' ? 'active' : '' ?>">
            <i class="bi bi-people"></i>
            <span>Invoices</span>
        </a>
        
        <!-- Bookings Dropdown -->
        <div class="sidebar-dropdown">
            <a href="#" class="sidebar-dropdown-toggle <?= (basename($_SERVER['PHP_SELF']) == 'booking.php' || basename($_SERVER['PHP_SELF']) == 'reservation.php') ? 'active' : '' ?>" tabindex="0">
                <i class="bi bi-journal-bookmark"></i>
                <span>Bookings</span>
                <i class="bi bi-chevron-down ms-auto" style="font-size:1rem;"></i>
            </a>
            <div class="sidebar-dropdown-menu">
                <a href="booking.php" class="dropdown-item <?= basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'active' : '' ?>">
                    <i class="bi bi-journal"></i> Booking
                </a>
                <a href="reservation.php" class="dropdown-item <?= basename($_SERVER['PHP_SELF']) == 'reservation.php' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check"></i> Reservation
                </a>
            </div>
        </div>
        
        <a href="customers.php" class="<?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>">
            <i class="bi bi-person-lines-fill"></i>
            <span>Customers</span>
        </a>
        
        <a href="adminreviews.php" class="<?= basename($_SERVER['PHP_SELF']) == 'adminreviews.php' ? 'active' : '' ?>">
            <i class="bi bi-bar-chart"></i>
            <span>Reviews</span>
        </a>
        
        <div class="divider"></div>
        
        <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
            <i class="bi bi-gear"></i>
            <span>Settings</span>
        </a>
        
        <a href="../guest/logout.php" class="text-danger logout-btn">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</div>