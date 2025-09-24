<?php
include_once '../config/configdatabse.php';

$limit = 15;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// Total records
$total_sql = "SELECT COUNT(*) AS total FROM RoomType";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch paginated records
$sql = "SELECT * FROM RoomType ORDER BY created_at ASC LIMIT $offset, $limit";
$result = $conn->query($sql);
?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Room Type</th>
            <th>Description</th>
            <!-- <th>Capacity</th>
            <th>Base Price</th> -->
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php
            $i = 1;
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['room_type_name']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <!-- <td><?= htmlspecialchars($row['capacity']) ?></td>
                    <td><?= htmlspecialchars($row['base_price']) ?></td> -->
                    <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                    <td>
                        <button class="action-btn edit" data-id="<?= $row['room_type_id'] ?>"><i
                                class="fas fa-edit"></i></button>
                        <button class="action-btn delete" data-id="<?= $row['room_type_id'] ?>"><i
                                class="fas fa-trash"></i></button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No room types found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Pagination -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="#" data-page="<?= $page - 1 ?>">Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="#" data-page="<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="#" data-page="<?= $page + 1 ?>">Next</a>
    <?php endif; ?>
</div>