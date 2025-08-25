<?php
$headerTitle = "Room Management";
$headerSubtitle = "Manage room availability, status, and maintenance schedules.";
$buttonText = "Add New Room";
$buttonLink = "newroom.php";
$showButton = true;

// Database connection
include_once '../config/configdatabse.php';

// Get total number of rooms
$total_rooms_result = $conn->query("SELECT COUNT(*) as total FROM Room");
$total_rooms_row = $total_rooms_result->fetch_assoc();
$total_rooms = $total_rooms_row['total'];

// Get room counts by status
$available_count = $conn->query("SELECT COUNT(*) as count FROM Room WHERE status = 'available'")->fetch_assoc()['count'];
$occupied_count = $conn->query("SELECT COUNT(*) as count FROM Room WHERE status = 'booked'")->fetch_assoc()['count'];
$maintenance_count = $conn->query("SELECT COUNT(*) as count FROM Room WHERE status = 'maintenance'")->fetch_assoc()['count'];

// Fetch all rooms with their details
$roomsQuery = "SELECT 
    r.room_id,
    r.room_number,
    r.price_per_night,
    r.weekend_price,
    r.season_price,
    r.capacity,
    r.image,
    r.status,
    r.floor_number,
    r.description,
    r.room_type,
    rt.room_type_name,
    rt.description as type_description
FROM Room r
LEFT JOIN RoomType rt ON r.room_type = rt.room_type_id
ORDER BY r.room_number";

$roomsResult = $conn->query($roomsQuery);
$rooms = [];

if($roomsResult && $roomsResult->num_rows > 0) {
    while($row = $roomsResult->fetch_assoc()) {
        // Get amenities for this room
        $amenity_sql = "SELECT a.amenity_name FROM RoomAmenity ra 
                        JOIN Amenity a ON ra.amenity_id = a.amenity_id 
                        WHERE ra.room_id = {$row['room_id']} LIMIT 3";
        $amenity_res = $conn->query($amenity_sql);
        $amenities = [];
        if ($amenity_res) {
            while ($amenity = $amenity_res->fetch_assoc()) {
                $amenities[] = $amenity['amenity_name'];
            }
        }
        $row['amenities'] = $amenities;
        $rooms[] = $row;
    }
}

// Fetch available room types for the edit modal
$roomTypesQuery = "SELECT room_type_id, room_type_name FROM RoomType ORDER BY room_type_name";
$roomTypesResult = $conn->query($roomTypesQuery);
$roomTypes = [];
if($roomTypesResult && $roomTypesResult->num_rows > 0) {
    while($row = $roomTypesResult->fetch_assoc()) {
        $roomTypes[] = $row;
    }
}
?>

<?php include '../include/admin/header.php'; ?>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header-content.php'?>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card filter-card" data-filter="all" data-status="all">
                <div class="stat-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="stat-number"><?= $total_rooms ?></div>
                <div class="stat-label">Total Rooms</div>
            </div>
            <div class="stat-card filter-card" data-filter="available" data-status="available">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?= $available_count ?></div>
                <div class="stat-label">Available</div>
            </div>
            <div class="stat-card filter-card" data-filter="occupied" data-status="booked">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-number"><?= $occupied_count ?></div>
                <div class="stat-label">Occupied</div>
            </div>
            <div class="stat-card filter-card" data-filter="maintenance" data-status="maintenance">
                <div class="stat-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="stat-number"><?= $maintenance_count ?></div>
                <div class="stat-label">Maintenance</div>
            </div>
        </div>

        <!-- Room Table -->
        <div class="table-container">
            <div class="table-header">
                <h5 class="table-title"><i class="fas fa-list me-2"></i>Room Inventory</h5>
                <div class="filter-indicator" id="filterIndicator" style="display: none;">
                    <span class="filter-text">Showing: <span id="currentFilter">All Rooms</span></span>
                    <button class="clear-filter-btn" onclick="clearFilter()">
                        <i class="fas fa-times"></i> Clear Filter
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Type & Details</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Amenities</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="roomsTableBody">
                        <?php foreach ($rooms as $room): ?>
                            <tr class="room-row" data-status="<?= strtolower($room['status']) ?>">
                                <td>
                                    <div class="room-info">
                                        <?php
                                        $imageFile = $room['image'] ?? '';
                                        $imgSrc = '../assets/images/hotel.jpg'; // Default fallback
                                        
                                        if (!empty($imageFile)) {
                                            $roomImagePath = '../uploads/room_images/' . $imageFile;
                                            $fullImagePath = __DIR__ . '/../uploads/room_images/' . $imageFile;
                                            
                                            if (file_exists($fullImagePath)) {
                                                $imgSrc = $roomImagePath;
                                            } else {
                                                $assetsImagePath = '../assets/images/' . $imageFile;
                                                $fullAssetsPath = __DIR__ . '/../assets/images/' . $imageFile;
                                                
                                                if (file_exists($fullAssetsPath)) {
                                                    $imgSrc = $assetsImagePath;
                                                } else {
                                                    $roomType = strtolower($room['room_type_name']);
                                                    if (strpos($roomType, 'deluxe') !== false || strpos($roomType, 'delux') !== false) {
                                                        $imgSrc = '../assets/images/delux.jpg';
                                                    } elseif (strpos($roomType, 'suite') !== false) {
                                                        $imgSrc = '../assets/images/suite.jpg';
                                                    } elseif (strpos($roomType, 'standard') !== false) {
                                                        $imgSrc = '../assets/images/standard.jpg';
                                                    } else {
                                                        $imgSrc = '../assets/images/hotel.jpg';
                                                    }
                                                }
                                            }
                                        } else {
                                            $roomType = strtolower($room['room_type_name']);
                                            if (strpos($roomType, 'deluxe') !== false || strpos($roomType, 'delux') !== false) {
                                                $imgSrc = '../assets/images/delux.jpg';
                                            } elseif (strpos($roomType, 'suite') !== false) {
                                                $imgSrc = '../assets/images/suite.jpg';
                                            } elseif (strpos($roomType, 'standard') !== false) {
                                                $imgSrc = '../assets/images/standard.jpg';
                                            } else {
                                                $imgSrc = '../assets/images/hotel.jpg';
                                            }
                                        }
                                        ?>
                                        <img src="<?= htmlspecialchars($imgSrc) ?>" 
                                             class="room-image" 
                                             alt="Room <?= $room['room_number'] ?>"
                                             onerror="this.src='../assets/images/hotel.jpg'">
                                        <div class="room-details">
                                            <h6>Room <?= htmlspecialchars($room['room_number']) ?></h6>
                                            <p>Floor <?= htmlspecialchars($room['floor_number'] ?? 'N/A') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="room-details">
                                        <h6><?= htmlspecialchars($room['room_type_name']) ?></h6>
                                        <p><?= htmlspecialchars($room['type_description'] ?? 'Luxury accommodation') ?></p>
                                        <small class="text-muted">Capacity: <?= htmlspecialchars($room['capacity'] ?? 'N/A') ?> guests</small>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                        $status = strtolower($room['status']);
                                        $statusClass = 'status-available';
                                        $statusIcon = 'fas fa-check-circle';
                                        if ($status == 'booked' || $status == 'occupied') {
                                            $statusClass = 'status-occupied';
                                            $statusIcon = 'fas fa-exclamation-circle';
                                        } elseif ($status == 'maintenance') {
                                            $statusClass = 'status-maintenance';
                                            $statusIcon = 'fas fa-tools';
                                        }
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <i class="<?= $statusIcon ?> me-1"></i><?= ucfirst($room['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="price-display">रु <?= number_format($room['price_per_night'], 0) ?></div>
                                    <small class="text-muted">per night</small>
                                </td>
                                <td>
                                    <?php if (!empty($room['amenities'])): ?>
                                        <div class="amenities-list">
                                            <?php foreach (array_slice($room['amenities'], 0, 2) as $amenity): ?>
                                                <span class="amenity-tag"><?= htmlspecialchars($amenity) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($room['amenities']) > 2): ?>
                                                <span class="amenity-tag">+<?= count($room['amenities']) - 2 ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No amenities</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-view" onclick="viewRoom(<?= $room['room_id'] ?>)" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action btn-edit" onclick="editRoom(<?= $room['room_id'] ?>)" title="Edit Room">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action btn-status" onclick="updateStatus(<?= $room['room_id'] ?>)" title="Update Status">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <button class="btn-action btn-delete" onclick="deleteRoom(<?= $room['room_id'] ?>)" title="Delete Room">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let currentFilter = 'all';
        const roomsData = <?= json_encode($rooms) ?>;
        const roomTypesData = <?= json_encode($roomTypes) ?>;

        // Filter functionality
        function filterRooms(status) {
            currentFilter = status;
            const roomRows = document.querySelectorAll('.room-row');
            const filterIndicator = document.getElementById('filterIndicator');
            const currentFilterText = document.getElementById('currentFilter');
            
            // Update active state of stat cards
            document.querySelectorAll('.filter-card').forEach(card => {
                card.classList.remove('active');
            });
            
            const activeCard = document.querySelector(`[data-filter="${status}"]`);
            if (activeCard) {
                activeCard.classList.add('active');
            }

            let visibleCount = 0;
            let filterName = 'All Rooms';

            roomRows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                let shouldShow = false;

                if (status === 'all') {
                    shouldShow = true;
                    filterName = 'All Rooms';
                } else if (status === 'available' && rowStatus === 'available') {
                    shouldShow = true;
                    filterName = 'Available Rooms';
                } else if (status === 'occupied' && (rowStatus === 'booked' || rowStatus === 'occupied')) {
                    shouldShow = true;
                    filterName = 'Occupied Rooms';
                } else if (status === 'maintenance' && rowStatus === 'maintenance') {
                    shouldShow = true;
                    filterName = 'Maintenance Rooms';
                }

                if (shouldShow) {
                    row.style.display = '';
                    visibleCount++;
                    // Add fade-in animation
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.style.opacity = '1';
                    }, 50);
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide filter indicator
            if (status !== 'all') {
                filterIndicator.style.display = 'flex';
                currentFilterText.textContent = filterName;
            } else {
                filterIndicator.style.display = 'none';
            }

            // Update room count in the active card
            updateRoomCount(status, visibleCount);
        }

        function clearFilter() {
            filterRooms('all');
        }

        function updateRoomCount(status, count) {
            const activeCard = document.querySelector(`[data-filter="${status}"]`);
            if (activeCard) {
                const numberElement = activeCard.querySelector('.stat-number');
                if (numberElement) {
                    numberElement.textContent = count;
                }
            }
        }

        // Add click event listeners to stat cards
        document.addEventListener('DOMContentLoaded', function() {
            const filterCards = document.querySelectorAll('.filter-card');
            
            filterCards.forEach(card => {
                card.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    filterRooms(filter);
                });
            });

            // Set initial active state
            document.querySelector('[data-filter="all"]').classList.add('active');
        });

        function viewRoom(roomId) {
            // Open room details in a modal or new page
            const room = roomsData.find(r => r.room_id == roomId);
            if (room) {
                showRoomDetailsModal(room);
            }
        }

        function editRoom(roomId) {
            // Show edit room modal
            const room = roomsData.find(r => r.room_id == roomId);
            if (room) {
                showEditRoomModal(room);
            }
        }

        function updateStatus(roomId) {
            // Show status update modal
            const room = roomsData.find(r => r.room_id == roomId);
            if (room) {
                showStatusUpdateModal(room);
            }
        }

        function deleteRoom(roomId) {
            const room = roomsData.find(r => r.room_id == roomId);
            if (room) {
                const roomNumber = room.room_number;
                if (confirm(`Are you sure you want to delete Room ${roomNumber}? This action cannot be undone.`)) {
                    // Show loading state
                    const deleteBtn = event.target.closest('.btn-delete');
                    const originalContent = deleteBtn.innerHTML;
                    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    deleteBtn.disabled = true;
                    
                    // Redirect to delete page
                    window.location.href = `rooms.php?delete_room=${roomId}`;
                }
            }
        }

        function showRoomDetailsModal(room) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Room ${room.room_number} Details</h3>
                        <button class="modal-close" onclick="closeModal(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="room-detail-grid">
                            <div class="detail-item">
                                <label>Room Number:</label>
                                <span>${room.room_number}</span>
                            </div>
                            <div class="detail-item">
                                <label>Type:</label>
                                <span>${room.room_type_name}</span>
                            </div>
                            <div class="detail-item">
                                <label>Status:</label>
                                <span class="status-badge status-${room.status.toLowerCase()}">${room.status}</span>
                            </div>
                            <div class="detail-item">
                                <label>Floor:</label>
                                <span>${room.floor_number || 'N/A'}</span>
                            </div>
                            <div class="detail-item">
                                <label>Capacity:</label>
                                <span>${room.capacity || 'N/A'} guests</span>
                            </div>
                            <div class="detail-item">
                                <label>Price per Night:</label>
                                <span>रु ${parseFloat(room.price_per_night).toLocaleString()}</span>
                            </div>
                            ${room.weekend_price ? `
                                <div class="detail-item">
                                    <label>Weekend Price:</label>
                                    <span>रु ${parseFloat(room.weekend_price).toLocaleString()}</span>
                                </div>
                            ` : ''}
                            ${room.season_price ? `
                                <div class="detail-item">
                                    <label>Seasonal Price:</label>
                                    <span>रु ${parseFloat(room.season_price).toLocaleString()}</span>
                                </div>
                            ` : ''}
                            ${room.description ? `
                                <div class="detail-item full-width">
                                    <label>Description:</label>
                                    <span>${room.description}</span>
                                </div>
                            ` : ''}
                        </div>
                        <div class="modal-actions">
                            <button class="btn-secondary" onclick="editRoom(${room.room_id}); closeModal(this)">
                                <i class="fas fa-edit"></i> Edit Room
                            </button>
                            <button class="btn-primary" onclick="bookRoom(${room.room_id}); closeModal(this)">
                                <i class="fas fa-calendar-plus"></i> Book Room
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            enableOverlayClickToClose(modal);
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function showStatusUpdateModal(room) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Update Room ${room.room_number} Status</h3>
                        <button class="modal-close" onclick="closeModal(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="statusUpdateForm" onsubmit="updateRoomStatus(event, ${room.room_id})">
                            <div class="form-group">
                                <label>Current Status:</label>
                                <span class="status-badge status-${room.status.toLowerCase()}">${room.status}</span>
                            </div>
                            <div class="form-group">
                                <label for="newStatus">New Status:</label>
                                <select id="newStatus" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="available" ${room.status === 'available' ? 'selected' : ''}>Available</option>
                                    <option value="booked" ${room.status === 'booked' ? 'selected' : ''}>Booked</option>
                                    <option value="maintenance" ${room.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="statusNote">Note (Optional):</label>
                                <textarea id="statusNote" name="note" placeholder="Add a note about the status change..."></textarea>
                            </div>
                            <div class="modal-actions">
                                <button type="button" class="btn-secondary" onclick="closeModal(this)">
                                    Cancel
                                </button>
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-save"></i> Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            enableOverlayClickToClose(modal);
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function updateRoomStatus(event, roomId) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const status = formData.get('status');
            const note = formData.get('note');

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;

            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_room_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            showNotification('Status updated successfully!', 'success');
                            closeModal(form);
                            // Reload page to reflect changes
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showNotification(response.message || 'Failed to update status', 'error');
                            submitBtn.innerHTML = originalContent;
                            submitBtn.disabled = false;
                        }
                    } catch (e) {
                        showNotification('An error occurred while updating status', 'error');
                        submitBtn.innerHTML = originalContent;
                        submitBtn.disabled = false;
                    }
                } else {
                    showNotification('Network error occurred', 'error');
                    submitBtn.innerHTML = originalContent;
                    submitBtn.disabled = false;
                }
            };

            xhr.onerror = function() {
                showNotification('Network error occurred', 'error');
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            };

            xhr.send(`room_id=${roomId}&status=${status}&note=${encodeURIComponent(note)}`);
        }

        function showEditRoomModal(room) {
            console.log('Room data for edit:', room); // Debug log
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Edit Room ${room.room_number}</h3>
                        <button class="modal-close" onclick="closeModal(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editRoomForm" onsubmit="updateRoom(event, ${room.room_id})">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="roomNumber">Room Number:</label>
                                    <input type="text" id="roomNumber" name="room_number" value="${room.room_number}" required>
                                </div>
                                <div class="form-group">
                                    <label for="floorNumber">Floor Number:</label>
                                    <input type="number" id="floorNumber" name="floor_number" value="${room.floor_number || ''}" min="1">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="roomType">Room Type:</label>
                                    <select id="roomType" name="room_type" required>
                                        <option value="">Select Room Type</option>
                                        ${roomTypesData.map(type => `
                                            <option value="${type.room_type_id}" ${room.room_type == type.room_type_id ? 'selected' : ''}>
                                                ${type.room_type_name}
                                            </option>
                                        `).join('')}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="capacity">Capacity:</label>
                                    <input type="number" id="capacity" name="capacity" value="${room.capacity || ''}" min="1" max="10">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="pricePerNight">Price per Night (रु):</label>
                                    <input type="number" id="pricePerNight" name="price_per_night" value="${room.price_per_night}" min="0" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="weekendPrice">Weekend Price (रु):</label>
                                    <input type="number" id="weekendPrice" name="weekend_price" value="${room.weekend_price || ''}" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="seasonPrice">Seasonal Price (रु):</label>
                                    <input type="number" id="seasonPrice" name="season_price" value="${room.season_price || ''}" min="0" step="0.01">
                                </div>
                                <div class="form-group">
                                    <label for="status">Status:</label>
                                    <select id="status" name="status" required>
                                        <option value="available" ${room.status === 'available' ? 'selected' : ''}>Available</option>
                                        <option value="booked" ${room.status === 'booked' ? 'selected' : ''}>Booked</option>
                                        <option value="maintenance" ${room.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea id="description" name="description" rows="3" placeholder="Enter room description...">${room.description || ''}</textarea>
                            </div>
                            <div class="modal-actions">
                                <button type="button" class="btn-secondary" onclick="closeModal(this)">
                                    Cancel
                                </button>
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-save"></i> Update Room
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            enableOverlayClickToClose(modal);
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function updateRoom(event, roomId) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            // Add room_id to the form data
            formData.append('room_id', roomId);

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;

            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_room.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            showNotification('Room updated successfully!', 'success');
                            closeModal(form);
                            // Reload page to reflect changes
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showNotification(response.message || 'Failed to update room', 'error');
                            submitBtn.innerHTML = originalContent;
                            submitBtn.disabled = false;
                        }
                    } catch (e) {
                        showNotification('An error occurred while updating room', 'error');
                        submitBtn.innerHTML = originalContent;
                        submitBtn.disabled = false;
                    }
                } else {
                    showNotification('Network error occurred', 'error');
                    submitBtn.innerHTML = originalContent;
                    submitBtn.disabled = false;
                }
            };

            xhr.onerror = function() {
                showNotification('Network error occurred', 'error');
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            };

            // Convert FormData to URL-encoded string
            const data = new URLSearchParams(formData).toString();
            xhr.send(data);
        }

        function bookRoom(roomId) {
            // Redirect to booking page
            window.location.href = `reservations.php?room_id=${roomId}`;
        }

        function closeModal(element) {
            const modal = element.closest('.modal-overlay');
            modal.classList.remove('show');
            setTimeout(() => modal.remove(), 300);
        }

        function enableOverlayClickToClose(modalOverlay) {
            modalOverlay.addEventListener('click', function(event) {
                if (event.target === modalOverlay) {
                    closeModal(modalOverlay);
                }
            });
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
                <button class="notification-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 10);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
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

        // Smooth animations on load
        window.addEventListener('load', () => {
            document.body.style.opacity = '1';
        });

        console.log('Rooms page initialized');
    </script>

    <style>
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 20px rgba(139, 115, 85, 0.08);
            border: 1px solid #f0ebe4;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(139, 115, 85, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(139, 115, 85, 0.15);
        }

        .stat-card.active {
            background: linear-gradient(135deg, #8b7355 0%, #7a6344 100%);
            color: white;
            border-color: #8b7355;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(139, 115, 85, 0.25);
        }

        .stat-card.active .stat-icon,
        .stat-card.active .stat-number,
        .stat-card.active .stat-label {
            color: white;
        }

        .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            /* color: #666; */
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #8b7355;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .table-container {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 25px rgba(139, 115, 85, 0.12);
            border: 1px solid #f0ebe4;
            overflow: hidden;
            margin: 0;
            width: 100%;
        }

        .table-header {
            padding: 1.5rem 2rem;
            border-bottom: 2px solid #e5d9cc;
            background: linear-gradient(135deg, #8b7355 0%, #7a6344 100%);
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #f59e0b, #10b981, #8b7355);
        }

        .table-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            margin: 0;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .table {
            margin: 0;
            border-collapse: collapse;
            width: 100%;
        }

        .table th {
            background: linear-gradient(135deg, #f8f6f3 0%, #f0ebe4 100%);
            border-bottom: 2px solid #e5d9cc;
            font-weight: 700;
            color: #5a4a3a;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.75rem;
            padding: 1.25rem 1.5rem;
            white-space: nowrap;
            position: relative;
        }

        .table th:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 25%;
            height: 50%;
            width: 1px;
            background: #e5d9cc;
        }

        .table td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f0ebe4;
            font-size: 0.875rem;
            position: relative;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            position: relative;
            opacity: 1;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, #fafafa 0%, #f8f6f3 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(139, 115, 85, 0.08);
        }

        .table tbody tr:nth-child(even) {
            background: rgba(248, 246, 243, 0.3);
        }

        .table tbody tr:nth-child(even):hover {
            background: linear-gradient(135deg, #fafafa 0%, #f8f6f3 100%);
        }

        .room-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .room-image {
            width: 60px;
            height: 60px;
            border-radius: 0.75rem;
            object-fit: cover;
            background: #f0ebe4;
            border: 2px solid #e5d9cc;
            box-shadow: 0 2px 8px rgba(139, 115, 85, 0.1);
            transition: all 0.3s ease;
        }

        .room-image:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(139, 115, 85, 0.2);
        }

        .room-details h6 {
            font-weight: 700;
            color: #5a4a3a;
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
        }

        .room-details p {
            color: #666;
            font-size: 0.875rem;
            margin: 0;
            font-weight: 500;
        }

        .status-badge {
            padding: 0.625rem 1.25rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .status-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .status-badge:hover::before {
            left: 100%;
        }

        .status-available {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .status-occupied {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .status-maintenance {
            background: rgba(107, 114, 128, 0.1);
            color: #6b7280;
        }

        .price-display {
            font-weight: 800;
            color: #8b7355;
            font-size: 1.125rem;
            text-shadow: 0 1px 2px rgba(139, 115, 85, 0.1);
        }

        .amenities-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .amenity-tag {
            background: linear-gradient(135deg, rgba(139, 115, 85, 0.1) 0%, rgba(139, 115, 85, 0.15) 100%);
            color: #8b7355;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(139, 115, 85, 0.2);
            box-shadow: 0 1px 3px rgba(139, 115, 85, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .btn-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-action:hover::before {
            left: 100%;
        }

        .btn-edit {
            background: rgba(139, 115, 85, 0.1);
            color: #8b7355;
        }

        .btn-edit:hover {
            background: #8b7355;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(139, 115, 85, 0.3);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-view {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .btn-view:hover {
            background: #10b981;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-status {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .btn-status:hover {
            background: #f59e0b;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .filter-indicator {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            backdrop-filter: blur(10px);
        }

        .filter-text {
            color: white;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .clear-filter-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .clear-filter-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }

            .table-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .filter-indicator {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.show {
            opacity: 1;
        }

        .modal-content {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal-overlay.show .modal-content {
            transform: scale(1);
        }

        .modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #f0ebe4;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #8b7355 0%, #7a6344 100%);
            color: white;
            border-radius: 1rem 1rem 0 0;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 2rem;
        }

        .room-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-item.full-width {
            grid-column: 1 / -1;
        }

        .detail-item label {
            font-weight: 600;
            color: #5a4a3a;
            font-size: 0.875rem;
        }

        .detail-item span {
            color: #666;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #5a4a3a;
            margin-bottom: 0.5rem;
        }

        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5d9cc;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: border-color 0.3s ease;
        }

        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8b7355;
            box-shadow: 0 0 0 3px rgba(139, 115, 85, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5d9cc;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group select:focus {
            outline: none;
            border-color: #8b7355;
            box-shadow: 0 0 0 3px rgba(139, 115, 85, 0.1);
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f0ebe4;
        }

        .btn-primary,
        .btn-secondary {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #8b7355;
            color: white;
        }

        .btn-primary:hover {
            background: #7a6344;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #f0ebe4;
            color: #8b7355;
        }

        .btn-secondary:hover {
            background: #e5d9cc;
            transform: translateY(-1px);
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 1rem 1.5rem;
            z-index: 1001;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 400px;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification-success {
            border-left: 4px solid #10b981;
        }

        .notification-error {
            border-left: 4px solid #ef4444;
        }

        .notification-info {
            border-left: 4px solid #3b82f6;
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .notification-content i {
            font-size: 1.25rem;
        }

        .notification-success .notification-content i {
            color: #10b981;
        }

        .notification-error .notification-content i {
            color: #ef4444;
        }

        .notification-info .notification-content i {
            color: #3b82f6;
        }

        .notification-close {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: background-color 0.3s ease;
        }

        .notification-close:hover {
            background: #f0ebe4;
        }
    </style>
</body>
</html>