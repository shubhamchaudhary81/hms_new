<?php
include_once '../config/configdatabse.php';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // items per page
$offset = ($page - 1) * $limit;

// Count total rows for pagination
$totalResult = $conn->query("SELECT COUNT(*) as total FROM ExtraServices");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch services
$sql = "SELECT * FROM ExtraServices ORDER BY created_at ASC LIMIT $offset, $limit";
$result = $conn->query($sql);
?>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Service Name</th>
                <th>Description</th>
                <th>Price (NRP)</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['service_id'] ?></td>
                    <td><?= htmlspecialchars($row['service_name']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= number_format($row['price'],2) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <button class="action-btn edit" data-id="<?= $row['service_id'] ?>"><i class="fas fa-edit"></i></button>
                        <button class="action-btn delete" data-id="<?= $row['service_id'] ?>"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No services found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php for($i=1; $i<=$totalPages; $i++): ?>
            <a href="#" data-page="<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>
