
<?php
$headerTitle = "Settings";
$headerSubtitle = "Configure hotel management system preferences and settings.";
// $buttonText = "New Reservation";
// $buttonLink = "addroom.php"; // <- Your target page
$showButton = true;
?>
<?php include '../include/admin/header.php'; ?>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        
        <div class="content-header">
            <div>
                <h1 class="content-title">Settings</h1>
                <p class="content-subtitle">Configure hotel management system preferences and settings.</p>
            </div>
        </div>

        <div class="settings-grid">
            <div class="settings-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-hotel"></i>
                    </div>
                    <h3 class="card-title">Hotel Information</h3>
                </div>
                <div class="card-content">
                    <div class="form-group">
                        <label class="form-label">Hotel Name</label>
                        <input type="text" class="form-input" value="Grand Plaza Hotel" placeholder="Enter hotel name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-textarea" placeholder="Enter hotel address">123 Main Street, Downtown, City 12345</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-input" value="+1 (555) 123-4567" placeholder="Enter phone number">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" value="info@grandplaza.com" placeholder="Enter email address">
                    </div>
                    <button class="btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3 class="card-title">Notifications</h3>
                </div>
                <div class="card-content">
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">Email Notifications</div>
                            <div class="setting-description">Receive email alerts for new bookings and updates</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">SMS Notifications</div>
                            <div class="setting-description">Get SMS alerts for urgent matters</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">Push Notifications</div>
                            <div class="setting-description">Browser push notifications for real-time updates</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">Daily Reports</div>
                            <div class="setting-description">Receive daily summary reports via email</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <h3 class="card-title">Account Settings</h3>
                </div>
                <div class="card-content">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-input" value="John Doe" placeholder="Enter full name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" value="john.doe@grandplaza.com" placeholder="Enter email">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select class="form-select">
                            <option value="manager" selected>Hotel Manager</option>
                            <option value="admin">Administrator</option>
                            <option value="staff">Staff Member</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time Zone</label>
                        <select class="form-select">
                            <option value="est" selected>Eastern Standard Time (EST)</option>
                            <option value="cst">Central Standard Time (CST)</option>
                            <option value="mst">Mountain Standard Time (MST)</option>
                            <option value="pst">Pacific Standard Time (PST)</option>
                        </select>
                    </div>
                    <button class="btn-primary">
                        <i class="fas fa-save"></i>
                        Update Profile
                    </button>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="card-title">Security</h3>
                </div>
                <div class="card-content">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-input" placeholder="Enter current password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-input" placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-input" placeholder="Confirm new password">
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">Two-Factor Authentication</div>
                            <div class="setting-description">Add an extra layer of security to your account</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <button class="btn-primary">
                        <i class="fas fa-key"></i>
                        Change Password
                    </button>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3 class="card-title">Appearance</h3>
                </div>
                <div class="card-content">
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">Dark Mode</div>
                            <div class="setting-description">Switch to dark theme for better night viewing</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">Compact View</div>
                            <div class="setting-description">Show more information in less space</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Language</label>
                        <select class="form-select">
                            <option value="en" selected>English</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Format</label>
                        <select class="form-select">
                            <option value="mm/dd/yyyy" selected>MM/DD/YYYY</option>
                            <option value="dd/mm/yyyy">DD/MM/YYYY</option>
                            <option value="yyyy-mm-dd">YYYY-MM-DD</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3 class="card-title">Data Management</h3>
                </div>
                <div class="card-content">
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">Auto Backup</div>
                            <div class="setting-description">Automatically backup data daily</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Backup Frequency</label>
                        <select class="form-select">
                            <option value="daily" selected>Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button class="btn-secondary">
                            <i class="fas fa-download"></i>
                            Export Data
                        </button>
                        <button class="btn-secondary" style="margin-left: 10px;">
                            <i class="fas fa-upload"></i>
                            Import Data
                        </button>
                    </div>
                    <div class="form-group">
                        <button class="btn-danger">
                            <i class="fas fa-trash"></i>
                            Clear All Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        // const sidebar = document.getElementById('sidebar');
        // const toggleBtn = document.getElementById('toggleBtn');
        // const toggleIcon = toggleBtn?.querySelector('i');

        // if (toggleBtn) {
        //     toggleBtn.addEventListener('click', () => {
        //         sidebar.classList.toggle('collapsed');
                
        //         if (sidebar.classList.contains('collapsed')) {
        //             toggleIcon.classList.remove('fa-chevron-left');
        //             toggleIcon.classList.add('fa-chevron-right');
        //         } else {
        //             toggleIcon.classList.remove('fa-chevron-right');
        //             toggleIcon.classList.add('fa-chevron-left');
        //         }
        //     });
        // }

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

        // Smooth animations on load
        window.addEventListener('load', () => {
            document.body.style.opacity = '1';
        });

        // Settings card interactions
        const settingsCards = document.querySelectorAll('.settings-card');
        settingsCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-3px)';
                card.style.boxShadow = '0 6px 25px rgba(139, 115, 85, 0.12)';
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '0 2px 20px rgba(139, 115, 85, 0.08)';
            });
        });

        // Toggle switch functionality
        const toggleSwitches = document.querySelectorAll('.toggle-switch input');
        toggleSwitches.forEach(toggle => {
            toggle.addEventListener('change', (e) => {
                console.log('Toggle changed:', e.target.checked);
                // Add toggle logic here
            });
        });

        // Form submission handlers
        const saveButtons = document.querySelectorAll('.btn-primary, .btn-secondary');
        saveButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('Settings saved:', button.textContent);
                // Add save logic here
            });
        });

        // Danger button confirmation
        const dangerButtons = document.querySelectorAll('.btn-danger');
        dangerButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Are you sure you want to perform this action? This cannot be undone.')) {
                    console.log('Danger action confirmed:', button.textContent);
                    // Add danger action logic here
                }
            });
        });

        console.log('Settings page initialized');
    </script>
</body>
</html>
