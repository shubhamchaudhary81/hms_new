<?php
// Database connection
include_once '../config/configdatabse.php';

// Fetch all staff members
$staffQuery = "SELECT s.*, r.role_name FROM Staffs s 
               JOIN Roles r ON s.role_id = r.role_id 
               ORDER BY s.staff_id DESC";
$staffResult = $conn->query($staffQuery);

// Fetch all roles for filtering
$rolesQuery = "SELECT * FROM Roles ORDER BY role_name";
$rolesResult = $conn->query($rolesQuery);
$roles = [];
while ($row = $rolesResult->fetch_assoc()) {
    $roles[] = $row;
}

$headerTitle = "Staff Management";
$headerSubtitle = "Manage hotel staff, schedules, and performance.";
$buttonText = "Add New Staff";
$buttonLink = "add_staff.php";
$showButton = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - HotelAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8d6e63;
            --secondary: #8a5a44;
            --accent: #f8c537;
            --light: #f9f5ff;
            --dark: #2a2a2a;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }
       
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fafafa;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        .sidebar.collapsed + .main-content {
            margin-left: 80px;
        }

        .content-header {
            background: white;
           padding: 5px 10px;
            border-radius: 15px;
            box-shadow: 0 2px 20px rgba(139, 115, 85, 0.08);
            margin-top: -23px;
            margin-bottom: 30px;
            border: 1px solid #f0ebe4;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .content-title {
            font-size: 28px;
            font-weight: 600;
            color: #5a4a3a;
            margin-bottom: 8px;
        }

        .content-subtitle {
            color: #8b7355;
            font-size: 16px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8b7355 0%, #a0896b 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(139, 115, 85, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #8b7355;
            border: 1px solid #8b7355;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #8b7355;
            color: white;
        }

        .btn-outline {
            background: transparent;
            color: #8b7355;
            border: 1px solid #e8e2db;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            border-color: #8b7355;
            background: #f8f6f3;
        }

         
        .main-container {
            display: flex;
            flex: 1;
            min-height: 100vh;
            position: relative;
        }

        .content-container {
            flex: 1;
            padding: 40px;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: white;
           padding: 5px 10px;
            border-radius: 15px;
            box-shadow: 0 2px 20px rgba(139, 115, 85, 0.08);
            margin-top: -23px;
            margin-bottom: 30px;
            border: 1px solid #f0ebe4;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-title {
         font-size: 28px;
            font-weight: 600;
            color: #5a4a3a;
            margin-bottom: 8px;
        }

        .page-title i {
            margin-right: 15px;
            font-size: 2rem;
        }

        .btn-primary {
            /* background: var(--primary);
            border: none;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease; */
              background: linear-gradient(135deg, #8b7355 0%, #a0896b 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;

        }

        .btn-primary:hover {
             transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(139, 115, 85, 0.3);
        }
        .btn-outline{
               background: transparent;
            color: #8b7355;
            border: 1px solid #e8e2db;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary {
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 500;
        }

        .filters-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .filter-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-input, .filter-select {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 15px;
            min-width: 200px;
        }

        .staff-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .staff-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .staff-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .staff-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .staff-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
            margin-right: 15px;
        }

        .staff-info {
            flex: 1;
        }

        .staff-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .staff-role {
            color: var(--primary);
            font-weight: 500;
            margin-bottom: 3px;
        }

        .staff-id {
            font-size: 12px;
            color: #666;
        }

        .staff-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .staff-status.active {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .staff-status.inactive {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
        }

        .staff-status.break {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning);
        }

        .staff-details {
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: #666;
        }

        .detail-value {
            color: var(--dark);
            font-weight: 500;
        }

        .staff-actions {
            display: flex;
            gap: 10px;
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #6d5a42;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        /* Modal Styles */
        .modal-header {
            background: var(--primary);
            color: white;
            border-bottom: none;
        }

        .modal-title {
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(106, 76, 147, 0.25);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content-container {
                padding: 25px;
                margin-left: 0;
            }
           
            .page-title {
                font-size: 1.5rem;
            }
           
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
           
            .page-header a {
                margin-top: 15px;
            }

            .filter-group {
                flex-direction: column;
            }

            .filter-input, .filter-select {
                min-width: 100%;
            }

            .staff-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Handle sidebar collapsed state */
        .sidebar.collapsed ~ .main-container .content-container {
            margin-left: 80px;
        }

        /* Ensure proper z-index for sidebar */
        .sidebar {
            z-index: 1000;
        }

        /* Profile Modal Styles */
        .profile-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin: -20px -20px 20px -20px;
        }

        .profile-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 600;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .profile-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .section-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: #6c757d;
            min-width: 120px;
        }

        .info-value {
            font-weight: 600;
            color: var(--dark);
            text-align: right;
        }

        .badge {
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 20px;
        }

        .badge.bg-success {
            background-color: var(--success) !important;
        }

        .badge.bg-danger {
            background-color: var(--danger) !important;
        }

        /* Edit Modal Styles */
        .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Enhanced Button Styles */
        .btn-secondary {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-secondary:hover {
            background: #6d5a42;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(106, 76, 147, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(106, 76, 147, 0.3);
        }

        /* Modal Animation */
        .modal.fade .modal-dialog {
            transform: scale(0.8);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-dialog {
            transform: scale(1);
        }

        /* Responsive Modal */
        @media (max-width: 768px) {
            .modal-dialog {
                margin: 10px;
            }
            
            .profile-header {
                padding: 20px;
                margin: -15px -15px 15px -15px;
            }
            
            .profile-avatar-large {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .info-value {
                text-align: left;
            }
        }

        /* Staff Card Button Container */
        .staff-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .staff-actions .btn {
            flex: 1;
            min-width: 120px;
            justify-content: center;
            font-size: 13px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .staff-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .staff-actions .btn:active {
            transform: translateY(0);
        }

        /* Ensure buttons are clickable */
        .view-staff-btn, .edit-staff-btn {
            pointer-events: auto;
            user-select: none;
        }

        /* Button focus states */
        .staff-actions .btn:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        /* Enhanced Modal Styles */
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-radius: 12px 12px 0 0;
            padding: 25px 30px;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e9ecef;
        }

        /* Form Enhancement */
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(139, 115, 85, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
        }

        /* Profile Modal Enhancements */
        .profile-header {
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }

        .profile-avatar-large {
            position: relative;
            z-index: 2;
            backdrop-filter: blur(10px);
        }

        /* Status Badge Enhancements */
        .staff-status {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 6px 12px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        .staff-status.active {
            background: rgba(40, 167, 69, 0.15);
            color: var(--success);
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .staff-status.inactive {
            background: rgba(220, 53, 69, 0.15);
            color: var(--danger);
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        /* Loading States */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn.loading {
            position: relative;
            color: transparent;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Notification Styles */
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            animation: slideInRight 0.3s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, var(--success), #20c997);
            color: white;
        }

        .alert-error {
            background: linear-gradient(135deg, var(--danger), #e74c3c);
            color: white;
        }

        .alert.alert-dismissible .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .alert.fade {
            animation: slideOutRight 0.3s ease forwards;
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-container">
        <div class="content-container">
          <div class="page-header d-flex justify-content-between align-items-start" style="    margin-top: -30px;">
    <div>
        <h1 class="page-title">Staff Management</h1>
        <p class="page-subtitle">Manage hotel staff, schedules, and performance.</p>
    </div>
    <div>
        <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#addRoleModal">
            <i class="bi bi-plus-circle me-2"></i>Add Role
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
            <i class="bi bi-person-plus me-2"></i>Add Staff
        </button>
    </div>
</div>
           

            <!-- Filters Section -->
        <div class="filters-section">
            <div class="filter-group">
                    <input type="text" class="filter-input" id="searchStaff" placeholder="Search staff...">
                    <select class="filter-select" id="filterRole">
                        <option value="">All Roles</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['role_name'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                        <?php endforeach; ?>
                </select>
                    <select class="filter-select" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                </select>
            </div>
        </div>

            <!-- Staff Grid -->
            <div class="staff-grid" id="staffGrid">
                <?php if ($staffResult && $staffResult->num_rows > 0): ?>
                    <?php while ($staff = $staffResult->fetch_assoc()): ?>
                        <div class="staff-card" 
                             data-staff-id="<?= $staff['staff_id'] ?>"
                             data-first-name="<?= htmlspecialchars($staff['first_name']) ?>"
                             data-last-name="<?= htmlspecialchars($staff['last_name']) ?>"
                             data-email="<?= htmlspecialchars($staff['email']) ?>"
                             data-phone="<?= htmlspecialchars($staff['phone_number']) ?>"
                             data-gender="<?= htmlspecialchars($staff['gender']) ?>"
                             data-role-id="<?= $staff['role_id'] ?>"
                             data-role-name="<?= htmlspecialchars($staff['role_name']) ?>"
                             data-status="<?= $staff['is_active'] ?>"
                             data-role="<?= htmlspecialchars($staff['role_name']) ?>" 
                             data-status="<?= $staff['is_active'] ?>">
                            <div class="staff-header">
                                <div class="staff-avatar"><?= strtoupper(substr($staff['first_name'], 0, 1) . substr($staff['last_name'], 0, 1)) ?></div>
                                <div class="staff-info">
                                    <div class="staff-name"><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></div>
                                    <div class="staff-role"><?= htmlspecialchars($staff['role_name']) ?></div>
                                    <div class="staff-id">ID: <?= $staff['staff_id'] ?></div>
                                </div>
                                <div class="staff-status <?= strtolower($staff['is_active']) ?>"><?= $staff['is_active'] ?></div>
                            </div>
                            <div class="staff-details">
                                <div class="detail-row">
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value"><?= htmlspecialchars($staff['email']) ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Phone</span>
                                    <span class="detail-value"><?= htmlspecialchars($staff['phone_number']) ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Gender</span>
                                    <span class="detail-value"><?= htmlspecialchars($staff['gender']) ?></span>
                                </div>
                            </div>
                            <div class="staff-actions">
                                <button class="btn-secondary view-staff-btn">
                                    <i class="bi bi-eye me-1"></i>View Profile
                                </button>
                                <button class="btn-outline edit-staff-btn">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-people" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3 text-muted">No Staff Members Found</h4>
                        <p class="text-muted">Start by adding some staff members to your team.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoleModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Add New Role
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                <form id="addRoleForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="roleName" class="form-label">Role Name</label>
                            <input type="text" class="form-control" id="roleName" name="roleName" required>
                </div>
                        <div class="mb-3">
                            <label for="roleDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="roleDescription" name="roleDescription" rows="3"></textarea>
                    </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Role</button>
                    </div>
                </form>
                    </div>
                </div>
            </div>

    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStaffModalLabel">
                        <i class="bi bi-person-plus me-2"></i>Add New Staff Member
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                <form id="addStaffForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                    </div>
                </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                </div>
            </div>
                    </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phoneNumber" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required>
                </div>
            </div>
                    </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                    </div>
                </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="roleId" class="form-label">Role</label>
                                    <select class="form-select" id="roleId" name="roleId" required>
                                        <option value="">Select Role</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                </div>
            </div>
                    </div>
                        <div class="mb-3">
                            <label for="isActive" class="form-label">Status</label>
                            <select class="form-select" id="isActive" name="isActive" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                    </div>
                </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Staff Member</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Staff Profile Modal -->
    <div class="modal fade" id="viewStaffModal" tabindex="-1" aria-labelledby="viewStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewStaffModalLabel">
                        <i class="bi bi-person-circle me-2"></i>Staff Profile
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="profile-header text-center mb-4">
                        <div class="profile-avatar-large mx-auto mb-3">
                            <span id="profile-avatar-text"></span>
                        </div>
                        <h4 id="profile-full-name" class="mb-1"></h4>
                        <p class="text-muted mb-0" id="profile-role"></p>
                        <span class="badge" id="profile-status-badge"></span>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="profile-section">
                                <h6 class="section-title"><i class="bi bi-person me-2"></i>Personal Information</h6>
                                <div class="info-item">
                                    <span class="info-label">Full Name:</span>
                                    <span class="info-value" id="profile-name"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Gender:</span>
                                    <span class="info-value" id="profile-gender"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Staff ID:</span>
                                    <span class="info-value" id="profile-staff-id"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-section">
                                <h6 class="section-title"><i class="bi bi-envelope me-2"></i>Contact Information</h6>
                                <div class="info-item">
                                    <span class="info-label">Email:</span>
                                    <span class="info-value" id="profile-email"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Phone:</span>
                                    <span class="info-value" id="profile-phone"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-section mt-4">
                        <h6 class="section-title"><i class="bi bi-briefcase me-2"></i>Employment Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Role:</span>
                                    <span class="info-value" id="profile-role-detail"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Status:</span>
                                    <span class="info-value" id="profile-status-detail"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="editFromProfileBtn">
                        <i class="bi bi-pencil me-2"></i>Edit Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div class="modal fade" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStaffModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Edit Staff Member
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editStaffForm">
                    <div class="modal-body">
                        <input type="hidden" id="editStaffId" name="staffId">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editFirstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="editFirstName" name="firstName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editLastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="editLastName" name="lastName" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="editEmail" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editPhoneNumber" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="editPhoneNumber" name="phoneNumber" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editGender" class="form-label">Gender</label>
                                    <select class="form-select" id="editGender" name="gender" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editRoleId" class="form-label">Role</label>
                                    <select class="form-select" id="editRoleId" name="roleId" required>
                                        <option value="">Select Role</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editIsActive" class="form-label">Status</label>
                            <select class="form-select" id="editIsActive" name="isActive" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Staff Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing staff.php');
            
            // Handle sidebar toggle state
            function updateContentMargin() {
                const sidebar = document.querySelector('.sidebar');
                const contentContainer = document.querySelector('.content-container');
                
                if (sidebar && contentContainer) {
                    if (sidebar.classList.contains('collapsed')) {
                        contentContainer.style.marginLeft = '80px';
                    } else {
                        contentContainer.style.marginLeft = '280px';
                    }
                }
            }

            // Listen for sidebar toggle events
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                const observer = new MutationObserver(updateContentMargin);
                observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
                updateContentMargin();
            }

            // Function to show notifications
            function showNotification(message, type) {
                const alertContainer = document.createElement('div');
                alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
                alertContainer.setAttribute('role', 'alert');
                alertContainer.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(alertContainer);
                
                // Auto-remove after 3 seconds
                setTimeout(() => {
                    alertContainer.classList.remove('show');
                    alertContainer.classList.add('fade');
                    setTimeout(() => alertContainer.remove(), 300);
                }, 3000);
            }

            // Add Role Form Submission
            document.getElementById('addRoleForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = 'Adding...';
                
                const formData = new FormData(this);
                
                fetch('add_role.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Role added successfully!', 'success');
                        this.reset();
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addRoleModal'));
                        modal.hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while adding the role.', 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    submitBtn.innerHTML = originalText;
                });
            });

            // Add Staff Form Submission
            document.getElementById('addStaffForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = 'Adding...';
                
                const formData = new FormData(this);
                
                fetch('add_staff.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Staff member added successfully!', 'success');
                        this.reset();
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addStaffModal'));
                        modal.hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while adding the staff member.', 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    submitBtn.innerHTML = originalText;
                });
            });

            // Edit Staff Form Submission
            document.getElementById('editStaffForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = 'Updating...';
                
                const formData = new FormData(this);
                
                fetch('update_staff.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Staff member updated successfully!', 'success');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editStaffModal'));
                        modal.hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while updating the staff member.', 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    submitBtn.innerHTML = originalText;
                });
            });

            // Search and Filter Functionality
            const searchInput = document.getElementById('searchStaff');
            const roleFilter = document.getElementById('filterRole');
            const statusFilter = document.getElementById('filterStatus');
            const staffCards = document.querySelectorAll('.staff-card');

            function filterStaff() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedRole = roleFilter.value;
                const selectedStatus = statusFilter.value;

                staffCards.forEach(card => {
                    const staffName = card.querySelector('.staff-name').textContent.toLowerCase();
                    const staffRole = card.dataset.roleName; // Changed to data-role-name
                    const staffStatus = card.dataset.status; // Changed to data-status

                    const matchesSearch = staffName.includes(searchTerm);
                    const matchesRole = !selectedRole || staffRole === selectedRole;
                    const matchesStatus = !selectedStatus || staffStatus === selectedStatus;

                    if (matchesSearch && matchesRole && matchesStatus) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            if (searchInput) searchInput.addEventListener('input', filterStaff);
            if (roleFilter) roleFilter.addEventListener('change', filterStaff);
            if (statusFilter) statusFilter.addEventListener('change', filterStaff);

            // Add event listeners for view and edit buttons
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('view-staff-btn') || e.target.closest('.view-staff-btn')) {
                    const button = e.target.classList.contains('view-staff-btn') ? e.target : e.target.closest('.view-staff-btn');
                    const staffCard = button.closest('.staff-card');
                    const staffData = staffCard.dataset;
                    
                    viewStaff(
                        staffData.staffId,
                        staffData.firstName,
                        staffData.lastName,
                        staffData.email,
                        staffData.phone,
                        staffData.gender,
                        staffData.roleName,
                        staffData.status
                    );
                }
                
                if (e.target.classList.contains('edit-staff-btn') || e.target.closest('.edit-staff-btn')) {
                    const button = e.target.classList.contains('edit-staff-btn') ? e.target : e.target.closest('.edit-staff-btn');
                    const staffCard = button.closest('.staff-card');
                    const staffData = staffCard.dataset;
                    
                    editStaff(
                        staffData.staffId,
                        staffData.firstName,
                        staffData.lastName,
                        staffData.email,
                        staffData.phone,
                        staffData.gender,
                        staffData.roleId,
                        staffData.status
                    );
                }
            });

            // Make functions globally available
            window.viewStaff = function(staffId, firstName, lastName, email, phoneNumber, gender, roleName, isActive) {
                console.log('View staff called:', staffId, firstName, lastName);
                
                // Set profile modal content
                document.getElementById('profile-avatar-text').textContent = firstName.charAt(0) + lastName.charAt(0);
                document.getElementById('profile-full-name').textContent = `${firstName} ${lastName}`;
                document.getElementById('profile-role').textContent = `Role: ${roleName}`;
                document.getElementById('profile-status-badge').textContent = isActive;
                document.getElementById('profile-status-badge').className = `badge ${isActive === 'Active' ? 'bg-success' : 'bg-danger'}`;

                document.getElementById('profile-name').textContent = `${firstName} ${lastName}`;
                document.getElementById('profile-gender').textContent = gender;
                document.getElementById('profile-staff-id').textContent = `ID: ${staffId}`;
                document.getElementById('profile-email').textContent = email;
                document.getElementById('profile-phone').textContent = phoneNumber;
                document.getElementById('profile-role-detail').textContent = roleName;
                document.getElementById('profile-status-detail').textContent = isActive;

                // Set up edit button functionality
                const editFromProfileBtn = document.getElementById('editFromProfileBtn');
                if (editFromProfileBtn) {
                    editFromProfileBtn.onclick = function() {
                        // Close view modal and open edit modal
                        const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewStaffModal'));
                        viewModal.hide();
                        
                        // Find the staff card to get the role_id
                        const staffCard = document.querySelector(`[data-staff-id="${staffId}"]`);
                        if (staffCard) {
                            const roleId = staffCard.dataset.roleId;
                            
                            // Populate edit form
                            document.getElementById('editStaffId').value = staffId;
                            document.getElementById('editFirstName').value = firstName;
                            document.getElementById('editLastName').value = lastName;
                            document.getElementById('editEmail').value = email;
                            document.getElementById('editPhoneNumber').value = phoneNumber;
                            document.getElementById('editGender').value = gender;
                            document.getElementById('editRoleId').value = roleId;
                            document.getElementById('editIsActive').value = isActive;
                            
                            // Show edit modal
                            setTimeout(() => {
                                const editModal = new bootstrap.Modal(document.getElementById('editStaffModal'));
                                editModal.show();
                            }, 300);
                        }
                    };
                }

                // Show view modal
                const viewModal = new bootstrap.Modal(document.getElementById('viewStaffModal'));
                viewModal.show();
            };

            window.editStaff = function(staffId, firstName, lastName, email, phoneNumber, gender, roleId, isActive) {
                console.log('Edit staff called:', staffId, firstName, lastName);
                
                // Populate edit form
                document.getElementById('editStaffId').value = staffId;
                document.getElementById('editFirstName').value = firstName;
                document.getElementById('editLastName').value = lastName;
                document.getElementById('editEmail').value = email;
                document.getElementById('editPhoneNumber').value = phoneNumber;
                document.getElementById('editGender').value = gender;
                document.getElementById('editRoleId').value = roleId;
                document.getElementById('editIsActive').value = isActive;
                
                // Show edit modal
                const editModal = new bootstrap.Modal(document.getElementById('editStaffModal'));
                editModal.show();
            };

            // Test button functionality
            console.log('Testing button functionality...');
            const viewButtons = document.querySelectorAll('.view-staff-btn');
            const editButtons = document.querySelectorAll('.edit-staff-btn');
            console.log('Found view buttons:', viewButtons.length);
            console.log('Found edit buttons:', editButtons.length);

            // Add click event listeners directly to buttons as backup
            viewButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const staffCard = this.closest('.staff-card');
                    const staffData = staffCard.dataset;
                    console.log('View button clicked for staff:', staffData.staffId);
                    
                    viewStaff(
                        staffData.staffId,
                        staffData.firstName,
                        staffData.lastName,
                        staffData.email,
                        staffData.phone,
                        staffData.gender,
                        staffData.roleName,
                        staffData.status
                    );
                });
            });

            editButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const staffCard = this.closest('.staff-card');
                    const staffData = staffCard.dataset;
                    console.log('Edit button clicked for staff:', staffData.staffId);
                    
                    editStaff(
                        staffData.staffId,
                        staffData.firstName,
                        staffData.lastName,
                        staffData.email,
                        staffData.phone,
                        staffData.gender,
                        staffData.roleId,
                        staffData.status
                    );
                });
            });

            console.log('Staff page JavaScript initialized successfully');
        });
    </script>
</body>
</html>
