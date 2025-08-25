<?php
session_start();
// Optionally, check if admin is logged in here
include_once '../config/configdatabse.php';

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'], $_POST['action'])) {
    $review_id = intval($_POST['review_id']);
    $action = $_POST['action'];
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE Reviews SET is_approved = 1 WHERE review_id = ?");
        $stmt->bind_param('i', $review_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE Reviews SET is_approved = -1 WHERE review_id = ?");
        $stmt->bind_param('i', $review_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: reviews.php');
    exit;
}

// Fetch all reviews with customer and room info
$sql = "SELECT r.*, CONCAT(c.first_name, ' ', c.last_name) AS customer_name, c.email, c.number, rm.room_number, rt.room_type_name, b.actual_check_in, b.actual_check_out, b.booking_id
        FROM Reviews r
        JOIN Customers c ON r.customer_id = c.id
        JOIN Bookings b ON r.booking_id = b.booking_id
        JOIN Room rm ON r.room_id = rm.room_id
        JOIN RoomType rt ON rm.room_type = rt.room_type_id
        ORDER BY r.review_date DESC";
$res = $conn->query($sql);
if (!$res) {
    $sql = str_replace('Customers', 'Customers', $sql);
    $res = $conn->query($sql);
    if (!$res) {
        die("SQL Error: " . $conn->error);
    }
}
$reviews = [];
while ($row = $res->fetch_assoc()) $reviews[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .reviews-title { font-size: 2rem; color: #3a5a78; font-weight: 700; margin-bottom: 2rem; letter-spacing: 1px; }
        .table thead th { background: #5d4037; color: #fff; }
        .table tbody tr:hover { background: #f1f5fb; }
        .badge-success { background: #4caf50; color: #fff; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #e53935; color: #fff; }
        .badge-pending { background: #b0b0b0; color: #fff; }
        .star-rating .fa-star { color: #d4af37; }
        .star-rating { font-size: 1.1rem; }
        .btn-action { font-weight: 600; letter-spacing: 0.5px; margin-bottom: 2px; }
        .customer-info { color: #495057; font-size: 1rem; font-weight: 500; }
        .room-info { color: #6c757d; font-size: 0.97rem; }
        .review-id { color: #888; font-size: 0.95rem; }
        .comment-cell { max-width: 260px; white-space: pre-line; word-break: break-word; }
        @media (max-width: 900px) {
            .reviews-title { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 p-0">
                <?php include 'sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <div class="py-5">
                    <div class="reviews-title"><i class="fas fa-star me-2"></i>Customer Reviews</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Review #</th>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Room</th>
                                    <th>Stay</th>
                                    <th>Booking #</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($reviews as $r): ?>
                                <tr>
                                    <td class="review-id">#<?= htmlspecialchars($r['review_id']) ?></td>
                                    <td><div class="customer-info"> <?= htmlspecialchars($r['customer_name']) ?> </div></td>
                                    <td>
                                        <div class="room-info"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($r['email'] ?? '-') ?></div>
                                        <div class="room-info"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($r['number'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <span class="fw-bold">Room <?= htmlspecialchars($r['room_number']) ?></span><br>
                                        <span class="room-info">(<?= htmlspecialchars($r['room_type_name']) ?>)</span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($r['actual_check_in'])) ?> <div class="text-muted small">to <?= date('M d, Y', strtotime($r['actual_check_out'])) ?></div></td>
                                    <td class="fw-bold">#<?= htmlspecialchars($r['booking_id']) ?></td>
                                    <td>
                                        <div class="star-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa<?= $i <= $r['rating'] ? 's' : 'r' ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </td>
                                    <td class="comment-cell"><?= htmlspecialchars($r['comment']) ?: '<span class="text-muted">No comment</span>' ?></td>
                                    <td>
                                        <?php if ($r['is_approved'] == 0): ?>
                                            <span class="badge badge-pending">Pending</span>
                                        <?php elseif ($r['is_approved'] == 1): ?>
                                            <span class="badge badge-success">Approved</span>
                                        <?php elseif ($r['is_approved'] == -1): ?>
                                            <span class="badge badge-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($r['review_date'])) ?><div class="text-muted small"><?= date('h:i A', strtotime($r['review_date'])) ?></div></td>
                                    <td>
                                        <?php if ($r['is_approved'] == 0): ?>
                                        <form method="post" style="display:inline-block">
                                            <input type="hidden" name="review_id" value="<?= $r['review_id'] ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm btn-action"><i class="fa fa-check"></i> Approve</button>
                                        </form>
                                        <form method="post" style="display:inline-block">
                                            <input type="hidden" name="review_id" value="<?= $r['review_id'] ?>">
                                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm btn-action"><i class="fa fa-times"></i> Reject</button>
                                        </form>
                                        <?php elseif ($r['is_approved'] == 1): ?>
                                            <span class="text-success"><i class="fa fa-check"></i></span>
                                        <?php elseif ($r['is_approved'] == -1): ?>
                                            <span class="text-danger"><i class="fa fa-times"></i></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 