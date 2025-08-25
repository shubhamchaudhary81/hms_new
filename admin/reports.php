<?php
$headerTitle = "Reports & Analytics";
$headerSubtitle = "Track performance metrics and generate detailed reports.";
$buttonText = "Export Report";
// $buttonLink = "export.php"; // <- Your target page
$showButton = true;
?>
<?php include '../include/admin/header.php'; ?>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <?php include 'header-content.php'; ?>

        <div class="filters-section">
            <div class="filter-group">
                <select class="filter-select">
                    <option>Last 30 Days</option>
                    <option>Last 7 Days</option>
                    <option>This Month</option>
                    <option>Last Month</option>
                    <option>This Year</option>
                    <option>Custom Range</option>
                </select>
                <select class="filter-select">
                    <option>All Reports</option>
                    <option>Revenue</option>
                    <option>Occupancy</option>
                    <option>Guest Satisfaction</option>
                    <option>Staff Performance</option>
                </select>
                <input type="date" class="filter-input" placeholder="Start Date">
                <input type="date" class="filter-input" placeholder="End Date">
            </div>
        </div>

        <div class="quick-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">$45,230</div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value">78.5%</div>
                <div class="stat-label">Occupancy Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-value">4.6</div>
                <div class="stat-label">Avg Rating</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value">1,234</div>
                <div class="stat-label">Total Guests</div>
            </div>
        </div>

        <div class="reports-grid">
            <div class="report-card">
                <div class="report-header">
                    <h3 class="report-title">Revenue Analysis</h3>
                    <div class="report-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="report-content">
                    <div class="metric">
                        <span class="metric-label">Daily Average</span>
                        <div>
                            <span class="metric-value">$1,508</span>
                            <span class="metric-change positive">+12.5%</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Weekly Total</span>
                        <div>
                            <span class="metric-value">$10,556</span>
                            <span class="metric-change positive">+8.3%</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Monthly Target</span>
                        <div>
                            <span class="metric-value">$50,000</span>
                            <span class="metric-change positive">90.5%</span>
                        </div>
                    </div>
                    <div class="chart-placeholder">
                        Revenue Trend Chart
                    </div>
                </div>
            </div>

            <div class="report-card">
                <div class="report-header">
                    <h3 class="report-title">Occupancy Report</h3>
                    <div class="report-icon">
                        <i class="fas fa-bed"></i>
                    </div>
                </div>
                <div class="report-content">
                    <div class="metric">
                        <span class="metric-label">Current Occupancy</span>
                        <div>
                            <span class="metric-value">78.5%</span>
                            <span class="metric-change positive">+5.2%</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Available Rooms</span>
                        <div>
                            <span class="metric-value">34</span>
                            <span class="metric-change negative">-15.8%</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Avg Length of Stay</span>
                        <div>
                            <span class="metric-value">2.8 days</span>
                            <span class="metric-change positive">+0.3</span>
                        </div>
                    </div>
                    <div class="chart-placeholder">
                        Occupancy Rate Chart
                    </div>
                </div>
            </div>

            <div class="report-card">
                <div class="report-header">
                    <h3 class="report-title">Guest Satisfaction</h3>
                    <div class="report-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                </div>
                <div class="report-content">
                    <div class="metric">
                        <span class="metric-label">Overall Rating</span>
                        <div>
                            <span class="metric-value">4.6/5</span>
                            <span class="metric-change positive">+0.2</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Total Reviews</span>
                        <div>
                            <span class="metric-value">342</span>
                            <span class="metric-change positive">+28</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Repeat Guests</span>
                        <div>
                            <span class="metric-value">23%</span>
                            <span class="metric-change positive">+3.1%</span>
                        </div>
                    </div>
                    <div class="chart-placeholder">
                        Satisfaction Trends
                    </div>
                </div>
            </div>

            <div class="report-card">
                <div class="report-header">
                    <h3 class="report-title">Staff Performance</h3>
                    <div class="report-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div class="report-content">
                    <div class="metric">
                        <span class="metric-label">Active Staff</span>
                        <div>
                            <span class="metric-value">24</span>
                            <span class="metric-change positive">+2</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Avg Response Time</span>
                        <div>
                            <span class="metric-value">8.5 min</span>
                            <span class="metric-change positive">-1.2 min</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Tasks Completed</span>
                        <div>
                            <span class="metric-value">156</span>
                            <span class="metric-change positive">+23</span>
                        </div>
                    </div>
                    <div class="chart-placeholder">
                        Staff Performance Metrics
                    </div>
                </div>
            </div>

            <div class="report-card">
                <div class="report-header">
                    <h3 class="report-title">Financial Summary</h3>
                    <div class="report-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                </div>
                <div class="report-content">
                    <div class="metric">
                        <span class="metric-label">Total Income</span>
                        <div>
                            <span class="metric-value">$45,230</span>
                            <span class="metric-change positive">+15.2%</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Operating Costs</span>
                        <div>
                            <span class="metric-value">$28,450</span>
                            <span class="metric-change negative">+3.8%</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Net Profit</span>
                        <div>
                            <span class="metric-value">$16,780</span>
                            <span class="metric-change positive">+22.1%</span>
                        </div>
                    </div>
                    <div class="chart-placeholder">
                        Financial Overview Chart
                    </div>
                </div>
            </div>

            <div class="report-card">
                <div class="report-header">
                    <h3 class="report-title">Room Type Analysis</h3>
                    <div class="report-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                </div>
                <div class="report-content">
                    <div class="metric">
                        <span class="metric-label">Standard Rooms</span>
                        <div>
                            <span class="metric-value">65%</span>
                            <span class="metric-change positive">+2.1%</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Deluxe Rooms</span>
                        <div>
                            <span class="metric-value">25%</span>
                            <span class="metric-change positive">+1.5%</span>
                        </div>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Suites</span>
                        <div>
                            <span class="metric-value">10%</span>
                            <span class="metric-change negative">-0.8%</span>
                        </div>
                    </div>
                    <div class="chart-placeholder">
                        Room Type Distribution
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

        // Report card interactions
        const reportCards = document.querySelectorAll('.report-card');
        reportCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 8px 30px rgba(139, 115, 85, 0.15)';
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '0 2px 20px rgba(139, 115, 85, 0.08)';
            });
        });

        // Filter functionality
        const filterInputs = document.querySelectorAll('.filter-input, .filter-select');
        filterInputs.forEach(filter => {
            filter.addEventListener('change', () => {
                console.log('Filter changed:', filter.value);
                // Add filtering logic here
            });
        });

        // Export functionality
        const exportBtn = document.querySelector('.btn-primary');
        exportBtn.addEventListener('click', () => {
            console.log('Exporting report...');
            // Add export logic here
        });

        console.log('Reports page initialized');
    </script>
</body>
</html>
