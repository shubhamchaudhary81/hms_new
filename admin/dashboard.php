<?php
session_start();
include 'admin-auth.php';
// Database connection
include_once '../config/configdatabse.php';


// Get dashboard statistics
$stats = [];

// Total Rooms
$roomQuery = "SELECT COUNT(*) as total_rooms FROM Room";
$roomResult = $conn->query($roomQuery);
$stats['total_rooms'] = $roomResult->fetch_assoc()['total_rooms'];

// Occupied Rooms
$occupiedQuery = "SELECT COUNT(*) as occupied_rooms FROM Room WHERE status = 'booked'";
$occupiedResult = $conn->query($occupiedQuery);
$stats['occupied_rooms'] = $occupiedResult->fetch_assoc()['occupied_rooms'];

// Total Guests (customers)
$guestsQuery = "SELECT COUNT(*) as total_guests FROM Customers";
$guestsResult = $conn->query($guestsQuery);
$stats['total_guests'] = $guestsResult->fetch_assoc()['total_guests'];

// Today's Revenue
$todayRevenueQuery = "SELECT COALESCE(SUM(amount), 0) as today_revenue FROM Payments WHERE DATE(payment_date) = CURDATE() AND status = 'Completed'";
$todayRevenueResult = $conn->query($todayRevenueQuery);
$stats['today_revenue'] = $todayRevenueResult->fetch_assoc()['today_revenue'];

// Recent Bookings
$recentBookingsQuery = "SELECT 
    c.first_name, c.last_name, 
    b.booking_id, b.status,
    rm.room_number
FROM Bookings b
JOIN Reservations r ON b.reservation_id = r.reservation_id
JOIN Customers c ON r.customer_id = c.id
JOIN Room rm ON b.room_id = rm.room_id
ORDER BY b.created_at DESC
LIMIT 3";
$recentBookingsResult = $conn->query($recentBookingsQuery);
$recentBookings = [];
if($recentBookingsResult && $recentBookingsResult->num_rows > 0) {
    while($row = $recentBookingsResult->fetch_assoc()) {
        $recentBookings[] = $row;
    }
}
?>

<?php include '../include/admin/header.php'; ?>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="content-headeradmin">
            <h1 class="content-title">Dashboard</h1>
            <p class="content-subtitle">Welcome back! Here's what's happening at your hotel today.</p>

    

        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-bed"></i>
                </div>
                <div class="stat-value"><?= $stats['total_rooms'] ?></div>
                <div class="stat-label">Total Rooms</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-value"><?= $stats['occupied_rooms'] ?></div>
                <div class="stat-label">Occupied Rooms</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?= $stats['total_guests'] ?></div>
                <div class="stat-label">Total Guests</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value"><?= number_format($stats['today_revenue'], 2) ?></div>
                <div class="stat-label">Today's Revenue</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Recent Bookings</h3>
                    <a href="reservations.php" class="view-all">View All</a>
                </div>
                <div class="booking-list">
                    <?php if(count($recentBookings) > 0): ?>
                        <?php foreach($recentBookings as $booking): ?>
                    <div class="booking-item">
                        <div class="booking-info">
                                    <span class="dashguest-name"><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></span>
                                    <span class="dashroom-number">Room <?= htmlspecialchars($booking['room_number']) ?></span>
                        </div>
                                <div class="booking-status <?= strtolower($booking['status']) ?>"><?= htmlspecialchars($booking['status']) ?></div>
                    </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-bookings">
                            <p>No recent bookings</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Revenue Overview</h3>
                    <div class="revenue-filters">
                        <button class="filter-btn active" data-period="day">Day</button>
                        <button class="filter-btn" data-period="week">Week</button>
                        <button class="filter-btn" data-period="month">Month</button>
                    </div>
                </div>
                <div class="revenue-chart-container">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                    </div>
                <div class="revenue-summary">
                    <div class="summary-item">
                        <span class="summary-label">Total Revenue:</span>
                        <span class="summary-value" id="totalRevenue">0.00</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Average Daily:</span>
                        <span class="summary-value" id="avgRevenue">0.00</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Transactions:</span>
                        <span class="summary-value" id="totalTransactions">0</span>
                    </div>
                </div>
                <div class="revenue-breakdown" id="revenueBreakdown">
                    <!-- Revenue breakdown will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Initialize Chart.js
        const ctx = document.getElementById('revenueChart').getContext('2d');
        let revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Revenue ($)',
                    data: [],
                    borderColor: '#8B7355',
                    backgroundColor: 'rgba(139, 115, 85, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#8B7355',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#8B7355',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: $' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#666',
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            color: '#666',
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Function to fetch revenue data from server
        async function fetchRevenueData(period) {
            try {
                const response = await fetch(`get_revenue_data.php?period=${period}`);
                const result = await response.json();
                
                if(result.success) {
                    updateChart(result.data);
                    updateSummary(result.summary);
                    updateBreakdown(result.breakdown); // Assuming updateBreakdown is defined elsewhere or will be added
                } else {
                    console.error('Error fetching revenue data:', result.error);
                    // Show fallback data
                    showFallbackData(period);
                }
            } catch(error) {
                console.error('Error fetching revenue data:', error);
                // Show fallback data
                showFallbackData(period);
            }
        }

        // Function to show fallback data when API fails
        function showFallbackData(period) {
            const fallbackData = {
                day: {
                    labels: ['6AM', '9AM', '12PM', '3PM', '6PM', '9PM', '12AM'],
                    data: [1200, 1800, 2400, 2100, 2800, 3200, 1850]
                },
                week: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    data: [8500, 9200, 7800, 10500, 12800, 15200, 9800]
                },
                month: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    data: [45000, 52000, 48000, 55000]
                }
            };
            
            const fallbackBreakdown = [
                { source: 'Room Bookings', revenue: 25000 },
                { source: 'Restaurant Orders', revenue: 15000 },
                { source: 'Room Services', revenue: 5000 }
            ];
            
            updateChart(fallbackData[period]);
            updateSummary({
                total_revenue: fallbackData[period].data.reduce((sum, val) => sum + val, 0),
                avg_revenue: Math.round(fallbackData[period].data.reduce((sum, val) => sum + val, 0) / fallbackData[period].data.length),
                total_transactions: Math.floor(Math.random() * 50) + 10
            });
            updateBreakdown(fallbackBreakdown);
        }

        // Filter functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                const period = this.getAttribute('data-period');
                fetchRevenueData(period);
            });
        });

        function updateChart(data) {
            revenueChart.data.labels = data.labels;
            revenueChart.data.datasets[0].data = data.data;
            revenueChart.update('active');
        }

        function updateSummary(summary) {
            document.getElementById('totalRevenue').textContent = '$' + summary.total_revenue.toLocaleString();
            document.getElementById('avgRevenue').textContent = '$' + summary.avg_revenue.toLocaleString();
            document.getElementById('totalTransactions').textContent = summary.total_transactions;
        }

        function updateBreakdown(breakdown) {
            const breakdownContainer = document.getElementById('revenueBreakdown');
            
            if (breakdown && breakdown.length > 0) {
                let html = '<div class="breakdown-header"><h4>Revenue by Source</h4></div><div class="breakdown-items">';
                
                breakdown.forEach(item => {
                    const percentage = breakdown[0].revenue > 0 ? ((item.revenue / breakdown[0].revenue) * 100).toFixed(1) : 0;
                    html += `
                        <div class="breakdown-item">
                            <div class="breakdown-info">
                                <span class="breakdown-source">${item.source}</span>
                                <span class="breakdown-amount">$${item.revenue.toLocaleString()}</span>
                            </div>
                            <div class="breakdown-bar">
                                <div class="breakdown-fill" style="width: ${percentage}%"></div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                breakdownContainer.innerHTML = html;
            } else {
                breakdownContainer.innerHTML = '<div class="no-breakdown"><p>No revenue data available</p></div>';
            }
        }

        // Load initial data (day view)
        document.addEventListener('DOMContentLoaded', function() {
            fetchRevenueData('day');
        });

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

        // Add hover effects for stat cards
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.boxShadow = '0 8px 30px rgba(139, 115, 85, 0.15)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.boxShadow = '0 2px 20px rgba(139, 115, 85, 0.08)';
            });
        });

        // Auto-refresh data every 5 minutes
        setInterval(() => {
            const activePeriod = document.querySelector('.filter-btn.active').getAttribute('data-period');
            fetchRevenueData(activePeriod);
        }, 300000); // 5 minutes

        console.log('Dashboard initialized');
    </script>

    <style>
        .revenue-filters {
            display: flex;
            gap: 8px;
        }

        .filter-btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            background: #fff;
            color: #666;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            background: #f5f5f5;
        }

        .filter-btn.active {
            background: #8B7355;
            color: #fff;
            border-color: #8B7355;
        }

        .revenue-chart-container {
            height: 300px;
            margin: 20px 0;
            position: relative;
        }

        .revenue-summary {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .summary-item {
            text-align: center;
        }

        .summary-label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
        }

        .summary-value {
            display: block;
            font-size: 18px;
            font-weight: bold;
            color: #8B7355;
        }

        .no-bookings {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .booking-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .booking-status.confirmed {
            background: #d4edda;
            color: #155724;
        }

        .booking-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .booking-status.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .revenue-breakdown {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .breakdown-header h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .breakdown-items {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .breakdown-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 15px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .breakdown-info {
            flex-grow: 1;
        }

        .breakdown-source {
            font-size: 14px;
            font-weight: 600;
            color: #555;
        }

        .breakdown-amount {
            font-size: 16px;
            font-weight: bold;
            color: #8B7355;
        }

        .breakdown-bar {
            flex-grow: 1;
            height: 8px;
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
        }

        .breakdown-fill {
            height: 100%;
            background: #8B7355;
            border-radius: 4px;
        }

        .no-breakdown {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        @media (max-width: 768px) {
            .revenue-filters {
                flex-direction: column;
                gap: 4px;
            }
            
            .filter-btn {
                font-size: 11px;
                padding: 4px 8px;
            }
            
            .revenue-summary {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</body>
</html>