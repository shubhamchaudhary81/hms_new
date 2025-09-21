<?php
include_once '../config/configdatabse.php';

$headerTitle = "Room Type";
$headerSubtitle = "See all room types available in the system.";
$buttonText = "Add New Room Type ";
$buttonLink = "add-room-type.php";
$showButton = true;

// Pagination setup
$limit = 15; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;
// Fetch total records
$total_sql = "SELECT COUNT(*) AS total FROM RoomType";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch paginated records
$sql = "SELECT * FROM RoomType ORDER BY created_at ASC LIMIT " . (int)$offset . ", " . (int)$limit;
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Types Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin/content.css">
    <link rel="stylesheet" href="../css/admin/room-type.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header-content.php'; ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Room Type</th>
                        <th>Description</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['room_type_id']) ?></td>
                                <td><?= htmlspecialchars($row['room_type_name']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['created_at']) ?></td>
                                <td>
                                    <button class="action-btn edit" data-id="<?= $row['room_type_id'] ?>"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete" data-id="<?= $row['room_type_id'] ?>"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No room types found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Room Type Modal -->
    <div class="modal" id="roomTypeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Room Type</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="roomTypeForm">
                    <div class="form-group">
                        <label for="roomTypeName">Room Type Name</label>
                        <input type="text" id="roomTypeName" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <input type="number" id="capacity" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="basePrice">Base Price ($)</label>
                        <input type="number" id="basePrice" step="0.01" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button class="btn btn-primary" id="saveBtn">Save Room Type</button>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('roomTypeModal');
        const addRoomTypeBtn = document.getElementById('addRoomTypeBtn');
        const closeBtn = document.querySelector('.close');
        const cancelBtn = document.getElementById('cancelBtn');
        const saveBtn = document.getElementById('saveBtn');

        addRoomTypeBtn.addEventListener('click', () => {
            document.getElementById('modalTitle').textContent = 'Add New Room Type';
            modal.style.display = 'flex';
        });

        const closeModal = () => {
            modal.style.display = 'none';
            document.getElementById('roomTypeForm').reset();
        };

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        saveBtn.addEventListener('click', () => {
            // Here you would typically save the data to the database
            alert('Room type saved successfully!');
            closeModal();
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Edit and Delete buttons functionality
        const editButtons = document.querySelectorAll('.action-btn.edit');
        const deleteButtons = document.querySelectorAll('.action-btn.delete');

        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('modalTitle').textContent = 'Edit Room Type';
                // In a real application, you would populate the form with existing data
                modal.style.display = 'flex';
            });
        });

        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (confirm('Are you sure you want to delete this room type?')) {
                    // In a real application, you would delete the record from the database
                    alert('Room type deleted successfully!');
                }
            });
        });

        // Pagination buttons
        const paginationButtons = document.querySelectorAll('.pagination button');
        paginationButtons.forEach(button => {
            button.addEventListener('click', () => {
                paginationButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
            });
        });
    </script>
</body>

</html>