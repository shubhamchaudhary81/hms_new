<?php
session_start();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Admin | Hotel System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="adminsidebar.css">
    <style>
        body { min-height: 100vh; display: flex; font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif; background: #f5f5f5; }
        .content { flex-grow: 1; margin-left: 260px; padding: 40px 30px 30px 30px; min-height: 100vh; }
        @media (max-width: 991px) { .content { margin-left: 0; padding: 20px 8px 8px 8px; } }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="content">
    <div class="header mb-4"><h2>Settings</h2></div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="mb-3">Profile Settings</h5>
                <form>
                    <div class="mb-3">
                        <label class="form-label">Admin Name</label>
                        <input type="text" class="form-control" value="Admin" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="admin@himalayahotel.com" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" value="" placeholder="Change password" />
                    </div>
                    <button class="btn btn-brown">Save Changes</button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="mb-3">Hotel Settings</h5>
                <form>
                    <div class="mb-3">
                        <label class="form-label">Hotel Name</label>
                        <input type="text" class="form-control" value="Himalaya Hotel" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" value="123 Mountain Road, City" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact</label>
                        <input type="text" class="form-control" value="+91 9000000000" />
                    </div>
                    <button class="btn btn-brown">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html> 