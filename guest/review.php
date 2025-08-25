<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include_once '../config/configdatabse.php';
if (!empty($error)) {
    echo '<div style="background: #ffdddd; color: #a00; padding: 10px; border: 1px solid #a00; margin-bottom: 10px; font-weight: bold;">DEBUG ERROR: ' . htmlspecialchars($error) . '</div>';
}
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login.php');
    exit;
}
$customer_id = $_SESSION['customer_id'];
$success = false;
$error = '';
// Fetch all eligible bookings (not already reviewed)
$bookings = [];
$stmt = $conn->prepare("SELECT b.booking_id, b.room_id, r.requested_check_in_date, r.requested_check_out_date, rm.room_number, rt.room_type_name FROM Bookings b JOIN Reservations r ON b.reservation_id = r.reservation_id JOIN Room rm ON b.room_id = rm.room_id JOIN RoomType rt ON rm.room_type = rt.room_type_id WHERE r.customer_id = ? AND b.status IN ('checked_out', 'completed', 'confirmed') AND b.booking_id NOT IN (SELECT booking_id FROM Reviews WHERE customer_id = ?) ORDER BY b.actual_check_out DESC, b.actual_check_in DESC");
$stmt->bind_param('ii', $customer_id, $customer_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $bookings[] = $row;
$stmt->close();
// Fetch guest's existing reviews
$reviews = [];
$rev_stmt = $conn->prepare("SELECT r.*, rm.room_number, rt.room_type_name, b.actual_check_in, b.actual_check_out FROM Reviews r JOIN Bookings b ON r.booking_id = b.booking_id JOIN Room rm ON b.room_id = rm.room_id JOIN RoomType rt ON rm.room_type = rt.room_type_id WHERE r.customer_id = ? ORDER BY r.review_date DESC");
$rev_stmt->bind_param('i', $customer_id);
$rev_stmt->execute();
$rev_res = $rev_stmt->get_result();
while ($row = $rev_res->fetch_assoc()) $reviews[] = $row;
$rev_stmt->close();
// MySQL connection check
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $room_id = intval($_POST['room_id'] ?? 0);
    // Debug: Output POST data and parsed values
    echo '<pre style="background:#eee;padding:10px;">';
    echo "POST: "; print_r($_POST);
    echo "\nCID: $customer_id\nBID: $booking_id\nRID: $room_id\nRating: $rating\nComment: $comment\n";
    echo '</pre>';
    if (!$room_id) {
        $error = 'Room ID was not submitted. Please ensure JavaScript is enabled or try again.';
    }
    // Prevent duplicate review
    $dup_stmt = $conn->prepare("SELECT review_id FROM Reviews WHERE customer_id = ? AND booking_id = ?");
    $dup_stmt->bind_param('ii', $customer_id, $booking_id);
    $dup_stmt->execute();
    $dup_stmt->store_result();
    if ($dup_stmt->num_rows > 0) {
        $error = 'You have already reviewed this stay.';
    } elseif ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating.';
    } elseif (!$booking_id || !$room_id) {
        $error = 'Booking or room not found.';
    } else {
        $ins = $conn->prepare("INSERT INTO Reviews (customer_id, booking_id, room_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
        if (!$ins) {
            die("Prepare failed: " . $conn->error);
        }
        $ins->bind_param('iiiis', $customer_id, $booking_id, $room_id, $rating, $comment);
        if ($ins->execute()) {
            $success = true;
        } else {
            die("Execution failed: " . $ins->error);
        }
        $ins->close();
    }
    $dup_stmt->close();
    // Only redirect if success
    if ($success) {
        header('Location: review.php?success=1');
        exit;
    }
}
if (isset($_GET['success']) && $_GET['success'] == '1') $success = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave a Review | Himalaya Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3a5a78; /* Deep navy blue */
            --secondary-color: #8b6b4a; /* Warm bronze */
            --accent-color: #d4af37; /* Gold accent */
            --light-color: #f8f5f2; /* Warm off-white */
            --dark-color: #2c3e50;
            --success-color: #5a8f7b; /* Muted teal */
            --warning-color: #c77e23; /* Burnt orange */
            --text-color: #333333;
            --text-light: #6c757d;
        }
        
        body {
            background-color: var(--light-color);
            font-family: 'Montserrat', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            padding-top: 2rem;
            padding-bottom: 4rem;
        }
        
        .page-header {
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(139, 107, 74, 0.2);
        }
        
        .page-title {
            font-weight: 700;
            color: var(--primary-color);
            position: relative;
            display: inline-block;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--accent-color);
        }
        
        .page-subtitle {
            color: var(--text-light);
            font-weight: 300;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
            background: white;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }
        
        .card-header h4 {
            color: white;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .card-header i {
            margin-right: 12px;
            color: var(--accent-color);
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .star-rating {
            font-size: 2rem;
            color: var(--accent-color);
            cursor: pointer;
            display: inline-block;
            margin: 0.5rem 0;
        }
        
        .star-rating .fa-star {
            transition: all 0.2s ease;
            margin: 0 3px;
        }
        
        .star-rating .fa-star:hover, 
        .star-rating .fa-star.active {
            transform: scale(1.2);
            text-shadow: 0 0 8px rgba(212, 175, 55, 0.4);
        }
        
        .star-rating .fa-star-o { 
            color: #e0e0e0;
        }
        
        .rating-text {
            font-size: 0.9rem;
            color: var(--text-light);
            font-style: italic;
            margin-top: 0.5rem;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--warning-color) 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            border-radius: 50px;
            color: white;
            margin-top: 1rem;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 107, 74, 0.3);
            color: white;
        }
        
        .form-control, .form-select {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(139, 107, 74, 0.15);
            background-color: white;
        }
        
        textarea.form-control {
            min-height: 150px;
        }
        
        .alert {
            border-radius: 6px;
            padding: 1rem 1.25rem;
            border: none;
        }
        
        .alert-success {
            background-color: rgba(90, 143, 123, 0.15);
            color: var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(199, 126, 35, 0.15);
            color: var(--warning-color);
        }
        
        .table {
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 1rem 1.25rem;
            border: none;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(248, 245, 242, 0.7);
        }
        
        .table td {
            padding: 1.25rem;
            vertical-align: middle;
            border-color: #f0f0f0;
        }
        
        .badge {
            padding: 6px 10px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .badge-success {
            background-color: rgba(90, 143, 123, 0.15);
            color: var(--success-color);
        }
        
        .badge-warning {
            background-color: rgba(199, 126, 35, 0.15);
            color: var(--warning-color);
        }
        
        .no-reviews {
            padding: 3rem 2rem;
            text-align: center;
            background-color: rgba(248, 245, 242, 0.5);
            border-radius: 8px;
        }
        
        .no-reviews i {
            font-size: 3.5rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }
        
        .no-reviews h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .no-reviews p {
            color: var(--text-light);
            max-width: 500px;
            margin: 0 auto 1.5rem;
        }
        
        .comment-preview {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding-top: 1.5rem;
                padding-bottom: 2rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .table thead {
                display: none;
            }
            
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            
            .table tr {
                margin-bottom: 1.5rem;
                border: 1px solid #f0f0f0;
                border-radius: 8px;
                overflow: hidden;
            }
            
            .table td {
                padding: 0.75rem;
                text-align: right;
                position: relative;
                padding-left: 50%;
            }
            
            .table td:before {
                content: attr(data-label);
                position: absolute;
                left: 1rem;
                width: 45%;
                padding-right: 1rem;
                font-weight: 600;
                color: var(--primary-color);
                text-align: left;
            }
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="page-header text-center">
        <h1 class="page-title animate__animated animate__fadeInDown">Share Your Experience</h1>
        <p class="page-subtitle animate__animated animate__fadeIn animate__delay-1s">Your feedback helps us maintain our high standards of service</p>
    </div>
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card animate__animated animate__fadeIn">
                <div class="card-header">
                    <h4><i class="fas fa-pen-alt"></i>Leave a Review</h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success animate__animated animate__bounceIn">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-3"></i>
                                <div>
                                    <h5 class="mb-1">Thank you for your review!</h5>
                                    <p class="mb-0">We appreciate your feedback and will use it to improve our services.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if ($error): ?>
                        <div class="alert alert-danger animate__animated animate__shakeX">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (count($bookings) > 0): ?>
                        <form method="post" id="reviewForm">
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-bed"></i>Select Your Stay
                                </label>
                                <select name="booking_id" class="form-select" required onchange="updateRoomId(this)">
                                    <option value="">-- Select your stay --</option>
                                    <?php foreach ($bookings as $b): ?>
                                        <option value="<?= $b['booking_id'] ?>" data-room="<?= $b['room_id'] ?>">
                                            <?= htmlspecialchars($b['room_number']) ?> (<?= htmlspecialchars($b['room_type_name']) ?>) - <?= date('M d, Y', strtotime($b['requested_check_in_date'])) ?> to <?= date('M d, Y', strtotime($b['requested_check_out_date'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="room_id" id="roomIdInput" value="">
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-star"></i>Your Rating
                                </label>
                                <div class="text-center">
                                    <div class="star-rating" id="starRating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="far fa-star mx-1" data-value="<?= $i ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="rating-text" id="ratingText">Tap to rate</div>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" value="0">
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-comment"></i>Your Review
                                </label>
                                <textarea name="comment" class="form-control" rows="5" maxlength="1000" placeholder="Share your experience with us... What did you like? What could we improve?"></textarea>
                                <small class="text-muted">Your honest feedback helps us serve you better.</small>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-submit">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Review
                                </button>
                            </div>
                        </form>
                        <?php else: ?>
                            <div class="no-reviews animate__animated animate__fadeIn">
                                <i class="fas fa-calendar-check floating"></i>
                                <h4 class="mb-3">No Eligible Stays Found</h4>
                                <p class="text-muted">You don't have any completed stays available for review at this time.</p>
                                <a href="../index.php" class="btn btn-primary mt-3">
                                    <i class="fas fa-home me-2"></i>Back to Home
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card animate__animated animate__fadeIn animate__delay-1s">
                <div class="card-header">
                    <h4><i class="fas fa-clipboard-list"></i>Your Review History</h4>
                </div>
                <div class="card-body">
                    <?php if (count($reviews) === 0): ?>
                        <div class="no-reviews">
                            <i class="fas fa-comment-slash floating"></i>
                            <h4 class="mb-3">No Reviews Yet</h4>
                            <p class="text-muted">Your submitted reviews will appear here once you've shared your experiences with us.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Room</th>
                                        <th>Stay</th>
                                        <th>Rating</th>
                                        <th>Comment</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reviews as $r): ?>
                                        <tr>
                                            <td data-label="Room">
                                                <strong><?= htmlspecialchars($r['room_number']) ?></strong>
                                                <div class="text-muted small"><?= htmlspecialchars($r['room_type_name']) ?></div>
                                            </td>
                                            <td data-label="Stay">
                                                <?= date('M d, Y', strtotime($r['actual_check_in'])) ?>
                                                <div class="text-muted small">to <?= date('M d, Y', strtotime($r['actual_check_out'])) ?></div>
                                            </td>
                                            <td data-label="Rating">
                                                <div class="star-rating small" style="font-size: 1rem;">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas <?= $i <= $r['rating'] ? 'fa-star' : 'fa-star' ?>" style="color:<?= $i <= $r['rating'] ? 'var(--accent-color)' : '#e0e0e0' ?>;"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                            <td data-label="Comment">
                                                <?php if (!empty($r['comment'])): ?>
                                                    <div class="comment-preview" title="<?= htmlspecialchars($r['comment']) ?>">
                                                        <?= htmlspecialchars($r['comment']) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">No comment</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Status">
                                                <?php if ($r['is_approved']): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check me-1"></i>Approved
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Date">
                                                <?= date('M d, Y', strtotime($r['review_date'])) ?>
                                                <div class="text-muted small"><?= date('h:i A', strtotime($r['review_date'])) ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const stars = document.querySelectorAll('#starRating i');
const ratingInput = document.getElementById('ratingInput');
const ratingText = document.getElementById('ratingText');

const ratingLabels = [
    "Tap to rate",
    "Poor - Needs significant improvement",
    "Fair - Room for improvement",
    "Good - Met expectations",
    "Very Good - Exceeded expectations",
    "Excellent - Outstanding experience"
];

stars.forEach(star => {
    star.addEventListener('mouseover', function () {
        const val = parseInt(this.getAttribute('data-value'));
        highlightStars(val);
        ratingText.textContent = ratingLabels[val];
    });

    star.addEventListener('mouseout', function () {
        const val = parseInt(ratingInput.value);
        highlightStars(val);
        ratingText.textContent = val ? ratingLabels[val] : ratingLabels[0];
    });

    star.addEventListener('click', function () {
        const val = parseInt(this.getAttribute('data-value'));
        ratingInput.value = val;
        highlightStars(val);
        ratingText.textContent = ratingLabels[val];
        this.classList.add('animate__animated', 'animate__rubberBand');
        setTimeout(() => {
            this.classList.remove('animate__animated', 'animate__rubberBand');
        }, 1000);
    });
});

function highlightStars(count) {
    stars.forEach((star, index) => {
        if (index < count) {
            star.classList.remove('far');
            star.classList.add('fas', 'active');
        } else {
            star.classList.remove('fas', 'active');
            star.classList.add('far');
        }
    });
}

function updateRoomId(select) {
    const selectedOption = select.options[select.selectedIndex];
    const roomId = selectedOption.getAttribute('data-room');
    document.getElementById('roomIdInput').value = roomId || '';
}
</script>

</body>
</html>