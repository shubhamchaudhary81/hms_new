<?php
// Database connection
include_once '../config/configdatabse.php';

header('Content-Type: application/json');

$period = isset($_GET['period']) ? $_GET['period'] : 'day';

try {
    $revenueData = [];
    
    switch($period) {
        case 'day':
            // Get hourly revenue for today from all sources
            $sql = "SELECT 
                        HOUR(payment_date) as hour,
                        SUM(amount) as revenue
                    FROM (
                        -- Booking payments
                        SELECT payment_date, amount FROM Payments 
                        WHERE DATE(payment_date) = CURDATE() AND status = 'Completed' AND booking_id IS NOT NULL
                        UNION ALL
                        -- Restaurant order payments
                        SELECT payment_date, amount FROM Payments 
                        WHERE DATE(payment_date) = CURDATE() AND status = 'Completed' AND restaurant_order_id IS NOT NULL
                        UNION ALL
                        -- Room service payments
                        SELECT payment_date, amount FROM Payments 
                        WHERE DATE(payment_date) = CURDATE() AND status = 'Completed' AND booking_id IN (
                            SELECT DISTINCT booking_id FROM BookingRoomService
                        )
                    ) combined_payments
                    GROUP BY HOUR(payment_date)
                    ORDER BY hour";
            
            $result = $conn->query($sql);
            $hourlyData = [];
            
            // Initialize all hours with 0
            for($i = 6; $i <= 23; $i++) {
                $hourlyData[$i] = 0;
            }
            
            if($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $hourlyData[$row['hour']] = (float)$row['revenue'];
                }
            }
            
            $revenueData = [
                'labels' => ['6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM'],
                'data' => array_values($hourlyData)
            ];
            break;
            
        case 'week':
            // Get daily revenue for current week from all sources
            $sql = "SELECT 
                        DAYNAME(payment_date) as day_name,
                        DATE(payment_date) as payment_date,
                        SUM(amount) as revenue
                    FROM (
                        -- Booking payments
                        SELECT payment_date, amount FROM Payments 
                        WHERE YEARWEEK(payment_date) = YEARWEEK(CURDATE()) AND status = 'Completed' AND booking_id IS NOT NULL
                        UNION ALL
                        -- Restaurant order payments
                        SELECT payment_date, amount FROM Payments 
                        WHERE YEARWEEK(payment_date) = YEARWEEK(CURDATE()) AND status = 'Completed' AND restaurant_order_id IS NOT NULL
                        UNION ALL
                        -- Room service payments
                        SELECT payment_date, amount FROM Payments 
                        WHERE YEARWEEK(payment_date) = YEARWEEK(CURDATE()) AND status = 'Completed' AND booking_id IN (
                            SELECT DISTINCT booking_id FROM BookingRoomService
                        )
                    ) combined_payments
                    GROUP BY DATE(payment_date)
                    ORDER BY payment_date";
            
            $result = $conn->query($sql);
            $dailyData = [
                'Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0, 'Thursday' => 0,
                'Friday' => 0, 'Saturday' => 0, 'Sunday' => 0
            ];
            
            if($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $dailyData[$row['day_name']] = (float)$row['revenue'];
                }
            }
            
            $revenueData = [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'data' => array_values($dailyData)
            ];
            break;
            
        case 'month':
            // Get weekly revenue for current month from all sources
            $sql = "SELECT 
                        WEEK(payment_date, 1) as week_number,
                        SUM(amount) as revenue
                    FROM (
                        -- Booking payments
                        SELECT payment_date, amount FROM Payments 
                        WHERE YEAR(payment_date) = YEAR(CURDATE()) AND MONTH(payment_date) = MONTH(CURDATE()) AND status = 'Completed' AND booking_id IS NOT NULL
                        UNION ALL
                        -- Restaurant order payments
                        SELECT payment_date, amount FROM Payments 
                        WHERE YEAR(payment_date) = YEAR(CURDATE()) AND MONTH(payment_date) = MONTH(CURDATE()) AND status = 'Completed' AND restaurant_order_id IS NOT NULL
                        UNION ALL
                        -- Room service payments
                        SELECT payment_date, amount FROM Payments 
                        WHERE YEAR(payment_date) = YEAR(CURDATE()) AND MONTH(payment_date) = MONTH(CURDATE()) AND status = 'Completed' AND booking_id IN (
                            SELECT DISTINCT booking_id FROM BookingRoomService
                        )
                    ) combined_payments
                    GROUP BY WEEK(payment_date, 1)
                    ORDER BY week_number";
            
            $result = $conn->query($sql);
            $weeklyData = [];
            
            // Get current month's weeks
            $currentMonth = date('Y-m');
            $firstDay = date('Y-m-01', strtotime($currentMonth));
            $lastDay = date('Y-m-t', strtotime($currentMonth));
            
            $week1 = date('W', strtotime($firstDay));
            $week4 = date('W', strtotime($lastDay));
            
            // Initialize weeks with 0
            for($i = $week1; $i <= $week4; $i++) {
                $weeklyData[$i] = 0;
            }
            
            if($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $weeklyData[$row['week_number']] = (float)$row['revenue'];
                }
            }
            
            $revenueData = [
                'labels' => array_map(function($week) { return 'Week ' . $week; }, array_keys($weeklyData)),
                'data' => array_values($weeklyData)
            ];
            break;
    }
    
    // Get comprehensive summary statistics
    $summarySql = "";
    switch($period) {
        case 'day':
            $summarySql = "SELECT 
                            SUM(amount) as total_revenue,
                            AVG(amount) as avg_revenue,
                            COUNT(*) as total_transactions
                          FROM (
                            -- Booking payments
                            SELECT amount FROM Payments 
                            WHERE DATE(payment_date) = CURDATE() AND status = 'Completed' AND booking_id IS NOT NULL
                            UNION ALL
                            -- Restaurant order payments
                            SELECT amount FROM Payments 
                            WHERE DATE(payment_date) = CURDATE() AND status = 'Completed' AND restaurant_order_id IS NOT NULL
                            UNION ALL
                            -- Room service payments
                            SELECT amount FROM Payments 
                            WHERE DATE(payment_date) = CURDATE() AND status = 'Completed' AND booking_id IN (
                                SELECT DISTINCT booking_id FROM BookingRoomService
                            )
                          ) combined_payments";
            break;
        case 'week':
            $summarySql = "SELECT 
                            SUM(amount) as total_revenue,
                            AVG(amount) as avg_revenue,
                            COUNT(*) as total_transactions
                          FROM (
                            -- Booking payments
                            SELECT amount FROM Payments 
                            WHERE YEARWEEK(payment_date) = YEARWEEK(CURDATE()) AND status = 'Completed' AND booking_id IS NOT NULL
                            UNION ALL
                            -- Restaurant order payments
                            SELECT amount FROM Payments 
                            WHERE YEARWEEK(payment_date) = YEARWEEK(CURDATE()) AND status = 'Completed' AND restaurant_order_id IS NOT NULL
                            UNION ALL
                            -- Room service payments
                            SELECT amount FROM Payments 
                            WHERE YEARWEEK(payment_date) = YEARWEEK(CURDATE()) AND status = 'Completed' AND booking_id IN (
                                SELECT DISTINCT booking_id FROM BookingRoomService
                            )
                          ) combined_payments";
            break;
        case 'month':
            $summarySql = "SELECT 
                            SUM(amount) as total_revenue,
                            AVG(amount) as avg_revenue,
                            COUNT(*) as total_transactions
                          FROM (
                            -- Booking payments
                            SELECT amount FROM Payments 
                            WHERE YEAR(payment_date) = YEAR(CURDATE()) AND MONTH(payment_date) = MONTH(CURDATE()) AND status = 'Completed' AND booking_id IS NOT NULL
                            UNION ALL
                            -- Restaurant order payments
                            SELECT amount FROM Payments 
                            WHERE YEAR(payment_date) = YEAR(CURDATE()) AND MONTH(payment_date) = MONTH(CURDATE()) AND status = 'Completed' AND restaurant_order_id IS NOT NULL
                            UNION ALL
                            -- Room service payments
                            SELECT amount FROM Payments 
                            WHERE YEAR(payment_date) = YEAR(CURDATE()) AND MONTH(payment_date) = MONTH(CURDATE()) AND status = 'Completed' AND booking_id IN (
                                SELECT DISTINCT booking_id FROM BookingRoomService
                            )
                          ) combined_payments";
            break;
    }
    
    $summaryResult = $conn->query($summarySql);
    $summary = [
        'total_revenue' => 0,
        'avg_revenue' => 0,
        'total_transactions' => 0
    ];
    
    if($summaryResult && $summaryResult->num_rows > 0) {
        $summaryRow = $summaryResult->fetch_assoc();
        $summary['total_revenue'] = (float)$summaryRow['total_revenue'];
        $summary['avg_revenue'] = (float)$summaryRow['avg_revenue'];
        $summary['total_transactions'] = (int)$summaryRow['total_transactions'];
    }
    
    // Get revenue breakdown by source
    $breakdownSql = "SELECT 
                        CASE 
                            WHEN booking_id IS NOT NULL AND restaurant_order_id IS NULL THEN 'Room Bookings'
                            WHEN restaurant_order_id IS NOT NULL THEN 'Restaurant Orders'
                            WHEN booking_id IN (SELECT DISTINCT booking_id FROM BookingRoomService) THEN 'Room Services'
                            ELSE 'Other'
                        END as source,
                        SUM(amount) as revenue
                     FROM Payments 
                     WHERE status = 'Completed'";
    
    switch($period) {
        case 'day':
            $breakdownSql .= " AND DATE(payment_date) = CURDATE()";
            break;
        case 'week':
            $breakdownSql .= " AND YEARWEEK(payment_date) = YEARWEEK(CURDATE())";
            break;
        case 'month':
            $breakdownSql .= " AND YEAR(payment_date) = YEAR(CURDATE()) AND MONTH(payment_date) = MONTH(CURDATE())";
            break;
    }
    
    $breakdownSql .= " GROUP BY source ORDER BY revenue DESC";
    
    $breakdownResult = $conn->query($breakdownSql);
    $breakdown = [];
    
    if($breakdownResult && $breakdownResult->num_rows > 0) {
        while($row = $breakdownResult->fetch_assoc()) {
            $breakdown[] = [
                'source' => $row['source'],
                'revenue' => (float)$row['revenue']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $revenueData,
        'summary' => $summary,
        'breakdown' => $breakdown
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?> 