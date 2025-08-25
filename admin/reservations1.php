<?php
$headerTitle = "Reservation Management";
$headerSubtitle = "Manage all hotel bookings and reservations";
$buttonText = "New Reservation";
// $buttonLink = "addroom.php";
$showButton = true;
include_once '../config/configdatabse.php';

// Get filter parameters
$check_in_filter = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out_filter = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_filter = isset($_GET['search']) ? $_GET['search'] : '';

// Build WHERE clause for filters
$where_conditions = [];
$params = [];
$param_types = '';

if ($check_in_filter) {
    $where_conditions[] = "r.requested_check_in_date >= ?";
    $params[] = $check_in_filter;
    $param_types .= 's';
}

if ($check_out_filter) {
    $where_conditions[] = "r.requested_check_out_date <= ?";
    $params[] = $check_out_filter;
    $param_types .= 's';
}

if ($status_filter && $status_filter !== 'All Status') {
    $where_conditions[] = "r.status = ?";
    $params[] = strtolower($status_filter);
    $param_types .= 's';
}

if ($search_filter) {
    $where_conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)";
    $search_term = "%$search_filter%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= 'sss';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Fetch all reservations with filters
$query = "
SELECT 
    r.reservation_id, r.requested_check_in_date AS check_in, r.requested_check_out_date AS check_out, 
    r.num_guests, r.status, r.estimated_total_amount,
    c.first_name, c.last_name, c.email,
    rm.room_number, rt.room_type_name, rm.price_per_night
FROM Reservations r
JOIN Customers c ON r.customer_id = c.id
JOIN RoomType rt ON r.room_type_id = rt.room_type_id
LEFT JOIN Bookings b ON b.reservation_id = r.reservation_id
LEFT JOIN Room rm ON b.room_id = rm.room_id
$where_clause
ORDER BY r.requested_check_in_date ASC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $all_reservations = $stmt->get_result();
    } else {
        $all_reservations = $conn->query($query);
    }
} else {
    $all_reservations = $conn->query($query);
}

// Debug: Check for database errors
if (!$all_reservations) {
    echo "<!-- Database Error: " . $conn->error . " -->";
    $all_reservations = null;
}

// Separate reservations by status for display
$pending_reservations = [];
$confirmed_reservations = [];
$other_reservations = [];

if ($all_reservations && $all_reservations->num_rows > 0) {
    while ($row = $all_reservations->fetch_assoc()) {
        switch ($row['status']) {
            case 'pending':
                $pending_reservations[] = $row;
                break;
            case 'confirmed':
                $confirmed_reservations[] = $row;
                break;
            default:
                $other_reservations[] = $row;
                break;
        }
    }
}

// Debug: Show counts
// echo "<!-- Debug: Total reservations found: " . ($all_reservations ? $all_reservations->num_rows : 'NULL') . " -->";
// echo "<!-- Debug: Pending: " . count($pending_reservations) . " -->";
// echo "<!-- Debug: Confirmed: " . count($confirmed_reservations) . " -->";
// echo "<!-- Debug: Other: " . count($other_reservations) . " -->";

// Debug: Show sample data
// if (!empty($pending_reservations)) {
//     echo "<!-- Debug: Sample pending reservation: " . json_encode($pending_reservations[0]) . " -->";
// }
?>

<?php include '../include/admin/header.php'; ?>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <?php include 'header-content.php'; ?>

        <div class="filters-section">
            <form method="GET" action="" id="filterForm">
                <div class="filter-group">
                    <input type="date" name="check_in" class="filter-input" placeholder="Check-in Date" value="<?= htmlspecialchars($check_in_filter) ?>">
                    <input type="date" name="check_out" class="filter-input" placeholder="Check-out Date" value="<?= htmlspecialchars($check_out_filter) ?>">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Confirmed" <?= $status_filter === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="Checked In" <?= $status_filter === 'Checked In' ? 'selected' : '' ?>>Checked In</option>
                        <option value="Checked Out" <?= $status_filter === 'Checked Out' ? 'selected' : '' ?>>Checked Out</option>
                    </select>
                    <input type="text" name="search" class="filter-input" placeholder="Search guest name..." value="<?= htmlspecialchars($search_filter) ?>">
                    <button type="submit" class="btn-primary">Apply Filters</button>
                    <button type="button" class="btn-outline" onclick="clearFilters()">Clear</button>
                    <button type="button" class="btn-outline" onclick="testModal()">Test Modal</button>
                    <button type="button" class="btn-outline" onclick="testDatabase()">Test DB</button>
                </div>
            </form>
        </div>

        <!-- Debug Information (remove in production) -->
        <div class="debug-info" style="background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 8px; font-family: monospace; font-size: 12px;">
            <h4>Debug Information:</h4>
            <p><strong>Total Reservations:</strong> <?= $all_reservations ? $all_reservations->num_rows : 'NULL' ?></p>
            <p><strong>Pending:</strong> <?= count($pending_reservations) ?></p>
            <p><strong>Confirmed:</strong> <?= count($confirmed_reservations) ?></p>
            <p><strong>Other:</strong> <?= count($other_reservations) ?></p>
            <p><strong>Filters Applied:</strong> <?= !empty($where_conditions) ? 'Yes' : 'No' ?></p>
            <?php if (!empty($where_conditions)): ?>
                <p><strong>Where Conditions:</strong> <?= implode(' AND ', $where_conditions) ?></p>
            <?php endif; ?>
        </div>

        <!-- Status Summary -->
        <!-- <div class="status-summary">
            <div class="status-card pending">
                <span class="status-count"><?= count($pending_reservations) ?></span>
                <span class="status-label">Pending</span>
            </div>
            <div class="status-card confirmed">
                <span class="status-count"><?= count($confirmed_reservations) ?></span>
                <span class="status-label">Confirmed</span>
            </div>
            <div class="status-card total">
                <span class="status-count"><?= count($pending_reservations) + count($confirmed_reservations) + count($other_reservations) ?></span>
                <span class="status-label">Total</span>
            </div>
        </div> -->

        <div class="reservations-table">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest Name</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Guests</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Display pending reservations first
                    if (!empty($pending_reservations)): 
                        foreach ($pending_reservations as $row): 
                    ?>
                        <tr data-status="pending">
                            <td>#<?= (int)$row['reservation_id'] ?></td>
                            <td>
                                <div class="guest-info">
                                    <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong>
                                    <small><?= htmlspecialchars($row['email']) ?></small>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['room_number'] ?? 'TBD') ?> - <?= htmlspecialchars($row['room_type_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['check_in'])) ?></td>
                            <td><?= date('M j, Y', strtotime($row['check_out'])) ?></td>
                            <td><?= (int)$row['num_guests'] ?> Adults</td>
                            <td>रु<?= number_format((float)$row['estimated_total_amount'], 0) ?></td>
                            <td><span class="status pending">Pending</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="View Details" onclick="viewReservation(<?= (int)$row['reservation_id'] ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Confirm" onclick="confirmReservation(<?= (int)$row['reservation_id'] ?>, event)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn-icon" title="Cancel" onclick="cancelReservation(<?= (int)$row['reservation_id'] ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    endif;
                    
                    // Display confirmed reservations
                    if (!empty($confirmed_reservations)): 
                        foreach ($confirmed_reservations as $row): 
                    ?>
                        <tr data-status="confirmed">
                            <td>#<?= (int)$row['reservation_id'] ?></td>
                            <td>
                                <div class="guest-info">
                                    <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong>
                                    <small><?= htmlspecialchars($row['email']) ?></small>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['room_number'] ?? 'TBD') ?> - <?= htmlspecialchars($row['room_type_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['check_in'])) ?></td>
                            <td><?= date('M j, Y', strtotime($row['check_out'])) ?></td>
                            <td><?= (int)$row['num_guests'] ?> Adults</td>
                            <td>रु<?= number_format((float)$row['estimated_total_amount'], 0) ?></td>
                            <td><span class="status confirmed">Confirmed</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="View Details" onclick="viewReservation(<?= (int)$row['reservation_id'] ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Check In" onclick="checkInReservation(<?= (int)$row['reservation_id'] ?>)">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </button>
                                    <button class="btn-icon" title="Cancel" onclick="cancelReservation(<?= (int)$row['reservation_id'] ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    endif;
                    
                    // Display other status reservations
                    if (!empty($other_reservations)): 
                        foreach ($other_reservations as $row): 
                    ?>
                        <tr data-status="<?= strtolower($row['status']) ?>">
                            <td>#<?= (int)$row['reservation_id'] ?></td>
                            <td>
                                <div class="guest-info">
                                    <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong>
                                    <small><?= htmlspecialchars($row['email']) ?></small>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['room_number'] ?? 'TBD') ?> - <?= htmlspecialchars($row['room_type_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['check_in'])) ?></td>
                            <td><?= date('M j, Y', strtotime($row['check_out'])) ?></td>
                            <td><?= (int)$row['num_guests'] ?> Adults</td>
                            <td>रु<?= number_format((float)$row['estimated_total_amount'], 0) ?></td>
                            <td><span class="status <?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" title="View Details" onclick="viewReservation(<?= (int)$row['reservation_id'] ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($row['status'] === 'checked_in'): ?>
                                        <button class="btn-icon" title="Check Out" onclick="checkOutReservation(<?= (int)$row['reservation_id'] ?>)">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    endif;
                    
                    // If no reservations found
                    if (empty($pending_reservations) && empty($confirmed_reservations) && empty($other_reservations)): 
                    ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-3"></i>
                                    <p>No reservations found matching your filters.</p>
                                    <button class="btn-outline" onclick="clearFilters()">Clear Filters</button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <button class="btn-outline">Previous</button>
            <span class="page-info">Page 1 of 1</span>
            <button class="btn-outline">Next</button>
        </div>
    </div>

    <!-- View Reservation Modal -->
    <div class="modal fade" id="viewReservationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reservation Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="reservationDetails">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary print-btn"><i class="fas fa-print me-1"></i> Print</button>
                </div>
            </div>
        </div>
    </div>

         <!-- Confirm Reservation Modal -->
     <div class="modal fade" id="confirmReservationModal" tabindex="-1" aria-labelledby="confirmReservationModalLabel" aria-hidden="true">
         <div class="modal-dialog">
             <div class="modal-content">
                 <div class="modal-header">
                     <h5 class="modal-title" id="confirmReservationModalLabel">Confirm Reservation</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                 </div>
                 <div class="modal-body">
                     <p>Are you sure you want to confirm this reservation?</p>
                     <p><strong>Guest:</strong> <span id="confirmGuestName"></span></p>
                     <p><strong>Room Type:</strong> <span id="confirmRoomType"></span></p>
                     <p><strong>Check-in:</strong> <span id="confirmCheckIn"></span></p>
                     <p><strong>Check-out:</strong> <span id="confirmCheckOut"></span></p>
                     <div class="form-group">
                         <label for="advanceAmount" class="fw-bold">Advance Amount (रु)</label>
                         <input type="number" id="advanceAmount" class="form-control" value="0" min="0" step="100" required>
                         <div class="form-text text-muted" id="advanceHint">Please enter advance amount</div>
                     </div>
                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                     <button type="button" class="btn btn-success" id="confirmReservationBtn">Confirm Reservation</button>
                 </div>
             </div>
         </div>
     </div>

    <!-- Success/Error Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="actionToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="toastTitle">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let currentReservationId = null;
        let isLoading = false;

        // Sidebar toggle functionality
        function handleResize() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                } else {
                    sidebar.classList.remove('mobile-open');
                }
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();

        // Smooth animations on load
        window.addEventListener('load', () => {
            document.body.style.opacity = '1';
        });

        // Table row selection
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('click', (e) => {
                if (!e.target.closest('button')) {
                    row.classList.toggle('selected');
                }
            });
        });

        // Filter functionality
        function clearFilters() {
            window.location.href = window.location.pathname;
        }
        
        // Test modal function
        function testModal() {
            console.log('Testing modal...');
            currentReservationId = 999;
            document.getElementById('confirmGuestName').textContent = 'Test Guest';
            document.getElementById('confirmRoomType').textContent = 'Test Room';
            document.getElementById('confirmCheckIn').textContent = 'Test Check-in';
            document.getElementById('confirmCheckOut').textContent = 'Test Check-out';
            document.getElementById('advanceAmount').value = 1000;
            document.getElementById('advanceHint').innerHTML = '<strong>Test:</strong> This is a test modal';
            
            // Show modal using Bootstrap 5
            const modal = new bootstrap.Modal(document.getElementById('confirmReservationModal'));
            modal.show();
        }
        
        // Test database connection
        function testDatabase() {
            console.log('Testing database connection...');
            fetch('test_db.php', {
                method: 'POST'
            })
            .then(response => response.text())
            .then(data => {
                console.log('Database test response:', data);
                showToast('Database Test', data, 'success');
            })
            .catch(error => {
                console.log('Database test failed:', error);
                showToast('Database Test Failed', error.message, 'error');
            });
        }

        // Auto-submit filters on change
        document.querySelectorAll('.filter-input, .filter-select').forEach(filter => {
            filter.addEventListener('change', () => {
                if (filter.name === 'search') return; // Don't auto-submit on search input
                document.getElementById('filterForm').submit();
            });
        });

        // Search with delay
        let searchTimeout;
        document.querySelector('input[name="search"]').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 500);
        });

        // Show toast notification
        function showToast(title, message, type = 'success') {
            const toast = document.getElementById('actionToast');
            const toastTitle = document.getElementById('toastTitle');
            const toastMessage = document.getElementById('toastMessage');
            
            toastTitle.textContent = title;
            toastMessage.textContent = message;
            
            // Remove existing classes
            toast.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning');
            
            // Add appropriate class
            if (type === 'success') {
                toast.classList.add('text-bg-success');
            } else if (type === 'error') {
                toast.classList.add('text-bg-danger');
            } else if (type === 'warning') {
                toast.classList.add('text-bg-warning');
            }
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }

        // View reservation details
        function viewReservation(reservationId) {
            if (isLoading) return;
            isLoading = true;
            
            // Show loading state
            document.getElementById('reservationDetails').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Show modal using Bootstrap 5
            const modal = new bootstrap.Modal(document.getElementById('viewReservationModal'));
            modal.show();
            
            // Use fetch API
            fetch('get_reservation_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    reservation_id: reservationId
                })
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('reservationDetails').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('reservationDetails').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error loading reservation details: ${error.message || 'Unknown error'}
                    </div>
                `;
            })
            .finally(() => {
                isLoading = false;
            });
        }

        // Confirm reservation
        function confirmReservation(reservationId, event) {
            console.log('confirmReservation called with ID:', reservationId);
            
            if (!event || !event.target) {
                console.error('Event object is missing or invalid');
                return;
            }
            
            currentReservationId = reservationId;
            const row = event.target.closest('tr');
            
            if (!row) {
                console.error('Could not find table row');
                return;
            }
            
            const guestName = row.querySelector('td:nth-child(2) strong').textContent;
            const roomInfo = row.querySelector('td:nth-child(3)').textContent;
            const checkIn = row.querySelector('td:nth-child(4)').textContent;
            const checkOut = row.querySelector('td:nth-child(5)').textContent;
            const totalAmount = row.querySelector('td:nth-child(7)').textContent.replace('रु', '').replace(',', '');
            
            console.log('Row data:', { guestName, roomInfo, checkIn, checkOut, totalAmount });
            
            // Calculate recommended advance (30% of total)
            const total = parseFloat(totalAmount) || 0;
            const recommendedAdvance = Math.max(Math.round(total * 0.3), 0);
            const minimumAdvance = Math.max(Math.round(total * 0.1), 100); // At least 10% or रु100
            
            console.log('Calculated amounts:', { total, recommendedAdvance, minimumAdvance });
            
            // Update modal content
            document.getElementById('confirmGuestName').textContent = guestName;
            document.getElementById('confirmRoomType').textContent = roomInfo;
            document.getElementById('confirmCheckIn').textContent = checkIn;
            document.getElementById('confirmCheckOut').textContent = checkOut;
            document.getElementById('advanceAmount').value = recommendedAdvance;
            
            // Show advance hint with minimum requirement
            const advanceHint = document.getElementById('advanceHint');
            if (total > 0) {
                advanceHint.innerHTML = `<strong>Recommended:</strong> 30% of total रु${total.toLocaleString()} = रु${recommendedAdvance.toLocaleString()}<br><strong>Minimum:</strong> रु${minimumAdvance.toLocaleString()} (10% of total)`;
            } else {
                advanceHint.innerHTML = '<strong>Minimum advance:</strong> रु100';
            }
            
            console.log('Opening modal...');
            
            // Use Bootstrap 5 syntax
            try {
                const modal = new bootstrap.Modal(document.getElementById('confirmReservationModal'));
                modal.show();
                console.log('Modal opened successfully using Bootstrap 5');
            } catch (error) {
                console.error('Error opening modal:', error);
                showToast('Error', 'Could not open confirmation modal', 'error');
            }
        }

        // Handle confirm reservation button click
        document.getElementById('confirmReservationBtn').addEventListener('click', function() {
            console.log('Confirm button clicked!');
            console.log('Current reservation ID:', currentReservationId);
            
            if (isLoading) {
                console.log('Already loading, returning...');
                return;
            }
            
            if (!currentReservationId) {
                console.error('No reservation ID set!');
                showToast('Error', 'No reservation selected', 'error');
                return;
            }
            
            const advanceAmount = parseFloat(document.getElementById('advanceAmount').value) || 0;
            console.log('Advance amount entered:', advanceAmount);
            
            // Validate advance amount
            if (advanceAmount <= 0) {
                showToast('Warning', 'Please enter a valid advance amount', 'warning');
                document.getElementById('advanceAmount').focus();
                return;
            }
            
            // Get total amount from the row to calculate minimum advance
            const row = document.querySelector(`tr[data-status="pending"]`);
            if (row) {
                const totalText = row.querySelector('td:nth-child(7)').textContent.replace('रु', '').replace(/,/g, '');
                const totalAmount = parseFloat(totalText) || 0;
                const minimumAdvance = Math.max(Math.round(totalAmount * 0.1), 100); // At least 10% or रु100
                
                console.log('Validation data:', { totalAmount, minimumAdvance, advanceAmount });
                
                if (advanceAmount < minimumAdvance) {
                    showToast('Warning', `Minimum advance required: रु${minimumAdvance.toLocaleString()} (10% of total)`, 'warning');
                    document.getElementById('advanceAmount').focus();
                    return;
                }
            }
            
            console.log('Sending request to book_reservation.php...');
            console.log('Request data:', { 
                reservation_id: currentReservationId, 
                status: 'confirmed', 
                advance_amount: advanceAmount 
            });
            isLoading = true;
            
            // Use fetch API instead of jQuery
            fetch('book_reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    reservation_id: currentReservationId,
                    status: 'confirmed',
                    advance_amount: advanceAmount
                })
            })
            .then(response => response.text())
            .then(data => {
                console.log('Response received:', data);
                
                if (data.trim() === 'success') {
                    // Hide modal using Bootstrap 5
                    const modal = bootstrap.Modal.getInstance(document.getElementById('confirmReservationModal'));
                    if (modal) {
                        modal.hide();
                    }
                    showToast('Success', 'Reservation confirmed successfully with advance payment of रु' + advanceAmount.toLocaleString() + '!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error', 'Error: ' + data, 'error');
                }
            })
            .catch(error => {
                console.log('Request failed:', error);
                showToast('Error', 'Error: ' + error.message, 'error');
            })
            .finally(() => {
                isLoading = false;
            });
        });

        // Cancel reservation
        function cancelReservation(reservationId) {
            if (isLoading) return;
            if (!confirm('Are you sure you want to cancel this reservation?')) return;
            
            isLoading = true;
            
            fetch('update_reservation_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    reservation_id: reservationId,
                    status: 'cancelled'
                })
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    showToast('Success', 'Reservation cancelled successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error', 'Error: ' + data, 'error');
                }
            })
            .catch(error => {
                showToast('Error', 'Error: ' + error.message, 'error');
            })
            .finally(() => {
                isLoading = false;
            });
        }

        // Check in reservation
        function checkInReservation(reservationId) {
            if (isLoading) return;
            if (!confirm('Are you sure you want to check in this guest?')) return;
            
            isLoading = true;
            
            fetch('check_in_reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    reservation_id: reservationId
                })
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    showToast('Success', 'Guest checked in successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error', 'Error: ' + data, 'error');
                }
            })
            .catch(error => {
                showToast('Error', 'Error: ' + error.message, 'error');
            })
            .finally(() => {
                isLoading = false;
            });
        }

        // Check out reservation
        function checkOutReservation(reservationId) {
            if (isLoading) return;
            if (!confirm('Are you sure you want to check out this guest?')) return;
            
            isLoading = true;
            
            fetch('update_reservation_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    reservation_id: reservationId,
                    status: 'checked_out'
                })
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    showToast('Success', 'Guest checked out successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error', 'Error: ' + data, 'error');
                }
            })
            .catch(error => {
                showToast('Error', 'Error: ' + error.message, 'error');
            })
            .finally(() => {
                isLoading = false;
            });
        }

        // Print reservation
        document.querySelector('.print-btn').addEventListener('click', function() {
            window.print();
        });

        // Prevent double-clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-icon') && isLoading) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        console.log('Reservations page initialized with enhanced functionality');
        console.log('Modal element exists:', document.getElementById('confirmReservationModal') !== null);
        console.log('Confirm button exists:', document.getElementById('confirmReservationBtn') !== null);
        console.log('Bootstrap modal available:', typeof bootstrap !== 'undefined');
    </script>

    <style>
        .status-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .status-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px 20px;
            border-radius: 8px;
            min-width: 100px;
            text-align: center;
        }
        
        .status-card.pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-card.confirmed {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .status-card.total {
            background: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }
        
        .status-count {
            font-size: 24px;
            font-weight: bold;
            display: block;
        }
        
        .status-label {
            font-size: 14px;
            margin-top: 5px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        #advanceAmount {
            font-size: 16px;
            font-weight: 500;
            text-align: center;
        }
        
        #advanceHint {
            font-size: 13px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .toast-container {
            z-index: 9999;
        }
        
        .btn-icon:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
       </style>
</body>
</html>