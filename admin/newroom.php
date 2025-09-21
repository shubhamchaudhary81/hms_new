<?php
// Database connection
include_once '../config/configdatabse.php';
// include '../config/configdatabase.php';
$headerTitle = "Add New Room";
$headerSubtitle = "Create a new room in the system";
$buttonText = "Back to Rooms";
$buttonLink = "rooms.php";
$showButton = true;

// Fetch room types for the dropdown
$roomTypes = [];
$result = $conn->query("SELECT room_type_id, room_type_name FROM roomtype");
while ($row = $result->fetch_assoc()) {
    $roomTypes[] = $row;
}

// Fetch amenities for the checkboxes
$amenities = [];
$amenityResult = $conn->query("SELECT amenity_id, amenity_name FROM Amenity");
while ($row = $amenityResult->fetch_assoc()) {
    $amenities[] = $row;
}

// Insert new room into the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // File upload
    $targetDir = "../uploads/room_images/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $targetFile = $targetDir . basename($_FILES["room_image"]["name"]);
    move_uploaded_file($_FILES["room_image"]["tmp_name"], $targetFile);

    // Fields
    $room_number = $_POST['room_number'];
    // $room_name = $_POST['room_name'];
    $room_type = (int)$_POST['room_type_id'];
    $price_per_night = (float)$_POST['price_per_night'];
    $status = "Available"; // default
    $floor_number = (int)$_POST['floor_number'];
    $description = $_POST['description'];
    $weekend_price = (float)$_POST['weekend_price'];
    $season_price = (float)$_POST['season_price'];
    $capacity = (int)$_POST['capacity'];
    $image = $targetFile;

    $sql = "INSERT INTO room 
        (room_number, room_type, price_per_night, status, floor_number, description, weekend_price, season_price, capacity, image)
        VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "sidsisdids",
        $room_number,
        $room_type,
        $price_per_night,
        $status,
        $floor_number,
        $description,
        $weekend_price,
        $season_price,
        $capacity,
        $image
    );

    if ($stmt->execute()) {
        $room_id = $conn->insert_id;

        // Save amenities
        if (isset($_POST['amenities']) && is_array($_POST['amenities'])) {
            $amenitySql = "INSERT INTO RoomAmenity (room_id, amenity_id, quantity) VALUES (?, ?, 1)";
            $amenityStmt = $conn->prepare($amenitySql);

            foreach ($_POST['amenities'] as $amenity_id) {
                $amenityStmt->bind_param("ii", $room_id, $amenity_id);
                $amenityStmt->execute();
            }
            $amenityStmt->close();
        }

        $success_message = "Room added successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Room</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8b7355;
            --primary-dark: #8b7355;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-color: #e0e0e0;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            /* line-height: 1.6; */
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
            min-height: 100vh;
        }

        .content-container {
            flex: 1;
            padding: 2rem;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        /* Header */
        /* .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            font-size: 1.5rem;
        } */

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--secondary-color);
            color: var(--secondary-color);
        }

        .btn-outline:hover {
            background-color: rgba(108, 117, 125, 0.1);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 2.5rem;
            margin-top: 1.5rem;
        }

        /* Form Elements */
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control, .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background-color: white;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(93, 95, 239, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-right: 0.75rem;
            cursor: pointer;
        }

        .form-check-label {
            cursor: pointer;
        }

        .input-group {
            display: flex;
        }

        .input-group-text {
            padding: 0.75rem 1rem;
            background-color: #f1f3f5;
            border: 1px solid var(--border-color);
            border-right: none;
            border-radius: 8px 0 0 8px;
            font-size: 1rem;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2.5rem;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 4px;
            background-color: #e9ecef;
            z-index: 1;
            transform: translateY(-50%);
        }

        .progress-bar {
            position: absolute;
            top: 50%;
            left: 0;
            height: 4px;
            background-color: var(--primary-color);
            z-index: 2;
            transform: translateY(-50%);
            transition: width 0.3s ease;
        }

        .step {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .step-number {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }

        .step.active .step-number {
            background-color: var(--primary-color);
            color: white;
        }

        .step.completed .step-number {
            background-color: var(--success-color);
            color: white;
        }

        .step-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--secondary-color);
        }

        .step.active .step-label,
        .step.completed .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Form Steps */
        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Form Sections */
        .form-section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
        }

        .form-section-title i {
            font-size: 1.25rem;
        }

        /* Form Navigation */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        /* Grid Layout */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 0.75rem;
            margin-bottom: 1.5rem;
        }

        /* Review Section */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--dark-color);
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            background-color: var(--primary-color);
            color: white;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.2);
            color: var(--success-color);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: var(--danger-color);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .content-container {
                margin-left: 0;
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .form-card {
                padding: 1.5rem;
            }

            .step-label {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .form-navigation {
                flex-direction: column-reverse;
                gap: 1rem;
            }

            .btn {
                width: 100%;
            }
        }

        /* Validation */
        .is-invalid {
            border-color: var(--danger-color) !important;
        }

        /* Sidebar collapsed state */
        .sidebar.collapsed ~ .main-container .content-container {
            margin-left: 80px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-container">
        
        <div class="content-container">
            <!-- <div class="page-header">
                <h1 class="page-title"><i class="fas fa-door-closed"></i>Add New Room</h1>
                <a href="rooms.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>Back to Rooms
                </a>
            </div> -->
            <?php include 'header-content.php'?>

            <!-- Success and Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i><?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i><?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <!-- Progress Steps -->
                <div class="progress-steps">
                    <div class="progress-bar" style="width: 0%;"></div>
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Basic Info</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Pricing</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Amenities</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Details</div>
                    </div>
                    <div class="step" data-step="5">
                        <div class="step-number">5</div>
                        <div class="step-label">Review</div>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data" id="roomForm">
                    <!-- Step 1: Basic Information -->
                    <div class="form-step active" id="step-1">
                        <h3 class="form-section-title"><i class="fas fa-info-circle"></i>Basic Information</h3>
                        <div class="row">
    <div class="col-md-6">
        <label class="form-label">Room Number</label>
        <input type="number" class="form-control" name="room_number" placeholder="Enter room number" required>
    </div>
    <!-- <div class="col-md-6">
        <label class="form-label">Room Name</label>
        <input type="text" class="form-control" name="room_name" placeholder="Enter room name" required>
    </div> -->
    <div class="col-md-6">
        <label class="form-label">Room Type</label>
        <select name="room_type_id" class="form-control" required>
            <option value="">Select Room Type</option>
            <?php foreach ($roomTypes as $type): ?>
                <option value="<?= $type['room_type_id'] ?>"><?= htmlspecialchars($type['room_type_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Floor</label>
        <select class="form-select" name="floor_number" required>
            <option value="" disabled selected>Select floor</option>
            <option>Ground Floor</option>
            <option>1st Floor</option>
            <option>2nd Floor</option>
            <option>3rd Floor</option>
        </select>
    </div>
</div>


                        <div class="form-navigation">
                            <button type="button" class="btn btn-outline" disabled>
                                <i class="fas fa-arrow-left"></i>Previous
                            </button>
                            <button type="button" class="btn btn-primary next-step">
                                Next<i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Pricing Information -->
                    <div class="form-step" id="step-2">
                        <h3 class="form-section-title"><i class="fas fa-money-bill-wave"></i>Pricing Information</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Base Price (per night)</label>
                                <div class="input-group">
                                    <span class="input-group-text">रु</span>
                                    <input type="number" class="form-control" name="price_per_night" placeholder="Enter base price" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Weekend Rate</label>
                                <div class="input-group">
                                    <span class="input-group-text">रु</span>
                                    <input type="number" class="form-control" name="weekend_price" placeholder="Enter weekend price">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Seasonal Rate</label>
                                <div class="input-group">
                                    <span class="input-group-text">रु</span>
                                    <input type="number" class="form-control" name="season_price" placeholder="Enter seasonal price">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max Occupancy/Capacity</label>
                                <input type="number" class="form-control" name="capacity" placeholder="Enter max guests" required>
                            </div>
                        </div>

                        <div class="form-navigation">
                            <button type="button" class="btn btn-outline prev-step">
                                <i class="fas fa-arrow-left"></i>Previous
                            </button>
                            <button type="button" class="btn btn-primary next-step">
                                Next<i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Amenities -->
                    <div class="form-step" id="step-3">
                        <h3 class="form-section-title"><i class="fas fa-star"></i>Amenities</h3>
                        <div class="row">
                            <?php
                            $amenityCount = count($amenities);
                            $halfCount = ceil($amenityCount / 2);
                            ?>
                            <div class="col-md-6">
                                <?php for ($i = 0; $i < $halfCount; $i++): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]"
                                               id="amenity_<?= $amenities[$i]['amenity_id'] ?>"
                                               value="<?= $amenities[$i]['amenity_id'] ?>">
                                        <label class="form-check-label" for="amenity_<?= $amenities[$i]['amenity_id'] ?>">
                                            <?= htmlspecialchars($amenities[$i]['amenity_name']) ?>
                                        </label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <div class="col-md-6">
                                <?php for ($i = $halfCount; $i < $amenityCount; $i++): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]"
                                               id="amenity_<?= $amenities[$i]['amenity_id'] ?>"
                                               value="<?= $amenities[$i]['amenity_id'] ?>">
                                        <label class="form-check-label" for="amenity_<?= $amenities[$i]['amenity_id'] ?>">
                                            <?= htmlspecialchars($amenities[$i]['amenity_name']) ?>
                                        </label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="form-navigation">
                            <button type="button" class="btn btn-outline prev-step">
                                <i class="fas fa-arrow-left"></i>Previous
                            </button>
                            <button type="button" class="btn btn-primary next-step">
                                Next<i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 4: Details -->
                    <div class="form-step" id="step-4">
                        <h3 class="form-section-title"><i class="fas fa-align-left"></i>Description</h3>
                        <div style="margin-bottom: 1.5rem;">
                            <label class="form-label">Room Description</label>
                            <textarea class="form-control" rows="4" name="description" placeholder="Enter detailed room description"></textarea>
                        </div>

                        <h3 class="form-section-title" style="margin-top: 2rem;"><i class="fas fa-image"></i>Room Images</h3>
                        <div style="margin-bottom: 1.5rem;">
                            <label class="form-label">Upload Room Photos</label>
                            <input class="form-control" type="file" id="formFileMultiple" name="room_image" multiple>
                        </div>
                        <small style="color: #6c757d;">Upload high-quality images of the room (max 5 photos)</small>

                        <div class="form-navigation">
                            <button type="button" class="btn btn-outline prev-step">
                                <i class="fas fa-arrow-left"></i>Previous
                            </button>
                            <button type="button" class="btn btn-primary next-step">
                                Next<i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 5: Review and Submit -->
                    <div class="form-step" id="step-5">
                        <h3 class="form-section-title"><i class="fas fa-check-circle"></i>Review Information</h3>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Room Details</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Room Number:</strong> <span id="review-room-number"></span></p>
                                        <!-- <p><strong>Room Name:</strong> <span id="review-room-name"></span></p> -->
                                        <p><strong>Room Type:</strong> <span id="review-room-type"></span></p>
                                        <p><strong>Floor:</strong> <span id="review-floor"></span></p>
                                        <p><strong>Status:</strong> Available</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Base Price:</strong> रु <span id="review-price"></span></p>
                                        <p><strong>Weekend Price:</strong> रु <span id="review-weekend-price"></span></p>
                                        <p><strong>Seasonal Price:</strong> रु <span id="review-season-price"></span></p>
                                        <p><strong>Capacity:</strong> <span id="review-capacity"></span></p>
                                    </div>
                                </div>
                                <hr style="margin: 1.5rem 0; border-color: var(--border-color);">
                                <p><strong>Description:</strong></p>
                                <p id="review-description"></p>
                                <hr style="margin: 1.5rem 0; border-color: var(--border-color);">
                                <p><strong>Amenities:</strong></p>
                                <div id="review-amenities" style="display: flex; flex-wrap: wrap; gap: 0.5rem;"></div>
                            </div>
                        </div>

                        <div class="form-navigation">
                            <button type="button" class="btn btn-outline prev-step">
                                <i class="fas fa-arrow-left"></i>Previous
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i>Save Room
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing newroom.php');
            
            const steps = document.querySelectorAll('.step');
            const formSteps = document.querySelectorAll('.form-step');
            const progressBar = document.querySelector('.progress-bar');
            
            console.log('Found elements:', { steps: steps.length, formSteps: formSteps.length, progressBar: !!progressBar });
            
            let currentStep = 1;

            // Handle sidebar toggle state
            function updateContentMargin() {
                const sidebar = document.querySelector('.sidebar');
                const contentContainer = document.querySelector('.content-container');
                
                console.log('Updating content margin:', { sidebar: !!sidebar, contentContainer: !!contentContainer });
                
                if (sidebar && contentContainer) {
                    if (sidebar.classList.contains('collapsed')) {
                        contentContainer.style.marginLeft = '80px';
                        console.log('Sidebar collapsed, margin set to 80px');
                    } else {
                        contentContainer.style.marginLeft = '280px';
                        console.log('Sidebar expanded, margin set to 280px');
                    }
                }
            }

            // Listen for sidebar toggle events
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                const observer = new MutationObserver(updateContentMargin);
                observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
                
                // Initial check
                updateContentMargin();
            }

            // Navigation between steps
            document.querySelectorAll('.next-step').forEach(button => {
                button.addEventListener('click', () => {
                    // Validate current step before proceeding
                    if (validateStep(currentStep)) {
                        goToStep(currentStep + 1);
                    }
                });
            });

            document.querySelectorAll('.prev-step').forEach(button => {
                button.addEventListener('click', () => {
                    goToStep(currentStep - 1);
                });
            });

            function goToStep(step) {
                if (step < 1 || step > 5) return;

                // Update current step
                currentStep = step;

                // Update progress bar
                const progressPercentage = ((step - 1) / (steps.length - 1)) * 100;
                progressBar.style.width = `${progressPercentage}%`;

                // Update step indicators
                steps.forEach((stepElement, index) => {
                    if (index + 1 < step) {
                        stepElement.classList.add('completed');
                        stepElement.classList.remove('active');
                    } else if (index + 1 === step) {
                        stepElement.classList.add('active');
                        stepElement.classList.remove('completed');
                    } else {
                        stepElement.classList.remove('active', 'completed');
                    }
                });

                // Show/hide form steps
                formSteps.forEach(formStep => {
                    formStep.classList.remove('active');
                });
                document.getElementById(`step-${step}`).classList.add('active');

                // Update review section if we're on the last step
                if (step === 5) {
                    updateReviewSection();
                }
            }

            function validateStep(step) {
                let isValid = true;
                
                if (step === 1) {
                    const requiredFields = [
                        document.querySelector('[name="room_number"]'),
                        // document.querySelector('[name="room_name"]'),
                        document.querySelector('[name="room_type_id"]'),
                        document.querySelector('[name="floor_number"]'),
                        // document.querySelector('[name="status"]')
                    ];
                    
                    requiredFields.forEach(field => {
                        if (!field.value) {
                            field.classList.add('is-invalid');
                            isValid = false;
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                } else if (step === 2) {
                    const requiredFields = [
                        document.querySelector('[name="price_per_night"]'),
                        document.querySelector('[name="capacity"]')
                    ];
                    
                    requiredFields.forEach(field => {
                        if (!field.value) {
                            field.classList.add('is-invalid');
                            isValid = false;
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                }
                
                return isValid;
            }

            function updateReviewSection() {
                // Basic info
                document.getElementById('review-room-number').textContent = document.querySelector('[name="room_number"]').value;
                // document.getElementById('review-room-name').textContent = document.querySelector('[name="room_name"]').value;
                document.getElementById('review-room-type').textContent = document.querySelector('[name="room_type_id"]').options[document.querySelector('[name="room_type_id"]').selectedIndex].text;
                document.getElementById('review-floor').textContent = document.querySelector('[name="floor_number"]').value;
                // document.getElementById('review-status').textContent = document.querySelector('[name="status"]').value;
                
                // Pricing
                document.getElementById('review-price').textContent = document.querySelector('[name="price_per_night"]').value;
                document.getElementById('review-weekend-price').textContent = document.querySelector('[name="weekend_price"]').value || 'N/A';
                document.getElementById('review-season-price').textContent = document.querySelector('[name="season_price"]').value || 'N/A';
                document.getElementById('review-capacity').textContent = document.querySelector('[name="capacity"]').value;
                
                // Description
                document.getElementById('review-description').textContent = document.querySelector('[name="description"]').value || 'No description provided';
                
                // Amenities
                const amenitiesContainer = document.getElementById('review-amenities');
                amenitiesContainer.innerHTML = '';
                
                const checkedAmenities = document.querySelectorAll('[name="amenities[]"]:checked');
                if (checkedAmenities.length > 0) {
                    checkedAmenities.forEach(amenity => {
                        const label = document.querySelector(`label[for="${amenity.id}"]`).textContent;
                        const badge = document.createElement('span');
                        badge.className = 'badge';
                        badge.textContent = label;
                        amenitiesContainer.appendChild(badge);
                    });
                } else {
                    amenitiesContainer.innerHTML = '<span style="color: #6c757d;">No amenities selected</span>';
                }
            }
        });
    </script>
</body>
</html>