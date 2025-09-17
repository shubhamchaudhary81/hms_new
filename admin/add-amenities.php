<?php
$headerTitle = "Add Amenity";
$headerSubtitle = "Add new amenities to enhance room features.";

session_start();
include_once '../config/configdatabse.php';

// Initialize message variables from session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$warning_message = isset($_SESSION['warning_message']) ? $_SESSION['warning_message'] : '';

// Clear session messages after retrieving them
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
unset($_SESSION['warning_message']);

// Add Amenity
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_amenity'])) {
    $amenity_name = trim($_POST['amenity_name']);
    $description = trim($_POST['description']);
    $icon_url = trim($_POST['icon_url']);

    if (!empty($amenity_name)) {
        $stmt = $conn->prepare("INSERT INTO Amenity (amenity_name, description, icon_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $amenity_name, $description, $icon_url);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Amenity added successfully!";
            $stmt->close();
            header("Location: add-amenities.php"); // reload same page
            exit();
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
            $stmt->close();
            header("Location: add-amenities.php"); // reload same page
            exit();
        }
    } else {
        $_SESSION['warning_message'] = "Please provide an amenity name.";
        header("Location: add-amenities.php"); // reload same page
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amenity Form</title>
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
            margin-left: 250px;
            /* Adjust based on your sidebar width */
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
            max-width: 1334px;
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

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
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
            <h1 class="page-title">Amenity Management</h1>
            <div class="breadcrumb">Dashboard / Facilities / Add Amenity</div>
        </div> -->

        <div class="form-container">
            <div class="form-header">
                <h2 class="form-title"><i class="bi bi-stars"></i>Add Amenity</h2>
            </div>

            <div class="form-body">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $success_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($warning_message)): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <?= $warning_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="add_amenity" value="1">

                    <div class="form-group">
                        <label class="form-label">Amenity Name</label>
                        <input type="text" class="form-control" name="amenity_name" placeholder="e.g. Free WiFi, TV, AC"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="4" name="description"
                            placeholder="Amenity description"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Icon URL</label>
                        <input type="text" class="form-control" name="icon_url"
                            placeholder="e.g. https://example.com/icon.png">
                        <small style="display: block; color: #6c757d; margin-top: 5px;">Optional: Provide a URL for an
                            icon representing this amenity</small>
                    </div>

                    <div class="form-footer">
                        <button type="button" class="btn btn-outline-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Amenity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cancelBtn = document.querySelector('.btn-outline-secondary');

            cancelBtn.addEventListener('click', function () {
                document.querySelector('form').reset();
            });
        });
    </script>
</body>

</html>