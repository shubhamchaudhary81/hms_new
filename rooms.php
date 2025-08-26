<?php
include_once 'config/configdatabse.php';

// Handle search/filter
$where = "WHERE r.status = 'available'";

if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where .= " AND (rt.room_type_name LIKE '%$search%' OR r.room_number LIKE '%$search%' OR r.description LIKE '%$search%')";
}
if (!empty($_GET['min_price'])) {
    $min_price = floatval($_GET['min_price']);
    $where .= " AND r.price_per_night >= $min_price";
}
if (!empty($_GET['max_price'])) {
    $max_price = floatval($_GET['max_price']);
    $where .= " AND r.price_per_night <= $max_price";
}
if (!empty($_GET['capacity'])) {
    $capacity = intval($_GET['capacity']);
    $where .= " AND r.capacity >= $capacity";
}
if (!empty($_GET['floor'])) {
    $floor = intval($_GET['floor']);
    $where .= " AND r.floor_number = $floor";
}

// For floor filter dropdown
$floors = [];
$floor_res = $conn->query("SELECT DISTINCT floor_number FROM room ORDER BY floor_number ASC");
while ($f = $floor_res->fetch_assoc()) {
    $floors[] = $f['floor_number'];
}

$sql = "SELECT r.*, r.image, rt.room_type_name, rt.description as type_description
        FROM room r
        JOIN RoomType rt ON r.room_type = rt.room_type_id
        $where
        ORDER BY r.room_id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $room_id = $row['room_id'];

    // Get review count and average rating
    $review_sql = "SELECT COUNT(*) as review_count, AVG(rating) as avg_rating FROM reviews WHERE room_id = $room_id";
    $review_res = $conn->query($review_sql);
    $review_data = $review_res ? $review_res->fetch_assoc() : ['review_count' => 0, 'avg_rating' => 0];

    // Get amenities for this room
    $amenity_sql = "SELECT a.amenity_name, a.icon_url FROM RoomAmenity ra 
                    JOIN Amenity a ON ra.amenity_id = a.amenity_id 
                    WHERE ra.room_id = $room_id LIMIT 5";
    $amenity_res = $conn->query($amenity_sql);
    $amenities = [];
    if ($amenity_res) {
        while ($amenity = $amenity_res->fetch_assoc()) {
            $amenities[] = $amenity;
        }
    }

    $row['reviews'] = $review_data['review_count'] ?? 0;
    $row['avg_rating'] = $review_data['avg_rating'] ?? 0;
    $row['amenities'] = $amenities;
    $row['features'] = isset($row['features']) ? array_map('trim', explode(',', $row['features'])) : [];
    $rooms[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Himalaya Hotel </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/mainindex.css">

    <style>
        :root {
            --primary-color: #8b7355;
            --secondary-color: #f59e0b;
            --accent-color: #10b981;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f8fafc;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Montserrat', sans-serif;
      color: var(--dark);
      background-color: #fff;
        }


        /* Premium Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary) !important;
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark);
            margin: 0 10px;
            position: relative;
        }

        .nav-link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--accent);
            transition: width 0.3s ease;
        }

        .nav-link:hover:after,
        .nav-link.active:after {
            width: 100%;
        }

        .btn-premium {
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(212, 167, 98, 0.3);
        }

        .btn-premium:hover {
            background: #c29555;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 167, 98, 0.4);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .hero-rooms {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            background: #8b7355;
            padding: 4rem 0 3rem 0;
            margin-top: 4rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-rooms::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .hero-rooms .container {
            position: relative;
            z-index: 2;
        }

        .hero-rooms h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hero-rooms p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.25rem;
            margin-bottom: 0;
            font-weight: 400;
        }

        .search-bar {
            background: white;
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            padding: 2rem;
            margin: -2rem auto 3rem auto;
            position: relative;
            z-index: 10;
            max-width: 1200px;
            border: 1px solid var(--border-color);
        }

        .search-bar .form-label {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .search-bar .form-control,
        .search-bar .form-select {
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .search-bar .form-control:focus,
        .search-bar .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        .btn-primary {
            /* background: linear-gradient(135deg, var(--primary-color) 0%, #3730a3 100%); */
            background: var(--primary-color);
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
            background-color: #8b7355;
        }

        .room-listing {
            margin-top: 1rem;
        }

        .room-card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            position: relative;
            display: flex;
            overflow: hidden;
        }

        .room-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color);
        }

        .room-image-container {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            min-width: 200px;
            max-width: 200px;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .room-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
            background: #f3f4f6;
        }

        .image-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #9ca3af;
            font-size: 1.2rem;
        }

        .room-card:hover .room-img {
            transform: scale(1.05);
        }

        .room-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: rgba(16, 185, 129, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .room-body {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .room-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .room-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .room-number {
            color: var(--text-light);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .room-rating {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .room-description {
            color: var(--text-light);
            font-size: 0.875rem;
            margin-bottom: 1rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .room-details {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: var(--bg-light);
            border-radius: 0.5rem;
            flex-wrap: wrap;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-icon {
            font-size: 1rem;
            color: var(--primary-color);
        }

        .detail-label {
            font-size: 0.75rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .detail-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .room-amenities {
            margin-bottom: 1rem;
        }

        .amenities-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .amenities-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
        }

        .amenity-badge {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: var(--primary-color);
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .room-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-color);
        }

        .room-price {
            display: flex;
            flex-direction: column;
        }

        .price-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-color);
        }

        .price-period {
            font-size: 0.75rem;
            color: var(--text-light);
            font-weight: 500;
        }

        .room-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-view {
            /* background: linear-gradient(135deg, var(--primary-color) 0%, #3730a3 100%); */
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 0.75rem;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .btn-view:hover {
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-book {
            background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 0.75rem;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .btn-book:hover {
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-wishlist {
            background: white;
            color: var(--text-light);
            border: 2px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-wishlist:hover {
            color: #ef4444;
            border-color: #ef4444;
            transform: translateY(-1px);
        }

        .stats-section {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .no-rooms {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .no-rooms i {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        @media (max-width: 991px) {
            .hero-rooms h1 {
                font-size: 2.5rem;
            }

            .room-image-container {
                min-width: 180px;
                max-width: 180px;
                height: 130px;
            }

            .search-bar {
                margin: -1rem 1rem 2rem 1rem;
            }
        }

        @media (max-width: 767px) {
            .hero-rooms h1 {
                font-size: 2rem;
            }

            .hero-rooms p {
                font-size: 1rem;
            }

            .search-bar {
                padding: 1.5rem;
            }

            .room-card {
                flex-direction: column;
            }

            .room-image-container {
                min-width: 100%;
                max-width: 100%;
                height: 200px;
            }

            .room-footer {
                flex-direction: column;
                gap: 0.75rem;
                align-items: stretch;
            }

            .room-actions {
                justify-content: center;
            }

            .room-details {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Himalaya Hotel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="#gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="rooms.php">Rooms</a></li>
                    <li class="nav-item ms-3"><a href="login.php" class="btn btn-premium">Login / Sign Up</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Hero Section -->
    <section class="hero-rooms">
        <div class="container">
            <h1>Luxury Rooms & Suites</h1>
            <p>Experience unparalleled comfort and elegance in our meticulously designed accommodations</p>
        </div>
    </section>

    <!-- Search/Filter Bar -->
    <section class="container">
        <div class="search-bar">
            <form class="row g-3 align-items-end" method="get">
                <div class="col-lg-3 col-md-6">
                    <label for="search" class="form-label">Search Rooms</label>
                    <input type="text" class="form-control" id="search" name="search"
                        placeholder="Room type, number, or description"
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="min_price" class="form-label">Min Price</label>
                    <input type="number" class="form-control" id="min_price" name="min_price" min="0" placeholder="₹0"
                        value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="max_price" class="form-label">Max Price</label>
                    <input type="number" class="form-control" id="max_price" name="max_price" min="0"
                        placeholder="₹50000"
                        value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" min="1" placeholder="Guests"
                        value="<?= isset($_GET['capacity']) ? htmlspecialchars($_GET['capacity']) : '' ?>">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="floor" class="form-label">Floor</label>
                    <select class="form-select" id="floor" name="floor">
                        <option value="">Any Floor</option>
                        <?php foreach ($floors as $f): ?>
                            <option value="<?= $f ?>" <?= (isset($_GET['floor']) && $_GET['floor'] == $f) ? 'selected' : '' ?>>
                                Floor <?= $f ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-1 col-md-12 d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="container">
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?= count($rooms) ?></div>
                    <div class="stat-label">Available Rooms</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= count(array_unique(array_column($rooms, 'room_type_name'))) ?></div>
                    <div class="stat-label">Room Types</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= count($floors) ?></div>
                    <div class="stat-label">Floors</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">4.8★</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Room Listings -->
    <section class="container room-listing">
        <div class="row">
            <?php if (empty($rooms)): ?>
                <div class="col-12">
                    <div class="no-rooms">
                        <i class="bi bi-search"></i>
                        <h3>No rooms found</h3>
                        <p class="text-muted">Try adjusting your search criteria to find available rooms.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($rooms as $room): ?>
                <div class="col-12">
                    <div class="room-card">
                        <div class="room-image-container">
                            <div class="image-loading">
                                <i class="bi bi-image"></i>
                            </div>
                            <?php
                            $imageFile = $room['image'] ?? '';

                            // Check if image exists in room_images folder
                            if (!empty($imageFile)) {
                                $roomImagePath = 'uploads/room_images/' . $imageFile;
                                $fullImagePath = __DIR__ . '/uploads/room_images/' . $imageFile;

                                if (file_exists($fullImagePath)) {
                                    $imgSrc = $roomImagePath;
                                } else {
                                    // Fallback to assets/images
                                    $assetsImagePath = 'assets/images/' . $imageFile;
                                    $fullAssetsPath = __DIR__ . '/assets/images/' . $imageFile;

                                    if (file_exists($fullAssetsPath)) {
                                        $imgSrc = $assetsImagePath;
                                    } else {
                                        // Default fallback based on room type
                                        $roomType = strtolower($room['room_type_name']);
                                        if (strpos($roomType, 'deluxe') !== false || strpos($roomType, 'delux') !== false) {
                                            $imgSrc = 'assets/images/delux.jpg';
                                        } elseif (strpos($roomType, 'suite') !== false) {
                                            $imgSrc = 'assets/images/suite.jpg';
                                        } elseif (strpos($roomType, 'standard') !== false) {
                                            $imgSrc = 'assets/images/standard.jpg';
                                        } else {
                                            $imgSrc = 'assets/images/hotel.jpg';
                                        }
                                    }
                                }
                            } else {
                                // No image specified, use default based on room type
                                $roomType = strtolower($room['room_type_name']);
                                if (strpos($roomType, 'deluxe') !== false || strpos($roomType, 'delux') !== false) {
                                    $imgSrc = 'assets/images/delux.jpg';
                                } elseif (strpos($roomType, 'suite') !== false) {
                                    $imgSrc = 'assets/images/suite.jpg';
                                } elseif (strpos($roomType, 'standard') !== false) {
                                    $imgSrc = 'assets/images/standard.jpg';
                                } else {
                                    $imgSrc = 'assets/images/hotel.jpg';
                                }
                            }
                            ?>
                            <img src="<?= htmlspecialchars($imgSrc) ?>" class="room-img"
                                alt="<?= htmlspecialchars($room['room_type_name']) ?> Room"
                                onerror="this.src='assets/images/hotel.jpg'">
                            <div class="room-badge">
                                <i class="bi bi-check-circle-fill me-1"></i>Available
                            </div>
                        </div>

                        <div class="room-body">
                            <div class="room-header">
                                <div>
                                    <div class="room-title"><?= htmlspecialchars($room['room_type_name']) ?></div>
                                    <div class="room-number">Room <?= htmlspecialchars($room['room_number']) ?></div>
                                </div>
                                <div class="room-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <?= $room['avg_rating'] > 0 ? number_format($room['avg_rating'], 1) : 'N/A' ?>
                                </div>
                            </div>

                            <div class="room-description">
                                <?= htmlspecialchars($room['description'] ?? $room['type_description'] ?? 'Luxurious accommodation with modern amenities and stunning views.') ?>
                            </div>

                            <div class="room-details">
                                <div class="detail-item">
                                    <i class="bi bi-people-fill detail-icon"></i>
                                    <span class="detail-label">Capacity:</span>
                                    <span class="detail-value"><?= htmlspecialchars($room['capacity']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="bi bi-building detail-icon"></i>
                                    <span class="detail-label">Floor:</span>
                                    <span class="detail-value"><?= htmlspecialchars($room['floor_number']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="bi bi-arrows-angle-expand detail-icon"></i>
                                    <span class="detail-label">Size:</span>
                                    <span class="detail-value"><?= htmlspecialchars($room['size'] ?? 'N/A') ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="bi bi-eye detail-icon"></i>
                                    <span class="detail-label">View:</span>
                                    <span class="detail-value"><?= htmlspecialchars($room['view_type'] ?? 'City') ?></span>
                                </div>
                            </div>

                            <?php if (!empty($room['amenities'])): ?>
                                <div class="room-amenities">
                                    <div class="amenities-title">Key Amenities</div>
                                    <div class="amenities-list">
                                        <?php foreach (array_slice($room['amenities'], 0, 4) as $amenity): ?>
                                            <span class="amenity-badge">
                                                <i class="bi bi-check"></i>
                                                <?= htmlspecialchars($amenity['amenity_name']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($room['amenities']) > 4): ?>
                                            <span class="amenity-badge">
                                                +<?= count($room['amenities']) - 4 ?> more
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="room-footer">
                                <div class="room-price">
                                    <div class="price-amount">₹<?= number_format($room['price_per_night'], 0) ?></div>
                                    <div class="price-period">per night</div>
                                </div>
                                <div class="room-actions">
                                    <button class="btn-wishlist" onclick="toggleWishlist(<?= $room['room_id'] ?>)"
                                        title="Add to Wishlist">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    <a href="guest/room_details.php?id=<?= $room['room_id'] ?>" class="btn-view">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="guest/reservation_form.php?room_id=<?= $room['room_id'] ?>" class="btn-book">
                                        <i class="bi bi-calendar-check"></i> Book Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Wishlist functionality
        function toggleWishlist(roomId) {
            const button = event.target.closest('.btn-wishlist');
            const icon = button.querySelector('i');

            if (icon.classList.contains('bi-heart')) {
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                button.style.color = '#ef4444';
                button.style.borderColor = '#ef4444';
                showNotification('Room added to wishlist!', 'success');
            } else {
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                button.style.color = '';
                button.style.borderColor = '';
                showNotification('Room removed from wishlist!', 'info');
            }
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }

        // Smooth scroll for search form
        document.querySelector('form').addEventListener('submit', function (e) {
            const searchBar = document.querySelector('.search-bar');
            if (searchBar) {
                searchBar.scrollIntoView({ behavior: 'smooth' });
            }
        });

        // Add loading animation for images
        document.addEventListener('DOMContentLoaded', function () {
            const images = document.querySelectorAll('.room-img');
            images.forEach(img => {
                // Set initial opacity to 0
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.3s ease';

                // Handle successful load
                img.addEventListener('load', function () {
                    this.style.opacity = '1';
                    // Hide loading placeholder
                    const loadingPlaceholder = this.parentElement.querySelector('.image-loading');
                    if (loadingPlaceholder) {
                        loadingPlaceholder.style.display = 'none';
                    }
                });

                // Handle error - set fallback image
                img.addEventListener('error', function () {
                    console.log('Image failed to load:', this.src);
                    this.src = 'assets/images/hotel.jpg';
                    this.style.opacity = '1';
                    // Hide loading placeholder
                    const loadingPlaceholder = this.parentElement.querySelector('.image-loading');
                    if (loadingPlaceholder) {
                        loadingPlaceholder.style.display = 'none';
                    }
                });

                // If image is already loaded (cached), show it immediately
                if (img.complete) {
                    img.style.opacity = '1';
                    // Hide loading placeholder
                    const loadingPlaceholder = img.parentElement.querySelector('.image-loading');
                    if (loadingPlaceholder) {
                        loadingPlaceholder.style.display = 'none';
                    }
                }
            });
        });
    </script>
</body>

</html>