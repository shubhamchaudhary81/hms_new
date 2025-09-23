<?php
include_once '../config/configdatabse.php';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Count total rows
$totalResult = $conn->query("SELECT COUNT(*) as total FROM Amenity");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch amenities
$sql = "SELECT * FROM Amenity ORDER BY created_at ASC LIMIT $offset, $limit";
$result = $conn->query($sql);
?>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Amenity Name</th>
                <th>Description</th>
                <th>Icon</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['amenity_id'] ?></td>
                        <td><?= htmlspecialchars($row['amenity_name']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td>
                            <?php if($row['icon_url']): ?>
                                <img src="<?= $row['icon_url'] ?>" alt="Icon" style="width:24px;height:24px;">
                            <?php endif; ?>
                        </td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <button class="action-btn edit" data-id="<?= $row['amenity_id'] ?>"><i class="fas fa-edit"></i></button>
                            <button class="action-btn delete" data-id="<?= $row['amenity_id'] ?>"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No amenities found.</td></tr>
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
