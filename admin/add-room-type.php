<?php

$headerTitle = "Add Room Type";
$headerSubtitle = "Manage room types and their descriptions.";
// $buttonText = "Add New Room";
// $buttonLink = "newroom.php";
$showButton = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room Type</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-brown: #8b7355;
            --light-brown: #f7f4f0;
            --dark-brown: #6d5a43;
            --text-dark: #333333;
            --text-light: #777777;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --border-color: #e9e9e9;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background-color: var(--light-gray);
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
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

        
        /* Main content area - adjusted for sidebar */
        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px; /* Adjust this based on your sidebar width */
        }
        
        .container {
            width: 50%;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(to right, var(--primary-brown), var(--dark-brown));
            color: white;
            padding: 25px 30px;
        }
        
        .card-title {
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-brown);
            box-shadow: 0 0 0 3px rgba(139, 115, 85, 0.2);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            padding: 14px 28px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary-brown);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--dark-brown);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-light);
        }
        
        .btn-outline:hover {
            background-color: var(--light-gray);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .card-body {
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include_once("sidebar.php"); ?>

    
    <div class="main-content">
        <?php include_once("header-content.php"); ?>
        <div class="container">
            <div class="card">
                <div class="card-header" style="color: white;">
                    <h1 class="card-title">
                        <i class="fas fa-house"></i>
                        Add Room Type
                    </h1>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="add_room_type" value="1">
                        
                        <div class="form-group">
                            <label class="form-label">Room Type Name</label>
                            <input type="text" class="form-control" name="room_type_name" placeholder="e.g. Deluxe, Suite, Standard" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="4" name="description" placeholder="Type description" required></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="reset" class="btn btn-outline">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Room Type</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const roomName = document.querySelector('input[name="room_type_name"]');
            const description = document.querySelector('textarea[name="description"]');
            
            if (roomName.value.trim() === '') {
                e.preventDefault();
                alert('Please enter a room type name');
                roomName.focus();
                return;
            }
            
            if (description.value.trim() === '') {
                e.preventDefault();
                alert('Please enter a description');
                description.focus();
            }
        });
    </script>
</body>
</html>