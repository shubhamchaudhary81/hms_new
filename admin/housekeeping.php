<?php
$headerTitle = "Housekeeping Management";
$headerSubtitle = "Track room cleaning tasks and maintenance schedules";
$buttonText = "Create Task";
$showButton = true;
?>
<?php include '../include/admin/header.php'; ?>
<body>
<?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header-content.php'; ?>
        <?php
        // Database
        include_once '../config/configdatabse.php';

        // Fetch active bookings (no checkout yet) with room number
        $bookings = [];
        $bookingSql = "SELECT b.booking_id, b.room_id, r.room_number
                       FROM Bookings b
                       JOIN Room r ON b.room_id = r.room_id
                       WHERE b.actual_check_out IS NULL
                       ORDER BY b.booking_id DESC";
        if ($result = $conn->query($bookingSql)) {
            while ($row = $result->fetch_assoc()) { $bookings[] = $row; }
        }

        // Fetch room services
        $roomServices = [];
        $rsSql = "SELECT room_service_id, service_name, price, availability_status FROM RoomService ORDER BY service_name";
        if ($rsRes = $conn->query($rsSql)) {
            while ($row = $rsRes->fetch_assoc()) { $roomServices[] = $row; }
        }

        // Fetch active staff
        $staffList = [];
        $staffSql = "SELECT staff_id, first_name, last_name FROM Staffs WHERE is_active = 'Active' ORDER BY first_name, last_name";
        if ($stRes = $conn->query($staffSql)) {
            while ($row = $stRes->fetch_assoc()) { $staffList[] = $row; }
        }

        // Check if BookingRoomService has assigned_staff_id column
        $hasAssignedStaff = false;
        if ($colRes = $conn->query("SHOW COLUMNS FROM BookingRoomService LIKE 'assigned_staff_id'")) {
            $hasAssignedStaff = $colRes->num_rows > 0;
        }

        // Fetch existing tasks from BookingRoomService
        $tasks = [];
        $taskSql = "SELECT brs.booking_room_service_id, brs.booking_id, brs.quantity, brs.service_date, brs.charge_amount, brs.status, brs.notes,
                           rs.service_name, rs.price,
                           r.room_number" .
                           ($hasAssignedStaff ? ", s.first_name AS staff_first, s.last_name AS staff_last" : "") .
                   " FROM BookingRoomService brs
                      JOIN RoomService rs ON brs.room_service_id = rs.room_service_id
                      JOIN Bookings b ON brs.booking_id = b.booking_id
                      JOIN Room r ON b.room_id = r.room_id " .
                   ($hasAssignedStaff ? " LEFT JOIN Staffs s ON brs.assigned_staff_id = s.staff_id " : " ") .
                   " ORDER BY brs.service_date DESC, brs.booking_room_service_id DESC";
        if ($tRes = $conn->query($taskSql)) {
            while ($row = $tRes->fetch_assoc()) { $tasks[] = $row; }
        }
        ?>
        <!-- <div class="content-header">
            <div>
                <h1 class="content-title">Housekeeping Management</h1>
                <p class="content-subtitle">Track room cleaning tasks and maintenance schedules.</p>
            </div>
            <button class="btn-primary">
                <i class="fas fa-plus"></i>
                Create Task
            </button>
        </div> -->

        <div class="filters-section">
            <div class="filter-group">
                <select id="filter-room" class="filter-select">
                    <option value="">All Rooms</option>
                    <?php foreach ($bookings as $b): ?>
                        <option value="<?= htmlspecialchars($b['room_number']) ?>">Room <?= htmlspecialchars($b['room_number']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filter-status" class="filter-select">
                    <option value="">All Status</option>
                    <option>Requested</option>
                    <option>Assigned</option>
                    <option>In Progress</option>
                    <option>Completed</option>
                    <option>Inspection</option>
                </select>
                <select id="filter-service" class="filter-select">
                    <option value="">All Services</option>
                    <?php foreach ($roomServices as $rs): ?>
                        <option value="<?= htmlspecialchars($rs['service_name']) ?>"><?= htmlspecialchars($rs['service_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filter-staff" class="filter-select">
                    <option value="">All Staff</option>
                    <?php if ($hasAssignedStaff): foreach ($staffList as $st): ?>
                        <option value="<?= htmlspecialchars($st['first_name'].' '.$st['last_name']) ?>"><?= htmlspecialchars($st['first_name'].' '.$st['last_name']) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
        </div>

        <div class="housekeeping-grid" id="tasksGrid">
            <?php if (count($tasks) === 0): ?>
                <div class="task-card" style="grid-column: 1/-1;">
                    <div class="task-details">
                        <div class="task-type">No tasks yet</div>
                        <div class="task-notes">Use the Create Task button to add a new housekeeping task.</div>
                    </div>
                </div>
            <?php else: foreach ($tasks as $task): ?>
                <div class="task-card" data-room="<?= htmlspecialchars($task['room_number']) ?>" data-status="<?= htmlspecialchars($task['status']) ?>" data-service="<?= htmlspecialchars($task['service_name']) ?>" data-staff="<?= isset($task['staff_first']) ? htmlspecialchars($task['staff_first'].' '.$task['staff_last']) : '' ?>">
                    <div class="task-header">
                        <span class="room-number">Room <?= htmlspecialchars($task['room_number']) ?></span>
                        <span class="task-priority <?= strtolower($task['status']) ?>"><?= htmlspecialchars($task['status']) ?></span>
                    </div>
                    <div class="task-details">
                        <div class="task-type"><?= htmlspecialchars($task['service_name']) ?></div>
                        <div class="task-info">
                            <?php if ($hasAssignedStaff): ?>
                            <div class="info-row">
                                <span class="info-label">Assigned to:</span>
                                <span class="info-value"><?= isset($task['staff_first']) ? htmlspecialchars($task['staff_first'].' '.$task['staff_last']) : '—' ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="info-row">
                                <span class="info-label">Quantity:</span>
                                <span class="info-value"><?= (int)$task['quantity'] ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Charge:</span>
                                <span class="info-value">रु <?= number_format((float)$task['charge_amount'], 2) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Service Date:</span>
                                <span class="info-value"><?= htmlspecialchars($task['service_date']) ?></span>
                            </div>
                        </div>
                        <div class="task-notes">
                            <?php if (!empty($task['notes'])): ?>
                                <strong>Notes:</strong> <?= htmlspecialchars($task['notes']) ?>
                            <?php else: ?>
                                Booking #<?= (int)$task['booking_id'] ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="task-actions">
                        <button class="btn-secondary">Update Status</button>
                        <button class="btn-outline">View Details</button>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Create Task Popup Module -->
    <div id="createTaskPopup" class="popup-overlay">
        <div class="popup-container">
            <div class="popup-header">
                <h3 class="popup-title">
                    <i class="fas fa-broom me-2"></i>Create Housekeeping Task
                </h3>
                <button type="button" class="popup-close" onclick="closeCreateTaskPopup()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="createTaskForm" class="popup-form">
                <div class="popup-body">
                    <!-- Booking and Service Selection -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-check me-2"></i>Booking
                            </label>
                            <select name="booking_id" id="bookingId" class="form-select" required>
                                <option value="">Select a booking...</option>
                                <?php foreach ($bookings as $b): ?>
                                    <option value="<?= (int)$b['booking_id'] ?>">#<?= (int)$b['booking_id'] ?> - Room <?= htmlspecialchars($b['room_number']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-concierge-bell me-2"></i>Room Service
                            </label>
                            <select name="room_service_id" id="roomServiceId" class="form-select" required>
                                <option value="">Select a service...</option>
                                <?php foreach ($roomServices as $rs): ?>
                                    <option value="<?= (int)$rs['room_service_id'] ?>" data-price="<?= (float)$rs['price'] ?>" <?= ($rs['availability_status'] === 'unavailable' ? 'disabled' : '') ?>>
                                        <?= htmlspecialchars($rs['service_name']) ?> 
                                        <?= ($rs['availability_status'] === 'unavailable' ? '(Unavailable)' : '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Quantity and Pricing Section -->
                    <div class="pricing-section">
                        <h6 class="section-title">
                            <i class="fas fa-calculator me-2"></i>Pricing Details
                        </h6>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Quantity</label>
                                <input type="number" min="1" value="1" name="quantity" id="quantity" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Unit Price (₹)</label>
                                <input type="text" id="unitPrice" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Total Amount (₹)</label>
                                <input type="text" id="totalCharge" class="form-control total-field" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Assignment and Status -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-tie me-2"></i>Assign Staff
                            </label>
                            <select name="assigned_staff_id" id="assignedStaffId" class="form-select" <?= $hasAssignedStaff ? '' : 'disabled' ?> >
                                <?php if (!$hasAssignedStaff): ?>
                                    <option value="">Staff assignment unavailable</option>
                                <?php else: ?>
                                    <option value="">Select staff (optional)</option>
                                    <?php foreach ($staffList as $st): ?>
                                        <option value="<?= (int)$st['staff_id'] ?>"><?= htmlspecialchars($st['first_name'].' '.$st['last_name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <?php if (!$hasAssignedStaff): ?>
                                <small class="form-help">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Staff assignment will be enabled automatically when creating the first task.
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-tasks me-2"></i>Task Status
                            </label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="Requested">Requested</option>
                                <option value="Assigned">Assigned</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Inspection">Inspection</option>
                            </select>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-sticky-note me-2"></i>Additional Notes (Optional)
                        </label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter any special instructions or notes for this task..."></textarea>
                    </div>
                </div>
                
                <div class="popup-footer">
                    <button type="button" class="btn btn-outline" onclick="closeCreateTaskPopup()">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>



    <script>
        // Popup functions
        function openCreateTaskPopup() {
            document.getElementById('createTaskPopup').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeCreateTaskPopup() {
            document.getElementById('createTaskPopup').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close popup when clicking outside
        document.getElementById('createTaskPopup').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateTaskPopup();
            }
        });

        // Close popup with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreateTaskPopup();
            }
        });
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

        // Task card interactions
        const taskCards = document.querySelectorAll('.task-card');
        taskCards.forEach(card => {
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
        function applyFilters() {
            const room = document.getElementById('filter-room')?.value || '';
            const status = document.getElementById('filter-status')?.value || '';
            const service = document.getElementById('filter-service')?.value || '';
            const staff = document.getElementById('filter-staff')?.value || '';

            document.querySelectorAll('#tasksGrid .task-card').forEach(card => {
                const match = (
                    (!room || card.dataset.room === room) &&
                    (!status || card.dataset.status === status) &&
                    (!service || card.dataset.service === service) &&
                    (!staff || (card.dataset.staff || '') === staff)
                );
                card.style.display = match ? 'block' : 'none';
            });
        }
        ['filter-room','filter-status','filter-service','filter-staff'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', applyFilters);
        });

        // Wire header button to open popup
        document.querySelectorAll('.content-header .btn-primary').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                openCreateTaskPopup();
            });
        });

        // Price calculation
        const serviceSelect = document.getElementById('roomServiceId');
        const qtyInput = document.getElementById('quantity');
        const unitPrice = document.getElementById('unitPrice');
        const totalCharge = document.getElementById('totalCharge');

        function recalc() {
            const price = parseFloat(serviceSelect?.selectedOptions[0]?.getAttribute('data-price') || '0');
            const qty = parseInt(qtyInput?.value || '1', 10);
            unitPrice.value = price.toFixed(2);
            totalCharge.value = (price * qty).toFixed(2);
        }
        if (serviceSelect) serviceSelect.addEventListener('change', recalc);
        if (qtyInput) qtyInput.addEventListener('input', recalc);
        recalc();

        // Create Task submit
        const createTaskForm = document.getElementById('createTaskForm');
        if (createTaskForm) {
            createTaskForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const submitBtn = createTaskForm.querySelector('button[type="submit"]');
                const data = new FormData(createTaskForm);
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating...';

                fetch('create_housekeeping_task.php', {
                    method: 'POST',
                    body: data
                }).then(r => r.json()).then(json => {
                    if (json.success) {
                        submitBtn.textContent = 'Created';
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        alert('Error: ' + (json.message || 'Failed to create task'));
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Create Task';
                    }
                }).catch(err => {
                    console.error(err);
                    alert('Network error creating task');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Task';
                });
            });
        }

        // Task status updates
        const statusButtons = document.querySelectorAll('.btn-secondary');
        statusButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('Status update clicked');
                // Add status update logic here
            });
        });

        console.log('Housekeeping page initialized');
    </script>
</body>
</html>
