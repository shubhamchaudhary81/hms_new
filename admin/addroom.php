<?php
include_once '../config/configdatabse.php';

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
    // Handle file upload
    $targetDir = "../uploads/room_images/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $targetFile = $targetDir . basename($_FILES["room_image"]["name"]);
    move_uploaded_file($_FILES["room_image"]["tmp_name"], $targetFile);

    // Collect form data
    $room_number = $_POST['room_number'];
    $room_name = $_POST['room_name']; // new field
    $room_type = (int)$_POST['room_type_id'];
    $price_per_night = (float)$_POST['price_per_night'];
    $status = $_POST['status'];
    $floor_number = (int)$_POST['floor_number'];
    $description = $_POST['description'];
    $weekend_price = (float)$_POST['weekend_price'];
    $season_price = (float)$_POST['season_price'];
    $capacity = (int)$_POST['capacity'];
    $image = $targetFile;

    // Updated SQL with room_name after room_number
    $sql = "INSERT INTO room (
                room_number, 
                room_name, 
                room_type, 
                price_per_night, 
                status, 
                floor_number, 
                description, 
                weekend_price, 
                season_price, 
                capacity, 
                image
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Binding parameters: s = string, i = integer, d = double
    $stmt->bind_param(
        "ssisidddiss",
        $room_number,    // s
        $room_name,      // s
        $room_type,      // i
        $price_per_night,// d
        $status,         // s
        $floor_number,   // i
        $description,    // s
        $weekend_price,  // d
        $season_price,   // d
        $capacity,       // i
        $image           // s
    );

    if ($stmt->execute()) {
        $room_id = $conn->insert_id;

        // Insert selected amenities
        // if (isset($_POST['amenities']) && is_array($_POST['amenities'])) {
        //     $amenitySql = "INSERT INTO RoomAmenity (room_id, amenity_id, quantity) VALUES (?, ?, 1)";
        //     $amenityStmt = $conn->prepare($amenitySql);
            
        //     foreach ($_POST['amenities'] as $amenity_id) {
        //         $amenityStmt->bind_param("ii", $room_id, $amenity_id);
        //         $amenityStmt->execute();
        //     }
        //     $amenityStmt->close();
        // }

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
    <title>Himalaya Hotel </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/addroom.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <div class="header-content">
                <h1><i class="fas fa-plus-circle"></i> Add New Room</h1>
                <p>Create a new room with detailed information and amenities</p>
            </div>
            <a href="rooms.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Back to Rooms
            </a>
        </div>

        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-step active" data-step="1">
                    <div class="step-circle">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="step-label">Basic Info</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="2">
                    <div class="step-circle">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="step-label">Pricing</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="3">
                    <div class="step-circle">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="step-label">Amenities</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="4">
                    <div class="step-circle">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <div class="step-label">Description</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="5">
                    <div class="step-circle">
                        <i class="fas fa-camera"></i>
                    </div>
                    <div class="step-label">Images</div>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <form id="roomForm" method="POST" enctype="multipart/form-data">
        
                
                <!-- Stage 1: Basic Information -->
                <div class="form-stage active" id="stage-1">
                    <div class="stage-header">
                        <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                        <p>Enter the fundamental details about the room</p>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-door-open"></i>
                                Room Number
                            </label>
                            <input type="number" class="form-input" name="room_number" placeholder="Enter room number" required>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-bed"></i>
                                Room Type
                            </label>
                             <select name="room_type_id" class="form-input" required>
                                    <option value="">Select Room Type</option>
                                    <?php foreach ($roomTypes as $type): ?>
                                        <option value="<?= $type['room_type_id'] ?>"><?= htmlspecialchars($type['room_type_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-building"></i>
                                Floor Number
                            </label>
                            <select class="form-input" name="floor_number" required>
                                <option value="">Select floor</option>
                                <option value="0">Ground Floor</option>
                                <option value="1">1st Floor</option>
                                <option value="2">2nd Floor</option>
                                <option value="3">3rd Floor</option>
                                <option value="4">4th Floor</option>
                            </select>
                            <div class="error-message"></div>
                        </div>

                       <div class="form-group">
    <label class="form-label">
        <i class="fas fa-tag"></i>
        Room Name
    </label>
    <input type="text" class="form-input" name="room_name" placeholder="Enter room name" required>
    <div class="error-message"></div>
</div>

<!-- Hidden Room Status (Always Available) -->
<input type="hidden" name="status" value="Available">

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-users"></i>
                                Maximum Capacity
                            </label>
                            <input type="number" class="form-input" name="capacity" placeholder="Enter max guests" min="1" max="10" required>
                            <div class="error-message"></div>
                        </div>
                    </div>
                </div>

                <!-- Stage 2: Pricing Information -->
                <div class="form-stage" id="stage-2">
                    <div class="stage-header">
                        <h2><i class="fas fa-dollar-sign"></i> Pricing Information</h2>
                        <p>Set up pricing structure for different periods</p>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-money-bill"></i>
                                Base Price (per night)
                            </label>
                            <div class="input-group">
                                <span class="input-prefix">$</span>
                                <input type="number" class="form-input" name="price_per_night" placeholder="Enter base price" step="0.01" required>
                            </div>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-weekend"></i>
                                Weekend Rate
                            </label>
                            <div class="input-group">
                                <span class="input-prefix">$</span>
                                <input type="number" class="form-input" name="weekend_price" placeholder="Enter weekend price" step="0.01">
                            </div>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-sun"></i>
                                Seasonal Rate
                            </label>
                            <div class="input-group">
                                <span class="input-prefix">$</span>
                                <input type="number" class="form-input" name="season_price" placeholder="Enter seasonal price" step="0.01">
                            </div>
                            <div class="error-message"></div>
                        </div>
                    </div>

                    <div class="pricing-preview">
                        <h3>Pricing Preview</h3>
                        <div class="price-cards">
                            <div class="price-card">
                                <div class="price-type">Regular</div>
                                <div class="price-amount" id="regular-preview">$0</div>
                            </div>
                            <div class="price-card">
                                <div class="price-type">Weekend</div>
                                <div class="price-amount" id="weekend-preview">$0</div>
                            </div>
                            <div class="price-card">
                                <div class="price-type">Seasonal</div>
                                <div class="price-amount" id="seasonal-preview">$0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stage 3: Amenities -->
                <div class="form-stage" id="stage-3">
                    <div class="stage-header">
                        <h2><i class="fas fa-star"></i> Room Amenities</h2>
                        <p>Select all amenities available in this room</p>
                    </div>
                    
                    <div class="amenities-grid">
                        <div class="amenity-category">
                            <h3>Basic Amenities</h3>
                            <div class="amenity-list">
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="1">
                                    <div class="amenity-card">
                                        <i class="fas fa-wifi"></i>
                                        <span>Free WiFi</span>
                                    </div>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="2">
                                    <div class="amenity-card">
                                        <i class="fas fa-tv"></i>
                                        <span>Smart TV</span>
                                    </div>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="3">
                                    <div class="amenity-card">
                                        <i class="fas fa-snowflake"></i>
                                        <span>Air Conditioning</span>
                                    </div>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="4">
                                    <div class="amenity-card">
                                        <i class="fas fa-phone"></i>
                                        <span>Telephone</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="amenity-category">
                            <h3>Bathroom</h3>
                            <div class="amenity-list">
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="5">
                                    <div class="amenity-card">
                                        <i class="fas fa-bath"></i>
                                        <span>Bathtub</span>
                                    </div>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="6">
                                    <div class="amenity-card">
                                        <i class="fas fa-shower"></i>
                                        <span>Shower</span>
                                    </div>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="7">
                                    <div class="amenity-card">
                                        <i class="fas fa-wind"></i>
                                        <span>Hair Dryer</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="amenity-category">
                            <h3>Comfort</h3>
                            <div class="amenity-list">
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="8">
                                    <div class="amenity-card">
                                        <i class="fas fa-coffee"></i>
                                        <span>Coffee Maker</span>
                                    </div>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="9">
                                    <div class="amenity-card">
                                        <i class="fas fa-cube"></i>
                                        <span>Mini Bar</span>
                                    </div>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="10">
                                    <div class="amenity-card">
                                        <i class="fas fa-lock"></i>
                                        <span>Safe</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="selected-amenities">
                        <h3>Selected Amenities: <span id="amenity-count">0</span></h3>
                        <div id="selected-list"></div>
                    </div>
                </div>
                <!-- <div class="mb-5">
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
                </div> -->

                <!-- Stage 4: Description -->
                <div class="form-stage" id="stage-4">
                    <div class="stage-header">
                        <h2><i class="fas fa-align-left"></i> Room Description</h2>
                        <p>Provide detailed information about the room</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-edit"></i>
                            Detailed Description
                        </label>
                        <textarea class="form-textarea" name="description" rows="5" placeholder="Enter a detailed description of the room, including features, view, and any special characteristics..."></textarea>
                        <div class="character-count">
                            <span id="char-count">0</span> / 500 characters
                        </div>
                    </div>

                    <div class="description-tips">
                        <h3>Writing Tips</h3>
                        <ul>
                            <li>Mention the room's view (city, ocean, garden, etc.)</li>
                            <li>Describe the bed type and size</li>
                            <li>Highlight unique features or decor</li>
                            <li>Include information about natural light</li>
                            <li>Mention any special services included</li>
                        </ul>
                    </div>
                </div>

                <!-- Stage 5: Images -->
                <div class="form-stage" id="stage-5">
                    <div class="stage-header">
                        <h2><i class="fas fa-camera"></i> Room Images</h2>
                        <p>Upload high-quality photos of the room</p>
                    </div>
                    
                    <div class="image-upload-area">
                        <div class="upload-zone" id="uploadZone">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h3>Drag & Drop Images Here</h3>
                            <p>or click to browse files</p>
                            <input type="file" id="imageInput" name="room_images[]" multiple accept="image/*" hidden>
                        </div>
                        
                        <div class="upload-guidelines">
                            <h4>Image Guidelines</h4>
                            <ul>
                                <li>Maximum 5 images allowed</li>
                                <li>Supported formats: JPG, PNG, WebP</li>
                                <li>Maximum file size: 5MB per image</li>
                                <li>Recommended resolution: 1920x1080 or higher</li>
                            </ul>
                        </div>
                    </div>

                    <div class="image-preview-container" id="imagePreview">
                        <!-- Uploaded images will appear here -->
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="form-navigation">
                    <button type="button" class="btn btn-outline" id="prevBtn" style="display: none;">
                        <i class="fas fa-arrow-left"></i>
                        Previous
                    </button>
                    
                    <div class="nav-right">
                        <button type="button" class="btn btn-outline" id="saveAsDraft">
                            <i class="fas fa-save"></i>
                            Save as Draft
                        </button>
                        <button type="button" class="btn btn-primary" id="nextBtn">
                            Next
                            <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                            <i class="fas fa-check"></i>
                            Create Room
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Success Modal -->
    <div class="modal" id="successModal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-check-circle"></i>
                <h2>Room Created Successfully!</h2>
            </div>
            <div class="modal-body">
                <p>The room has been added to your hotel inventory.</p>
                <div class="room-summary" id="roomSummary">
                    <!-- Room summary will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="createAnother()">Create Another Room</button>
                <button class="btn btn-primary" onclick="viewRooms()">View All Rooms</button>
            </div>
        </div>
    </div>

    <script src="../js/admin/addroom.js"></script>
</body>
</html>
