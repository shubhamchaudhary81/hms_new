<?php
    $headerTitle = "Add Room Service";
$headerSubtitle = "Add new room services to enhance guest experience.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Service Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin/content.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            /* padding: 20px; */
        }
        
        /* Main content area styling */
        .main-content {
            flex: 1;
            margin-left: 250px; /* Adjust based on your sidebar width */
            padding: 20px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .breadcrumb {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Form container styling */
        .form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-header {
            background: #8b7355;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .form-title {
            font-weight: 600;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .form-title i {
            margin-right: 10px;
        }
        
        .form-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
            font-size: 1rem;
        }
        
        .form-control, .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #8b7355;
            box-shadow: 0 0 0 3px rgba(139, 115, 85, 0.2);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-outline-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }
        
        .btn-outline-secondary:hover {
            background: #e9ecef;
        }
        
        .btn-primary {
            background: #8b7355;
            color: white;
        }
        
        .btn-primary:hover {
            background: #7a6348;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
            }
        }
        
        @media (max-width: 576px) {
            .form-footer {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar would be here -->
    <?php include('sidebar.php'); ?>
    <div class="main-content">
        <?php include('header-content.php'); ?>
        <!-- <div class="page-header">
            <h1 class="page-title">Room Service Management</h1>
            <div class="breadcrumb">Dashboard / Services / Add Service</div>
        </div> -->
        
        <div class="form-container">
            <div class="form-header">
                <h2 class="form-title"><i class="bi bi-cone-striped"></i>Add Room Service</h2>
            </div>
            
            <div class="form-body">
                <form method="POST" action="">
                    <input type="hidden" name="add_service" value="1">
                    
                    <div class="form-group">
                        <label class="form-label">Service Name</label>
                        <input type="text" class="form-control" name="service_name" placeholder="e.g. Laundry, Room Cleaning" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="4" name="description" placeholder="Service description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" placeholder="Enter price" required step="0.01">
                        <small style="display: block; color: #6c757d; margin-top: 5px;">Enter the price for this service</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Availability Status</label>
                        <select class="form-select" name="availability_status">
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                    
                    <div class="form-footer">
                        <button type="button" class="btn btn-outline-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cancelBtn = document.querySelector('.btn-outline-secondary');
            
            cancelBtn.addEventListener('click', function() {
                document.querySelector('form').reset();
            });
        });
    </script>
</body>
</html>