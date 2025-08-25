<?php
// Database connection
include_once '../config/configdatabse.php';
// include '../config/configdatabase.php';

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
    // ... (file upload code here)
    $targetDir = "../uploads/room_images/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}
$targetFile = $targetDir . basename($_FILES["room_image"]["name"]);
move_uploaded_file($_FILES["room_image"]["tmp_name"], $targetFile);

    $room_number = $_POST['room_number'];
    $room_type = (int)$_POST['room_type_id'];
    $price_per_night = (float)$_POST['price_per_night'];
    $status = $_POST['status'];
    $floor_number = (int)$_POST['floor_number'];
    $description = $_POST['description'];
    $weekend_price = (float)$_POST['weekend_price'];
    $season_price = (float)$_POST['season_price'];
    $capacity = (int)$_POST['capacity'];
    $image = $targetFile;

    $sql = "INSERT INTO room (room_number, room_type, price_per_night, status, floor_number, description, weekend_price, season_price, capacity, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
        // Get the room_id of the newly inserted room
        $room_id = $conn->insert_id;
        
        // Insert selected amenities into RoomAmenity table
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
    <title>Add New Room</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/addroom.css">
    <link rel="stylesheet" href="adminsidebar.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
    <!-- Main Content -->
    <div class="main-container">
        <div class="content-container">
            <div class="page-header">
                <h1 class="page-title"><i class="bi bi-door-closed"></i>Add New Room</h1>
                <a href="rooms.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Rooms
                </a>
            </div>

            <!-- Success and Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method = "POST" enctype="multipart/form-data">
                    <!-- Basic Information Section -->
                    <div class="mb-5">
                        <h3 class="form-section-title"><i class="bi bi-info-circle"></i>Basic Information</h3>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Room Number</label>
                                <input type="number" class="form-control" name= "room_number" placeholder="Enter room number" required>
                            </div>
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
                                <select class="form-select" name ="floor_number" required>
                                    <option value="" disabled selected>Select floor</option>
                                    <option>Ground Floor</option>
                                    <option>1st Floor</option>
                                    <option>2nd Floor</option>
                                    <option>3rd Floor</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="" disabled selected>Select status</option>
                                    <option>Available</option>
                                    <option>Occupied</option>
                                    <option>Maintenance</option>
                                    <option>Reserved</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Pricing Section -->
                    <div class="mb-5">
                        <h3 class="form-section-title"><i class=""></i><span style="color: #f8c537;">रु</span> &nbsp Pricing Information</h3>
                        <div class="row g-3">
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
                    </div>

                    <div class="section-divider"></div>

                    <!-- Amenities Section -->
                    <div class="mb-5">
                        <h3 class="form-section-title"><i class="bi bi-stars"></i>Amenities</h3>
                        <div class="row">
                            <?php 
                            $amenityCount = count($amenities);
                            $halfCount = ceil($amenityCount / 2);
                            ?>
                            <div class="col-md-6">
                                <?php for ($i = 0; $i < $halfCount; $i++): ?>
                                    <div class="form-check mb-3">
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
                                    <div class="form-check mb-3">
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
                    </div>

                    <div class="section-divider"></div>

                    <!-- Description Section -->
                    <div class="mb-4">
                        <h3 class="form-section-title"><i class="bi bi-text-paragraph"></i>Description</h3>
                        <div class="mb-3">
                            <label class="form-label">Room Description</label>
                            <textarea class="form-control" rows="4" name="description" placeholder="Enter detailed room description"></textarea>
                        </div>
                        <!-- <div class="mb-3">
                            <label class="form-label">Special Notes</label>
                            <textarea class="form-control" rows="2" placeholder="Any special notes about this room"></textarea>
                        </div> -->
                    </div>

                    <div class="section-divider"></div>

                    <!-- Image Upload Section -->
                    <div class="mb-5">
                        <h3 class="form-section-title"><i class="bi bi-image"></i>Room Images</h3>
                        <div class="mb-3">
                            <label class="form-label">Upload Room Photos</label>
                            <input class="form-control" type="file" id="formFileMultiple" name="room_image" multiple>
                        </div>
                        <small class="text-muted">Upload high-quality images of the room (max 5 photos)</small>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end mt-4">
                        <button type="reset" class="btn btn-outline-secondary me-3">
                            <i class="bi bi-x-circle me-2"></i>Reset Form
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on smaller screens
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content-container');
            
            function toggleSidebar() {
                if (window.innerWidth <= 992) {
                    sidebar.classList.toggle('active');
                    content.classList.toggle('active');
                }
            }
            
            // You can add a button to toggle the sidebar if needed
            // document.getElementById('sidebarToggle').addEventListener('click', toggleSidebar);
        });
    </script>
</body>
</html>