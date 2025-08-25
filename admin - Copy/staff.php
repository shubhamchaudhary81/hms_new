<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff - Admin | Hotel System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="adminsidebar.css">
    <style>
        :root {
            --primary: #8d6e63;
            --secondary: #8a5a44;
            --light: #f8f9fa;
            --dark: #2a2a2a;
            --border: rgba(0,0,0,0.08);
        }
        
        body {
            min-height: 100vh;
            display: flex;
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            color: var(--dark);
        }

        .content {
            flex-grow: 1;
            margin-left: 280px;
            padding: 30px;
            min-height: 100vh;
        }

        .header {
            background: white;
            padding: 20px 25px;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            color: var(--primary);
            font-weight: 700;
            margin: 0;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid var(--border);
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 20px;
            margin-top: 20px;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--primary);
            color: white;
            font-weight: 500;
            border: none;
            padding: 15px 20px;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(106, 76, 147, 0.03);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: #5a3f7a;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.2s ease;
            background: rgba(0,0,0,0.05);
            color: var(--dark);
            border: none;
        }

        .action-btn:hover {
            background: rgba(0,0,0,0.1);
        }

        .staff-role-icon {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: rgba(106, 76, 147, 0.1);
            color: var(--primary);
            margin-right: 10px;
        }

        @media (max-width: 991px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="content">
    <div class="header">
        <h2><i class="bi bi-people me-2"></i>Staff Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
            <i class="bi bi-plus-circle me-2"></i>Add Staff
        </button>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">Ravi Sharma</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="staff-role-icon">
                                    <i class="bi bi-person-gear"></i>
                                </div>
                                <span>Manager</span>
                            </div>
                        </td>
                        <td>+91 9876543210</td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle-fill me-1"></i>Active
                            </span>
                        </td>
                        <td>
                            <button class="action-btn me-2">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="action-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Priya Singh</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="staff-role-icon">
                                    <i class="bi bi-person-lines-fill"></i>
                                </div>
                                <span>Receptionist</span>
                            </div>
                        </td>
                        <td>+91 9123456780</td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-hourglass-split me-1"></i>On Leave
                            </span>
                        </td>
                        <td>
                            <button class="action-btn me-2">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="action-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Ajay Kumar</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="staff-role-icon">
                                    <i class="bi bi-bucket-fill"></i>
                                </div>
                                <span>Housekeeping</span>
                            </div>
                        </td>
                        <td>+91 9988776655</td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                <i class="bi bi-x-circle-fill me-1"></i>Inactive
                            </span>
                        </td>
                        <td>
                            <button class="action-btn me-2">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="action-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing <span class="fw-bold">1</span> to <span class="fw-bold">3</span> of <span class="fw-bold">12</span> staff
            </div>
            <nav>
                <ul class="pagination mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="staffName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="staffName" placeholder="Enter full name">
                    </div>
                    <div class="mb-3">
                        <label for="staffRole" class="form-label">Role</label>
                        <select class="form-select" id="staffRole">
                            <option selected disabled>Select role</option>
                            <option value="manager">Manager</option>
                            <option value="receptionist">Receptionist</option>
                            <option value="housekeeping">Housekeeping</option>
                            <option value="chef">Chef</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="staffContact" class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" id="staffContact" placeholder="Enter contact number">
                    </div>
                    <div class="mb-3">
                        <label for="staffStatus" class="form-label">Status</label>
                        <select class="form-select" id="staffStatus">
                            <option value="active" selected>Active</option>
                            <option value="on_leave">On Leave</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Add Staff</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>