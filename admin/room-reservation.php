<?php
session_start();
if ($_SESSION['admin_id'] == "" || $_SESSION['admin_name'] == "") {
    header("Location: ../login.php");
    exit();
}
// Database connection
include_once '../config/configdatabse.php';

$headerTitle = "New Reservation";
$headerSubtitle = "Manage all hotel bookings and reservations";
// $buttonText = "New Reservation";
// $buttonLink = "addroom.php";
// $showButton = true;

// Fetch room types from DB
$roomTypes = [];
$sql = "SELECT room_type_id, room_type_name, base_price FROM RoomType";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $roomTypes[] = $row;
}

// // If user already selected a room type (from form submission), use it
// $selectedTypeId = isset($_POST['room_type_id']) ? intval($_POST['room_type_id']) :
//     (!empty($roomTypes) ? $roomTypes[0]['room_type_id'] : 0);

// // Fetch Available Rooms for the Selected Type
// $availableRooms = [];
// if ($selectedTypeId) {
//     $stmt = $conn->prepare("SELECT room_id, room_number 
//                             FROM Room 
//                             WHERE room_type = ? AND status = 'available'
//                             ORDER BY room_number ASC");
//     $stmt->bind_param("i", $selectedTypeId);
//     $stmt->execute();
//     $resultRooms = $stmt->get_result();

//     while ($room = $resultRooms->fetch_assoc()) {
//         $availableRooms[] = $room;
//     }
// }


// 2) Determine selected room type
// Prefer POST, then GET, then fallback to first room type (if any)
// $selectedTypeId = 0;
// if (isset($_POST['room_type_id']) && $_POST['room_type_id'] !== '') {
//     $selectedTypeId = intval($_POST['room_type_id']);
// } elseif (isset($_GET['room_type_id']) && $_GET['room_type_id'] !== '') {
//     $selectedTypeId = intval($_GET['room_type_id']);
// } elseif (!empty($roomTypes)) {
//     $selectedTypeId = intval($roomTypes[0]['room_type_id']);
// }

// // (Optional) preserve selected room id after submit
// $selectedRoomId = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;

// // 3) Fetch available rooms for the selected type
// $availableRooms = [];
// if ($selectedTypeId) {
//     $stmt = $conn->prepare("SELECT room_id, room_number FROM Room WHERE room_type = ? AND status = 'available' ORDER BY room_number ASC");
//     if ($stmt) {
//         $stmt->bind_param("i", $selectedTypeId);
//         $stmt->execute();
//         $res = $stmt->get_result();
//         while ($r = $res->fetch_assoc()) {
//             $availableRooms[] = $r;
//         }
//         $stmt->close();
//     } else {
//         // For debugging - remove in production
//         die("Prepare failed: " . $conn->error);
//     }
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Staff Reservation System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin/content.css">
    <style>
        :root {
            --primary: #8b7355;
            --primary-light: #a58c6c;
            --primary-dark: #76614a;
            --accent: #5a4c3d;
            --light: #f9f6f2;
            --light-alt: #e8dfd1;
            --dark: #333;
            --gray: #666;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }

        /* ✅ Adjusted for sidebar */
        .main-content {
            margin-left: 260px;
            /* match sidebar width */
            padding: 20px 30px;
            max-width: calc(100% - 260px);
        }

        header {
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            padding: 20px 0;
            text-align: center;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: var(--primary);
            margin: 25px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-alt);
        }


        h3 {
            color: var(--accent);
            margin-bottom: 15px;
        }

        .staff-info {
            background-color: var(--light-alt);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .reservation-process {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
            align-items: center;
            gap: 10px;
        }

        .process-step {
            text-align: center;
            flex: 1;
            position: relative;
            z-index: 3;
            cursor: pointer;
        }

        .process-step .circle {
            width: 50px;
            height: 50px;
            background-color: var(--light-alt);
            border: 3px solid var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-weight: bold;
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .process-step.active .circle {
            background-color: var(--primary);
            color: white;
        }

        .process-step p {
            font-weight: 500;
            color: var(--primary);
        }

        .process-connector {
            position: absolute;
            top: 34px;
            left: 5%;
            right: 5%;
            height: 6px;
            background-color: var(--light-alt);
            z-index: 1;
            border-radius: 4px;
            overflow: hidden;
        }

        .connector-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            transition: width 0.35s ease;
        }

        .form-section {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--accent);
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--light-alt);
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: none;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        /* Ensure all select elements match input styles */
        select.form-select {
            appearance: none;
            /* remove default arrow styling */
            -webkit-appearance: none;
            -moz-appearance: none;
            padding: 12px 15px;
            border: 2px solid var(--light-alt);
            border-radius: 6px;
            font-size: 16px;
            background-color: white;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D'10'%20height%3D'5'%20viewBox%3D'0%200%2010%205'%20xmlns%3D'http://www.w3.org/2000/svg'%3E%3Cpath%20d%3D'M0%200l5%205%205-5z'%20fill%3D'%23666'%20/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 10px 5px;
            transition: border-color 0.3s;
            cursor: pointer;
        }

        select.form-select:focus {
            border-color: var(--primary);
            outline: none;
        }

        /* Optional: adjust invalid-feedback for selects */
        select.form-select:invalid {
            border-color: #e8dfd1;
        }

        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 14px 28px;
            font-size: 18px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: 600;
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        .btn-container {
            text-align: center;
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .facilities-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }

        .facility-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
            cursor: pointer;
            position: relative;
        }

        .facility-card:hover {
            transform: translateY(-5px);
        }

        .facility-card.selected {
            border: 3px solid var(--primary);
        }

        .facility-card.selected::after {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .facility-img {
            height: 180px;
            background-color: var(--light-alt);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 2.5rem;
        }

        .facility-content {
            padding: 20px;
        }

        .facility-title {
            font-size: 1.4rem;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .facility-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--accent);
            margin: 10px 0;
        }

        .facility-desc {
            color: var(--gray);
            margin-bottom: 15px;
        }

        .guest-summary {
            background-color: var(--light-alt);
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
        }

        .guest-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--accent);
        }

        .selected-facilities {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .facility-badge {
            background-color: var(--primary);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        footer {
            text-align: center;
            padding: 30px 0;
            margin-top: 50px;
            background-color: var(--light-alt);
            color: var(--accent);
            border-radius: 10px 10px 0 0;
        }

        /* small utility */
        .small {
            font-size: 0.9rem;
            color: var(--gray);
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .reservation-process {
                flex-direction: column;
                gap: 30px;
            }

            .process-connector {
                display: none;
            }

            .btn-container {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content"><!-- ✅ wrapped all content here -->
        <?php include 'header-content.php'; ?>

        <!-- <div class="staff-info">
            <div>
                <strong>Staff ID:</strong> SH1258 | <strong>Name:</strong> Emily Johnson
            </div>
            <div>
                <button class="btn btn-outline">Save Progress</button>
            </div>
        </div> -->

        <div class="reservation-process">
            <div class="process-connector">
                <div class="connector-fill" id="connector-fill"></div>
            </div>
            <div class="process-step active" data-step="1">
                <div class="circle">1</div>
                <p>Guest Details</p>
            </div>
            <div class="process-step" data-step="2">
                <div class="circle">2</div>
                <p>Select Facilities</p>
            </div>
            <div class="process-step" data-step="3">
                <div class="circle">3</div>
                <p>Confirmation</p>
            </div>
        </div>

        <!-- STEP 1: Guest Information -->
        <!-- STEP 1: Guest Information -->
        <div class="form-section step-content" data-step="1">
            <h2>Guest Information</h2>
            <form id="reservation-form" method="post">
                <!-- Row 1: Names + Email -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="first-name">First Name *</label>
                        <input type="text" id="first-name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last-name">Last Name *</label>
                        <input type="text" id="last-name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>

                <!-- Row 2: Phone + DOB + Gender -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="number" required>
                    </div>
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender">
                            <option value="">Select Gender</option>
                            <option value="Male" name="gender">Male</option>
                            <option value="Female" name="gender">Female</option>
                            <option value="Other" name="gender">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Row 3: Province + District + City -->
                <!-- <div class="form-row">
                    <div class="form-group">
                        <label for="province">Province</label>
                        <input type="text" id="province">
                    </div>
                    <div class="form-group">
                        <label for="district">District</label>
                        <input type="text" id="district">
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city">
                    </div>
                </div> -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="province" class="form-label">Province <span class="text-danger">*</span></label>
                        <select class="form-select" id="province" name="province" required>
                            <option value="">Select Province</option>
                        </select>
                        <div class="invalid-feedback">Please select your province.</div>
                    </div>

                    <div class="form-group">
                        <label for="district" class="form-label">District <span class="text-danger">*</span></label>
                        <select class="form-select" id="district" name="district" required>
                            <option value="">Select District</option>
                        </select>
                        <div class="invalid-feedback">Please select your district.</div>
                    </div>

                    <div class="form-group">
                        <label for="city" class="form-label">City/Municipality <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="city" name="city" required>
                            <option value="">Select City</option>
                        </select>
                        <div class="invalid-feedback">Please select your city/municipality.</div>
                    </div>
                </div>

                <!-- Row 4: Check-in/Check-out + Guests + Room Type -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="check-in">Check-in Date *</label>
                        <input type="date" id="check-in" name="check_in" required>
                    </div>
                    <div class="form-group">
                        <label for="check-out">Check-out Date *</label>
                        <input type="date" id="check-out" name="check_out" required>
                    </div>
                    <div class="form-group">
                        <label for="guests">Number of Guests *</label>
                        <select id="guests" required>
                            <option value="">Select guests</option>
                            <option value="1" name="num_guests">1 Guest</option>
                            <option value="2" name="num_guests">2 Guests</option>
                            <option value="3" name="num_guests">3 Guests</option>
                            <option value="4" name="num_guests">4 Guests</option>
                        </select>
                    </div>
                </div>

                <!-- Row 5: Room Type + Special Requests + (empty for layout) -->
                <div class="form-row">
                    <!-- <div class="form-group">
                        <label for="room-type">Room Type *</label>
                        <select id="room-type" name="room_type_id" required>
                            <option value="">Select Room Type</option>
                            <?php foreach ($roomTypes as $type): ?>
                                <option value="<?= $type['room_type_id'] ?>">
                                    <?= htmlspecialchars($type['room_type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div> -->
                    <!-- Room Type -->
                    <div class="form-group">
                        <label for="room-type">Room Type *</label>
                        <select id="room-type" name="room_type_id" required>
                            <option value="">Select Room Type</option>
                            <?php foreach ($roomTypes as $type): ?>
                                <option value="<?= $type['room_type_id'] ?>">
                                    <?= htmlspecialchars($type['room_type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Room Number -->
                    <div class="form-group">
                        <label for="room-number">Room Number *</label>
                        <!-- <select id="room-number" name="room_id" required>
                            <option value="">Select Room Number</option>
                            <?php if (!empty($availableRooms)): ?>
                                <?php foreach ($availableRooms as $room): ?>
                                    <option value="<?= $room['room_id'] ?>">
                                        <?= htmlspecialchars($room['room_number']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No Rooms Available</option>
                            <?php endif; ?>
                        </select> -->
                        <select id="room-number" name="room_id" required>
                            <option value="">Select Room Number</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="special-requests">Special Requests</label>
                        <textarea id="special-requests" rows="1" name="special_requests"></textarea>
                    </div>
                    <div class="form-group">
                        <!-- Empty for alignment -->
                    </div>
                </div>

                <div class="btn-container">
                    <button type="button" id="clear-form" class="btn btn-outline">Clear Form</button>
                    <button type="submit" id="to-step-2" class="btn">
                        Proceed to Facility Selection <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- STEP 2: Facilities -->
        <div class="form-section step-content" data-step="2" style="display:none;">
            <h2>Available Facilities</h2>
            <p>Click on facilities to add them to the guest's reservation</p>

            <div class="facilities-section">
                <div class="facility-card" data-facility="pool" data-price="0">
                    <div class="facility-img">
                        <i class="fas fa-swimming-pool"></i>
                    </div>
                    <div class="facility-content">
                        <h3 class="facility-title">Infinity Pool</h3>
                        <div class="facility-price">Complimentary for guests</div>
                        <p class="facility-desc">Enjoy our stunning infinity pool with panoramic views of the city
                            skyline.</p>
                    </div>
                </div>

                <div class="facility-card" data-facility="spa" data-price="85">
                    <div class="facility-img">
                        <i class="fas fa-spa"></i>
                    </div>
                    <div class="facility-content">
                        <h3 class="facility-title">Luxury Spa</h3>
                        <div class="facility-price">From $85 per treatment</div>
                        <p class="facility-desc">Relax and rejuvenate with our wide range of spa treatments and
                            therapies.</p>
                    </div>
                </div>

                <div class="facility-card" data-facility="gym" data-price="0">
                    <div class="facility-img">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <div class="facility-content">
                        <h3 class="facility-title">Fitness Center</h3>
                        <div class="facility-price">Complimentary for guests</div>
                        <p class="facility-desc">State-of-the-art fitness equipment available 24/7 for your convenience.
                        </p>
                    </div>
                </div>

                <div class="facility-card" data-facility="dining" data-price="35">
                    <div class="facility-img">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="facility-content">
                        <h3 class="facility-title">Fine Dining</h3>
                        <div class="facility-price">From $35 per person</div>
                        <p class="facility-desc">Experience gourmet cuisine at our award-winning restaurant.</p>
                    </div>
                </div>

                <div class="facility-card" data-facility="concierge" data-price="0">
                    <div class="facility-img">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <div class="facility-content">
                        <h3 class="facility-title">Concierge Service</h3>
                        <div class="facility-price">Complimentary for guests</div>
                        <p class="facility-desc">Our concierge team is available to assist with all your needs and
                            arrangements.</p>
                    </div>
                </div>

                <div class="facility-card" data-facility="wifi" data-price="0">
                    <div class="facility-img">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <div class="facility-content">
                        <h3 class="facility-title">High-Speed WiFi</h3>
                        <div class="facility-price">Complimentary for guests</div>
                        <p class="facility-desc">Stay connected with our high-speed internet access throughout the
                            hotel.</p>
                    </div>
                </div>

                <div class="facility-card" data-facility="parking" data-price="25">
                    <div class="facility-img">
                        <i class="fas fa-parking"></i>
                    </div>
                    <div class="facility-content">
                        <h3 class="facility-title">Valet Parking</h3>
                        <div class="facility-price">$25 per day</div>
                        <p class="facility-desc">Secure valet parking service with in-and-out privileges.</p>
                    </div>
                </div>

                <div class="facility-card" data-facility="business" data-price="50">
                    <div class="facility-img">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <div class="facility-content">
                        <h3 class="facility-title">Business Center</h3>
                        <div class="facility-price">$50 per day access</div>
                        <p class="facility-desc">Fully equipped business center with meeting rooms and printing
                            services.</p>
                    </div>
                </div>
            </div>

            <div class="btn-container">
                <button class="btn btn-outline" id="back-to-step-1">Back to Guest Details</button>
                <button class="btn" id="to-step-3">Proceed to Confirmation <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- STEP 3: Confirmation -->
        <div class="form-section step-content" data-step="3" style="display:none;">
            <div class="guest-summary">
                <h2>Guest Reservation Summary</h2>
                <div class="guest-details">
                    <div>
                        <div class="detail-item">
                            <span class="detail-label">Guest Name:</span>
                            <span id="summary-name">Not provided yet</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Contact:</span>
                            <span id="summary-contact">Not provided yet</span>
                        </div>
                    </div>
                    <div>
                        <div class="detail-item">
                            <span class="detail-label">Stay Duration:</span>
                            <span id="summary-dates">Not selected yet</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Room Type:</span>
                            <span id="summary-room">Not selected yet</span>
                        </div>
                    </div>
                    <div>
                        <div class="detail-item">
                            <span class="detail-label">Total Guests:</span>
                            <span id="summary-guests">Not specified yet</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Additional Charges:</span>
                            <span id="summary-charges">$0</span>
                        </div>
                    </div>
                </div>

                <h3>Selected Facilities</h3>
                <div class="selected-facilities" id="selected-facilities-list">
                    <div class="facility-badge">
                        <span>No facilities selected yet</span>
                    </div>
                </div>

                <div class="btn-container" style="margin-top:20px;">
                    <button class="btn btn-outline" id="back-to-step-2">Back to Facilities</button>
                    <button class="btn" id="complete-reservation">Complete Reservation</button>
                </div>
            </div>
        </div>

    </div>

    <!-- <footer>
        <div class="container">
            <p>© 2023 Luxury Haven Hotel. Staff Reservation System v2.5</p>
            <p>For support, contact IT Department at extension 5050 or email support@luxuryhaven.com</p>
        </div>
    </footer> -->

    <script>
        // --- Dates setup ---
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('check-in').setAttribute('min', today);

        document.getElementById('check-in').addEventListener('change', function () {
            const checkInDate = this.value;
            document.getElementById('check-out').setAttribute('min', checkInDate);
            updateSummary();
        });

        // Initialize with today's date
        document.getElementById('check-in').value = today;

        // Set check-out to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('check-out').value = tomorrow.toISOString().split('T')[0];

        // Stepper logic
        const processSteps = document.querySelectorAll('.process-step');
        const stepContents = document.querySelectorAll('.step-content');
        const connectorFill = document.getElementById('connector-fill');
        const totalSteps = processSteps.length;
        let currentStep = 1;

        function updateProcess() {
            processSteps.forEach((el, idx) => {
                if (idx + 1 <= currentStep) el.classList.add('active');
                else el.classList.remove('active');
            });

            const percent = ((currentStep - 1) / (totalSteps - 1)) * 100;
            connectorFill.style.width = percent + '%';

            stepContents.forEach((el, idx) => {
                el.style.display = (idx + 1 === currentStep) ? 'block' : 'none';
            });

            // keep page scrolled to top of main content when changing steps on small screens
            window.scrollTo({ top: document.querySelector('.main-content').offsetTop - 20, behavior: 'smooth' });
        }

        function goToStep(step) {
            // allow going forward only when validation passes
            if (step > currentStep) {
                if (!validateStep(step)) return;
            }
            currentStep = step;
            updateProcess();

            // when entering confirmation step, ensure summary is updated
            if (currentStep === 3) {
                updateSummary();
            }
        }

        processSteps.forEach((el, idx) => {
            el.addEventListener('click', () => {
                const step = idx + 1;
                // only allow jumping to steps <= currentStep (previous steps) or to the same step
                if (step <= currentStep) goToStep(step);
            });
        });

        // Validate required fields for step transitions
        function validateStep(targetStep) {
            if (targetStep === 2 || targetStep === 3) {
                // ensure guest form required fields filled
                const requiredIds = ['first-name', 'last-name', 'email', 'phone', 'check-in', 'check-out', 'guests', 'room-type'];
                for (let id of requiredIds) {
                    const el = document.getElementById(id);
                    if (!el || !el.value) {
                        alert('Please fill all required guest details before proceeding.');
                        // focus the missing field
                        if (el) el.focus();
                        return false;
                    }
                }
            }
            if (targetStep === 3) {
                // optional: ensure at least one facility? currently not required.
            }
            return true;
        }

        // wire up navigation buttons
        document.getElementById('reservation-form').addEventListener('submit', function (e) {
            e.preventDefault();
            // try to go to step 2
            goToStep(2);
        });

        document.getElementById('to-step-3').addEventListener('click', function () {
            goToStep(3);
        });

        document.getElementById('back-to-step-1').addEventListener('click', function () {
            goToStep(1);
        });

        document.getElementById('back-to-step-2').addEventListener('click', function () {
            goToStep(2);
        });

        // Clear form
        document.getElementById('clear-form').addEventListener('click', function () {
            clearForm();
        });

        function clearForm() {
            document.getElementById('reservation-form').reset();
            document.getElementById('check-in').value = today;
            const t = new Date();
            t.setDate(t.getDate() + 1);
            document.getElementById('check-out').value = t.toISOString().split('T')[0];

            // clear selected facilities
            selectedFacilities.clear();
            document.querySelectorAll('.facility-card.selected').forEach(c => c.classList.remove('selected'));
            updateSelectedFacilities();
            updateSummary();
            goToStep(1);
        }

        // Facility selection
        const facilityCards = document.querySelectorAll('.facility-card');
        const selectedFacilities = new Set();

        facilityCards.forEach(card => {
            card.addEventListener('click', function () {
                this.classList.toggle('selected');
                const facility = this.getAttribute('data-facility');

                if (selectedFacilities.has(facility)) {
                    selectedFacilities.delete(facility);
                } else {
                    selectedFacilities.add(facility);
                }

                updateSelectedFacilities();
            });
        });

        // Update selected facilities list
        function updateSelectedFacilities() {
            const facilitiesList = document.getElementById('selected-facilities-list');
            facilitiesList.innerHTML = '';

            if (selectedFacilities.size === 0) {
                facilitiesList.innerHTML = '<div class="facility-badge"><span>No facilities selected yet</span></div>';
                document.getElementById('summary-charges').textContent = '$0';
            } else {
                let totalCharges = 0;

                selectedFacilities.forEach(facility => {
                    const card = document.querySelector(`[data-facility="${facility}"]`);
                    const name = card.querySelector('.facility-title').textContent;
                    const price = parseInt(card.getAttribute('data-price')) || 0;

                    totalCharges += price;

                    const badge = document.createElement('div');
                    badge.className = 'facility-badge';
                    badge.innerHTML = `<span>${name}</span> <small class="small">${price > 0 ? '$' + price : 'Complimentary'}</small>`;
                    facilitiesList.appendChild(badge);
                });

                document.getElementById('summary-charges').textContent = '$' + totalCharges;
            }
        }

        // Update reservation summary
        function updateSummary() {
            const firstName = document.getElementById('first-name').value;
            const lastName = document.getElementById('last-name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const checkIn = document.getElementById('check-in').value;
            const checkOut = document.getElementById('check-out').value;
            const guests = document.getElementById('guests').value;
            const roomType = document.getElementById('room-type').value;

            if (firstName && lastName) {
                document.getElementById('summary-name').textContent = `${firstName} ${lastName}`;
            } else {
                document.getElementById('summary-name').textContent = 'Not provided yet';
            }

            if (email || phone) {
                document.getElementById('summary-contact').textContent = `${email} | ${phone}`;
            } else {
                document.getElementById('summary-contact').textContent = 'Not provided yet';
            }

            if (checkIn && checkOut) {
                document.getElementById('summary-dates').textContent = `${checkIn} to ${checkOut}`;
            } else {
                document.getElementById('summary-dates').textContent = 'Not selected yet';
            }

            if (guests) {
                document.getElementById('summary-guests').textContent = `${guests} Guest${guests > 1 ? 's' : ''}`;
            } else {
                document.getElementById('summary-guests').textContent = 'Not specified yet';
            }

            if (roomType) {
                const roomText = document.getElementById('room-type').options[document.getElementById('room-type').selectedIndex].text;
                document.getElementById('summary-room').textContent = roomText;
            } else {
                document.getElementById('summary-room').textContent = 'Not selected yet';
            }

            // ensure facilities list updated and charges displayed
            updateSelectedFacilities();
        }

        // Complete reservation
        document.getElementById('complete-reservation').addEventListener('click', function () {
            // final validation
            if (!validateStep(3)) return;

            // you can replace this with an AJAX call to save reservation to server
            alert('Reservation completed successfully!');
            // optionally clear form or go back to step 1
            // clearForm();
        });

        // initialize
        updateProcess();
        updateSelectedFacilities();

        // --- Nepal Province, District, City Data ---
        const locationData = {
            "Province 1": {
                "Bhojpur": ["Bhojpur Municipality", "Shadanand Municipality", "Hatuwagadhi Rural Municipality", "Pauwadungma Rural Municipality", "Ramprasad Rai Rural Municipality", "Salpasilichho Rural Municipality", "Temkemaiyung Rural Municipality"],
                "Dhankuta": ["Dhankuta Municipality", "Chhathar Jorpati Rural Municipality", "Khalsa Chhintang Sahidbhumi Rural Municipality", "Mahalaxmi Municipality", "Pakhribas Municipality", "Sangurigadhi Rural Municipality", "Shahidbhumi Rural Municipality"],
                "Ilam": ["Ilam Municipality", "Deumai Municipality", "Mai Municipality", "Suryodaya Municipality", "Chulachuli Rural Municipality", "Fakfokathum Rural Municipality", "Maijogmai Rural Municipality", "Mangsebung Rural Municipality", "Rong Rural Municipality", "Sandakpur Rural Municipality"],
                "Jhapa": ["Bhadrapur Municipality", "Birtamod Municipality", "Damak Municipality", "Kankai Municipality", "Mechinagar Municipality", "Arjundhara Municipality", "Buddhashanti Rural Municipality", "Gauradaha Municipality", "Gauriganj Rural Municipality", "Haldibari Rural Municipality", "Jhapa Rural Municipality", "Kamal Rural Municipality", "Shivasatakshi Municipality"],
                "Khotang": ["Diktel Rupakot Majhuwagadhi Municipality", "Aiselukharka Rural Municipality", "Barahapokhari Rural Municipality", "Diprung Chuichumma Rural Municipality", "Halesi Tuwachung Municipality", "Jantedhunga Rural Municipality", "Kepilasgadhi Rural Municipality", "Khotehang Rural Municipality", "Lamidanda Rural Municipality", "Rawa Besi Rural Municipality", "Sakela Rural Municipality"],
                "Morang": ["Biratnagar Metropolitan City", "Belbari Municipality", "Budhiganga Rural Municipality", "Dhanpalthan Rural Municipality", "Gramthan Rural Municipality", "Jahada Rural Municipality", "Kanepokhari Rural Municipality", "Katahari Rural Municipality", "Kerabari Rural Municipality", "Letang Municipality", "Miklajung Rural Municipality", "Pathari Sanischare Municipality", "Rangeli Municipality", "Ratuwamai Municipality", "Sundarharaicha Municipality", "Urlabari Municipality"],
                "Okhaldhunga": ["Siddhicharan Municipality", "Champadevi Rural Municipality", "Chisankhugadhi Rural Municipality", "Khijidemba Rural Municipality", "Likhu Rural Municipality", "Manebhanjyang Rural Municipality", "Molung Rural Municipality", "Sunkoshi Rural Municipality"],
                "Panchthar": ["Phidim Municipality", "Falelung Rural Municipality", "Falgunanda Rural Municipality", "Hilihang Rural Municipality", "Kummayak Rural Municipality", "Miklajung Rural Municipality", "Tumbewa Rural Municipality", "Yangwarak Rural Municipality"],
                "Sankhuwasabha": ["Khandbari Municipality", "Chainpur Municipality", "Dharmadevi Municipality", "Madi Municipality", "Panchkhapan Municipality", "Bhotkhola Rural Municipality", "Chichila Rural Municipality", "Makalu Rural Municipality", "Sabhapokhari Rural Municipality", "Silichong Rural Municipality"],
                "Solukhumbu": ["Salleri Municipality", "Dudhkunda Municipality", "Khumbu Pasanglhamu Rural Municipality", "Mahakulung Rural Municipality", "Mapya Dudhkoshi Rural Municipality", "Necha Salyan Rural Municipality", "Sotang Rural Municipality", "Thulung Dudhkoshi Rural Municipality"],
                "Sunsari": ["Inaruwa Municipality", "Dharan Sub-Metropolitan City", "Duhabi Municipality", "Itahari Sub-Metropolitan City", "Barahachhetra Municipality", "Bhokraha Narsingh Rural Municipality", "Dewangunj Rural Municipality", "Gadhi Rural Municipality", "Harinagara Rural Municipality", "Koshi Rural Municipality", "Ramdhuni Municipality"],
                "Taplejung": ["Phungling Municipality", "Aathrai Tribeni Rural Municipality", "Mikwakhola Rural Municipality", "Meringden Rural Municipality", "Pathibhara Yangwarak Rural Municipality", "Phaktanglung Rural Municipality", "Sidingba Rural Municipality", "Sirijangha Rural Municipality"],
                "Tehrathum": ["Myanglung Municipality", "Aathrai Rural Municipality", "Chhathar Rural Municipality", "Laligurans Municipality", "Menchhayayem Rural Municipality"],
                "Udayapur": ["Triyuga Municipality", "Belaka Municipality", "Chaudandigadhi Municipality", "Katari Municipality", "Rautamai Rural Municipality", "Sunkoshi Rural Municipality", "Tapli Rural Municipality", "Udayapurgadhi Rural Municipality"]
            },
            "Madhesh Province": {
                "Bara": ["Kalaiya Sub-Metropolitan City", "Jeetpursimara Sub-Metropolitan City", "Kolhabi Municipality", "Nijgadh Municipality", "Pacharauta Municipality", "Parwanipur Rural Municipality", "Prasauni Rural Municipality", "Simraungadh Municipality", "Suwarna Rural Municipality"],
                "Dhanusha": ["Janakpur Sub-Metropolitan City", "Bateshwar Rural Municipality", "Chhireshwarnath Municipality", "Dhanauji Rural Municipality", "Dhanushadham Municipality", "Ganeshman Charnath Municipality", "Hansapur Municipality", "Kamala Municipality", "Laxminiya Rural Municipality", "Mithila Bihari Municipality", "Mithila Municipality", "Mukhiyapatti Musharniya Rural Municipality", "Nagarain Municipality", "Sabaila Municipality", "Shahidnagar Municipality"],
                "Mahottari": ["Jaleshwar Municipality", "Aurahi Municipality", "Bardibas Municipality", "Balwa Municipality", "Bhangaha Municipality", "Ekdara Rural Municipality", "Gaushala Municipality", "Loharpatti Municipality", "Manara Shiswa Municipality", "Matihani Municipality", "Pipra Rural Municipality", "Ramgopalpur Municipality", "Samsi Rural Municipality", "Sonama Rural Municipality"],
                "Parsa": ["Birgunj Metropolitan City", "Bahudarmai Municipality", "Bindabasini Rural Municipality", "Chhipaharmai Rural Municipality", "Jagarnathpur Rural Municipality", "Jirabhawani Rural Municipality", "Kalikamai Rural Municipality", "Pakaha Mainpur Rural Municipality", "Parsagadhi Municipality", "Pokhariya Municipality", "Sakhuwa Prasauni Rural Municipality", "Thori Rural Municipality"],
                "Rautahat": ["Gaur Municipality", "Baudhimai Municipality", "Brindaban Municipality", "Chandrapur Municipality", "Dewahi Gonahi Municipality", "Durga Bhagwati Rural Municipality", "Fatuwa Bijaypur Municipality", "Gadhimai Municipality", "Garuda Municipality", "Gujara Municipality", "Ishanath Municipality", "Katahariya Municipality", "Madhav Narayan Municipality", "Maulapur Municipality", "Paroha Municipality", "Phatuwa Bijaypur Municipality", "Rajdevi Municipality", "Rajpur Municipality", "Yamunamai Rural Municipality"],
                "Saptari": ["Rajbiraj Municipality", "Agnisair Krishna Savaran Rural Municipality", "Balan Bihul Rural Municipality", "Bodebarsain Municipality", "Chhinnamasta Rural Municipality", "Dakneshwori Municipality", "Hanumannagar Kankalini Municipality", "Kanchanrup Municipality", "Khadak Municipality", "Mahadeva Rural Municipality", "Rajgadh Rural Municipality", "Rupani Rural Municipality", "Saptakoshi Municipality", "Shambhunath Municipality", "Surunga Municipality", "Tilathi Koiladi Rural Municipality", "Tirhut Rural Municipality"],
                "Sarlahi": ["Malangwa Municipality", "Bagmati Municipality", "Balara Municipality", "Barahathawa Municipality", "Bishnu Rural Municipality", "Bramhapuri Rural Municipality", "Chakraghatta Rural Municipality", "Chandranagar Rural Municipality", "Dhankaul Rural Municipality", "Godaita Municipality", "Haripur Municipality", "Haripurwa Municipality", "Haripurwa Rural Municipality", "Ishworpur Municipality", "Kabilasi Municipality", "Kaudena Rural Municipality", "Lalbandi Municipality", "Parsa Rural Municipality", "Ramnagar Rural Municipality", "Sakhuwa Prasauni Rural Municipality"],
                "Siraha": ["Siraha Municipality", "Aurahi Rural Municipality", "Bariyarpatti Rural Municipality", "Bhagwanpur Rural Municipality", "Bishnupur Rural Municipality", "Dhangadhimai Municipality", "Golbazar Municipality", "Kalyanpur Municipality", "Karjanha Municipality", "Lahan Municipality", "Laxmipur Patari Rural Municipality", "Mirchaiya Municipality", "Naraha Rural Municipality", "Navarajpur Rural Municipality", "Sakhuwanankarkatti Rural Municipality", "Sukhipur Municipality"],
            },
            "Bagmati Province": {
                "Bhaktapur": ["Bhaktapur Municipality", "Changunarayan Municipality", "Madhyapur Thimi Municipality", "Suryabinayak Municipality"],
                "Chitwan": ["Bharatpur Metropolitan City", "Kalika Municipality", "Khairahani Municipality", "Madi Municipality", "Rapti Municipality", "Ratnanagar Municipality", "Ichchhakamana Rural Municipality"],
                "Dhading": ["Dhadingbesi (Nilkantha Municipality)", "Benighat Rorang Rural Municipality", "Dhunibesi Municipality", "Galchhi Rural Municipality", "Gajuri Rural Municipality", "Gangajamuna Rural Municipality", "Jwalamukhi Rural Municipality", "Khaniyabash Rural Municipality", "Netrawati Dabjong Rural Municipality", "Rubi Valley Rural Municipality", "Siddhalek Rural Municipality", "Thakre Rural Municipality", "Tripurasundari Rural Municipality"],
                "Dolakha": ["Bhimeshwor Municipality", "Baiteshwor Rural Municipality", "Bigu Rural Municipality", "Gaurishankar Rural Municipality", "Jiri Municipality", "Kalinchowk Rural Municipality", "Melung Rural Municipality", "Shailung Rural Municipality", "Tamakoshi Rural Municipality"],
                "Kathmandu": ["Kathmandu Metropolitan City", "Budhanilkantha Municipality", "Chandragiri Municipality", "Dakshinkali Municipality", "Gokarneshwor Municipality", "Kageshwori Manohara Municipality", "Kirtipur Municipality", "Nagarjun Municipality", "Shankharapur Municipality", "Tarakeshwar Municipality", "Tokha Municipality"],
                "Kavrepalanchok": ["Dhulikhel Municipality", "Banepa Municipality", "Bethanchok Rural Municipality", "Bhumlu Rural Municipality", "Chaurideurali Rural Municipality", "Khanikhola Rural Municipality", "Mandandeupur Municipality", "Namobuddha Municipality", "Panauti Municipality", "Panchkhal Municipality", "Roshi Rural Municipality", "Temal Rural Municipality"],
                "Lalitpur": ["Lalitpur Metropolitan City", "Bagmati Rural Municipality", "Godawari Municipality", "Konjyosom Rural Municipality", "Mahankal Rural Municipality", "Mahalaxmi Municipality", "Shankharapur Municipality"],
                "Makwanpur": ["Hetauda Sub-Metropolitan City", "Bakaiya Rural Municipality", "Bhimphedi Rural Municipality", "Bagmati Rural Municipality", "Indrasarowar Rural Municipality", "Kailash Rural Municipality", "Makawanpurgadhi Rural Municipality", "Manahari Rural Municipality", "Raksirang Rural Municipality", "Thaha Municipality"],
                "Nuwakot": ["Bidur Municipality", "Belkotgadhi Municipality", "Dupcheshwar Rural Municipality", "Kakani Rural Municipality", "Kispang Rural Municipality", "Likhu Rural Municipality", "Meghang Rural Municipality", "Panchakanya Rural Municipality", "Shivapuri Rural Municipality", "Suryagadhi Rural Municipality", "Tadi Rural Municipality", "Tarkeshwar Rural Municipality"],
                "Ramechhap": ["Manthali Municipality", "Doramba Rural Municipality", "Gokulganga Rural Municipality", "Khandadevi Rural Municipality", "Likhu Tamakoshi Rural Municipality", "Ramechhap Municipality", "Sunapati Rural Municipality", "Umakunda Rural Municipality"],
                "Rasuwa": ["Uttargaya Rural Municipality", "Aamachhodingmo Rural Municipality", "Gosaikunda Rural Municipality", "Kalika Rural Municipality", "Naukunda Rural Municipality"],
                "Sindhuli": ["Kamalamai Municipality", "Dudhauli Municipality", "Golanjor Rural Municipality", "Ghyanglekh Rural Municipality", "Hariharpurgadhi Rural Municipality", "Marin Rural Municipality", "Phikkal Rural Municipality", "Sunkoshi Rural Municipality", "Tinpatan Rural Municipality"],
                "Sindhupalchok": ["Chautara Sangachokgadhi Municipality", "Barhabise Municipality", "Balephi Rural Municipality", "Bhotekoshi Rural Municipality", "Helambu Rural Municipality", "Indrawati Rural Municipality", "Jugal Rural Municipality", "Lisankhu Pakhar Rural Municipality", "Melamchi Municipality", "Panchpokhari Thangpal Rural Municipality", "Sunkoshi Rural Municipality", "Tripurasundari Rural Municipality"]
            },
            "Gandaki Province": {
                "Baglung": ["Baglung Municipality", "Dhorpatan Municipality", "Galkot Municipality", "Jaimuni Municipality", "Bareng Rural Municipality", "Kanthekhola Rural Municipality", "Nisikhola Rural Municipality", "Taman Khola Rural Municipality", "Tara Khola Rural Municipality"],
                "Gorkha": ["Gorkha Municipality", "Palungtar Municipality", "Aarughat Rural Municipality", "Ajirkot Rural Municipality", "Bhimsen Thapa Rural Municipality", "Chum Nubri Rural Municipality", "Dharche Rural Municipality", "Gandaki Rural Municipality", "Sahid Lakhan Rural Municipality", "Siranchok Rural Municipality"],
                "Kaski": ["Pokhara Metropolitan City", "Annapurna Rural Municipality", "Machhapuchchhre Rural Municipality", "Madi Rural Municipality", "Rupa Rural Municipality"],
                "Lamjung": ["Besisahar Municipality", "Madhya Nepal Municipality", "Rainas Municipality", "Sundarbazar Municipality", "Dordi Rural Municipality", "Dudhpokhari Rural Municipality", "Kwholasothar Rural Municipality", "Marsyangdi Rural Municipality"],
                "Manang": ["Chame Rural Municipality", "Manang Ngisyang Rural Municipality", "Narpa Bhumi Rural Municipality", "Nashong Rural Municipality"],
                "Mustang": ["Gharpajhong Rural Municipality", "Lomanthang Rural Municipality", "Lo-Ghekar Damodarkunda Rural Municipality", "Thasang Rural Municipality", "Varagung Muktichhetra Rural Municipality"],
                "Myagdi": ["Beni Municipality", "Annapurna Rural Municipality", "Dhaulagiri Rural Municipality", "Malika Rural Municipality", "Mangala Rural Municipality", "Raghuganga Rural Municipality"],
                "Nawalpur": ["Kawasoti Municipality", "Devchuli Municipality", "Gaindakot Municipality", "Madhyabindu Municipality", "Binayi Tribeni Rural Municipality", "Bulingtar Rural Municipality", "Hupsekot Rural Municipality", "Baudikali Rural Municipality"],
                "Parbat": ["Kusma Municipality", "Jaljala Rural Municipality", "Mahashila Rural Municipality", "Modi Rural Municipality", "Paiyun Rural Municipality", "Phalebas Municipality", "Bihadi Rural Municipality"],
                "Syangja": ["Putalibazar Municipality", "Bhirkot Municipality", "Chapakot Municipality", "Galyang Municipality", "Waling Municipality", "Aandhikhola Rural Municipality", "Arjunchaupari Rural Municipality", "Biruwa Rural Municipality", "Harinas Rural Municipality", "Kaligandaki Rural Municipality", "Phedikhola Rural Municipality"],
                "Tanahun": ["Damauli (Byas Municipality)", "Bandipur Rural Municipality", "Bhimad Municipality", "Devghat Rural Municipality", "Ghiring Rural Municipality", "Myagde Rural Municipality", "Rhishing Rural Municipality", "Shuklagandaki Municipality", "Vyas Municipality"]
            },
            "Lumbini Province": {
                "Arghakhanchi": ["Sandhikharka Municipality", "Bhumikasthan Municipality", "Chhatradev Rural Municipality", "Malarani Rural Municipality", "Panini Rural Municipality", "Shitaganga Municipality"],
                "Banke": ["Nepalgunj Sub-Metropolitan City", "Baijanath Rural Municipality", "Duduwa Rural Municipality", "Janaki Rural Municipality", "Kohalpur Municipality", "Khajura Rural Municipality", "Narainapur Rural Municipality", "Rapti Sonari Rural Municipality"],
                "Bardiya": ["Gulariya Municipality", "Badhaiyatal Rural Municipality", "Barbardiya Municipality", "Basgadhi Municipality", "Geruwa Rural Municipality", "Madhuwan Municipality", "Rajapur Municipality", "Thakurbaba Municipality"],
                "Dang": ["Ghorahi Sub-Metropolitan City", "Lamahi Municipality", "Tulsipur Sub-Metropolitan City", "Banglachuli Rural Municipality", "Dangisharan Rural Municipality", "Gadhawa Rural Municipality", "Rajpur Rural Municipality", "Rapti Rural Municipality", "Shantinagar Rural Municipality"],
                "Eastern Rukum": ["Rukumkot (Putha Uttarganga Rural Municipality)", "Bhume Rural Municipality", "Sisne Rural Municipality"],
                "Gulmi": ["Tamghas (Resunga Municipality)", "Chandrakot Rural Municipality", "Chhatrakot Rural Municipality", "Dhurkot Rural Municipality", "Gulmidarbar Rural Municipality", "Isma Rural Municipality", "Kaligandaki Rural Municipality", "Madane Rural Municipality", "Malika Rural Municipality", "Musikot Municipality", "Ruru Rural Municipality", "Satyawati Rural Municipality"],
                "Kapilvastu": ["Taulihawa (Kapilvastu Municipality)", "Banganga Municipality", "Bijaynagar Rural Municipality", "Buddhabhumi Municipality", "Krishnanagar Municipality", "Maharajgunj Municipality", "Mayadevi Rural Municipality", "Shivaraj Municipality", "Suddhodhan Rural Municipality", "Yashodhara Rural Municipality"],
                "Parasi": ["Ramgram Municipality", "Bardaghat Municipality", "Sunwal Municipality", "Palhinandan Rural Municipality", "Pratappur Rural Municipality", "Sarawal Rural Municipality"],
                "Palpa": ["Tansen Municipality", "Bagnaskali Rural Municipality", "Mathagadhi Rural Municipality", "Nisdi Rural Municipality", "Purbakhola Rural Municipality", "Rainadevi Chhahara Rural Municipality", "Rampur Municipality", "Ribdikot Rural Municipality", "Tinau Rural Municipality"],
                "Pyuthan": ["Pyuthan Khalanga (Pyuthan Municipality)", "Airawati Rural Municipality", "Gaumukhi Rural Municipality", "Jhimruk Rural Municipality", "Mallarani Rural Municipality", "Mandavi Rural Municipality", "Naubahini Rural Municipality", "Sarumarani Rural Municipality", "Sworgadwari Municipality"],
                "Rolpa": ["Liwang (Rolpa Municipality)", "Gangadev Rural Municipality", "Lungri Rural Municipality", "Madi Rural Municipality", "Pariwartan Rural Municipality", "Runtigadhi Rural Municipality", "Sunchhahari Rural Municipality", "Thawang Rural Municipality", "Triveni Rural Municipality"],
                "Rupandehi": ["Butwal Sub-Metropolitan City", "Devdaha Municipality", "Gaidahawa Rural Municipality", "Kanchan Rural Municipality", "Kotahimai Rural Municipality", "Lumbini Sanskritik Municipality", "Marchawari Rural Municipality", "Mayadevi Rural Municipality", "Omsatiya Rural Municipality", "Rohini Rural Municipality", "Sainamaina Municipality", "Siddharthanagar Municipality", "Siyari Rural Municipality", "Sudhdhodhan Rural Municipality", "Tilottama Municipality"]
            },
            "Karnali Province": {
                "Dailekh": ["Dailekh Municipality", "Aathbis Municipality", "Bhairabi Rural Municipality", "Chamunda Bindrasaini Municipality", "Dullu Municipality", "Dungeshwor Rural Municipality", "Mahabu Rural Municipality", "Narayan Municipality", "Naumule Rural Municipality", "Thantikandh Rural Municipality"],
                "Dolpa": ["Dunai (Thuli Bheri Municipality)", "Chharka Tangsong Rural Municipality", "Dolpo Buddha Rural Municipality", "Jagadulla Rural Municipality", "Kaike Rural Municipality", "Mudkechula Rural Municipality", "Shey Phoksundo Rural Municipality", "Tripurasundari Municipality"],
                "Humla": ["Simikot Rural Municipality", "Adanchuli Rural Municipality", "Chankheli Rural Municipality", "Kharpunath Rural Municipality", "Sarkegad Rural Municipality", "Tanjakot Rural Municipality"],
                "Jajarkot": ["Khalanga (Bheri Municipality)", "Chhedagad Municipality", "Junichande Rural Municipality", "Kuse Rural Municipality", "Nalgad Municipality", "Shivalaya Rural Municipality"],
                "Jumla": ["Jumla Municipality", "Chandannath Municipality", "Guthichaur Rural Municipality", "Hima Rural Municipality", "Kanakasundari Rural Municipality", "Patrasi Rural Municipality", "Sinja Rural Municipality", "Tatopani Rural Municipality"],
                "Kalikot": ["Manma (Kalikot Municipality)", "Mahawai Rural Municipality", "Naraharinath Rural Municipality", "Pachaljharana Rural Municipality", "Palata Rural Municipality", "Raskot Municipality", "Sanni Triveni Rural Municipality", "Shubha Kalika Rural Municipality", "Tilagufa Municipality"],
                "Mugu": ["Gamgadhi (Chhayanath Rara Municipality)", "Khatyad Rural Municipality", "Mugum Karmarong Rural Municipality", "Soru Rural Municipality"],
                "Salyan": ["Salyan Khalanga (Sharada Municipality)", "Bagchaur Municipality", "Bangad Kupinde Municipality", "Chhatreshwori Rural Municipality", "Darma Rural Municipality", "Dhanwang Rural Municipality", "Kalimati Rural Municipality", "Kapurkot Rural Municipality", "Kumakh Rural Municipality", "Siddha Kumakh Rural Municipality", "Triveni Rural Municipality"],
                "Surkhet": ["Birendranagar Municipality", "Bheriganga Municipality", "Barahatal Rural Municipality", "Chaukune Rural Municipality", "Chingad Rural Municipality", "Gurbhakot Municipality", "Lekbeshi Municipality", "Panchapuri Municipality", "Simta Rural Municipality"],
                "Western Rukum": ["Musikot Municipality", "Aathbiskot Municipality", "Banphikot Rural Municipality", "Chaurjahari Municipality", "Sanibheri Rural Municipality", "Tribeni Rural Municipality"]
            },
            "Sudurpashchim Province": {
                "Achham": ["Mangalsen Municipality", "Bannigadhi Jayagadh Rural Municipality", "Chaurpati Rural Municipality", "Dhakari Rural Municipality", "Kamalbazar Municipality", "Mellekh Rural Municipality", "Ramaroshan Rural Municipality", "Sanphebagar Municipality", "Turmakhand Rural Municipality"],
                "Baitadi": ["Dasharathchand Municipality", "Dilasaini Rural Municipality", "Dogadakedar Rural Municipality", "Melauli Municipality", "Pancheshwor Rural Municipality", "Patan Municipality", "Purchaudi Municipality", "Shivanath Rural Municipality", "Sigas Rural Municipality"],
                "Bajhang": ["Chainpur Municipality", "Bungal Municipality", "Durgathali Rural Municipality", "Jaya Prithvi Municipality", "Kedarsyun Rural Municipality", "Khaptad Chhanna Rural Municipality", "Masta Rural Municipality", "Saipal Rural Municipality", "Surma Rural Municipality", "Talkot Rural Municipality", "Thalara Rural Municipality"],
                "Bajura": ["Martadi (Badimalika Municipality)", "Budhiganga Municipality", "Budhinanda Municipality", "Chhededaha Rural Municipality", "Gaumul Rural Municipality", "Himali Rural Municipality", "Jagannath Rural Municipality", "Khaptad Chhededaha Rural Municipality", "Swamikartik Khapar Rural Municipality", "Triveni Municipality"],
                "Dadeldhura": ["Amargadhi Municipality", "Aalital Rural Municipality", "Bhageshwar Rural Municipality", "Ganayapdhura Rural Municipality", "Nawadurga Rural Municipality", "Parashuram Municipality", "Sahajpur Rural Municipality"],
                "Darchula": ["Darchula Municipality", "Apihimal Rural Municipality", "Byas Rural Municipality", "Mahakali Municipality", "Malikarjun Rural Municipality", "Marma Rural Municipality", "Naugad Rural Municipality", "Shailyashikhar Municipality"],
                "Doti": ["Dipayal Silgadhi Municipality", "Adarsha Rural Municipality", "Badikedar Rural Municipality", "Bogatan Phudsil Rural Municipality", "Jorayal Rural Municipality", "K.I. Singh Rural Municipality", "Purbichauki Rural Municipality", "Sayal Rural Municipality", "Shikhar Municipality"],
                "Kailali": ["Dhangadhi Sub-Metropolitan City", "Bhajani Municipality", "Ghodaghodi Municipality", "Godawari Municipality", "Gauriganga Municipality", "Janaki Rural Municipality", "Joshipur Rural Municipality", "Kailari Rural Municipality", "Lamkichuha Municipality", "Mohanyal Rural Municipality", "Tikapur Municipality"],
                "Kanchanpur": ["Bhimdatta Municipality", "Bedkot Municipality", "Belauri Municipality", "Beldandi Rural Municipality", "Krishnapur Municipality", "Laljhadi Rural Municipality", "Mahakali Municipality", "Punarbas Municipality", "Shuklaphanta Municipality"]
            }
        };

        const provinceSelect = document.getElementById('province');
        const districtSelect = document.getElementById('district');
        const citySelect = document.getElementById('city');

        // Populate provinces
        for (const province in locationData) {
            const option = document.createElement('option');
            option.value = province;
            option.textContent = province;
            provinceSelect.appendChild(option);
        }

        // When province changes, update districts
        provinceSelect.addEventListener('change', function () {
            districtSelect.innerHTML = '<option value="">Select District</option>';
            citySelect.innerHTML = '<option value="">Select City</option>';
            if (this.value && locationData[this.value]) {
                for (const district in locationData[this.value]) {
                    const option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    districtSelect.appendChild(option);
                }
            }
        });

        districtSelect.addEventListener('change', function () {
            citySelect.innerHTML = '<option value="">Select City</option>';
            const province = provinceSelect.value;
            if (province && this.value && locationData[province][this.value]) {
                for (const city of locationData[province][this.value]) {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                }
            }
        });

        document.getElementById('room-type').addEventListener('change', function () {
            let typeId = this.value;
            let roomNumberSelect = document.getElementById('room-number');

            // Clear previous options
            roomNumberSelect.innerHTML = '<option value="">Loading...</option>';

            if (typeId) {
                fetch('get_rooms.php?room_type_id=' + typeId)
                    .then(response => response.json())
                    .then(data => {
                        roomNumberSelect.innerHTML = '<option value="">Select Room Number</option>';
                        if (data.length > 0) {
                            data.forEach(room => {
                                let option = document.createElement('option');
                                option.value = room.room_id;
                                option.textContent = room.room_number;
                                roomNumberSelect.appendChild(option);
                            });
                        } else {
                            roomNumberSelect.innerHTML = '<option value="">No Rooms Available</option>';
                        }
                    });
            } else {
                roomNumberSelect.innerHTML = '<option value="">Select Room Number</option>';
            }
        });
    </script>
</body>

</html>