<?php
include_once '../config/configdatabse.php';

$limit = 15;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch total records
$total_res = $conn->query("SELECT COUNT(*) as total FROM MenuItems");
$total_row = $total_res->fetch_assoc();
$total_pages = ceil($total_row['total'] / $limit);

// Fetch paginated data
$res = $conn->query("SELECT * FROM MenuItems ORDER BY created_at ASC LIMIT $offset, $limit");

echo '<table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Type</th>
                <th>Category</th>
                <th>Available</th>
                <th>Image</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

$i=1;
while ($row = $res->fetch_assoc()) {
    // $imagePath = $row['menu_image'];
    // $imageSrc = $row['menu_image'] && file_exists($imagePath) ? 'uploads/menu_items/' . $row['menu_image'] : 'https://via.placeholder.com/50';

    echo '<tr>
            <td>' . $i++ . '</td>
            <td>' . $row['item_name'] . '</td>
            <td>' . $row['item_description'] . '</td>
            <td>' . $row['price'] . '</td>
            <td>' . $row['item_type'] . '</td>
            <td>' . $row['category'] . '</td>
            <td>' . ($row['is_available'] ? 'Yes' : 'No') . '</td>
            <td><img src="' . $row['menu_image'] . '" alt="Menu Image" style="height:5rem; width:6rem; border-radius:5px;object-fit:fill;"></td>
            <td>' . $row['created_at'] . '</td>
            <td>
                <button class="action-btn edit" data-id="' . $row['menu_item_id'] . '"><i class="fas fa-edit"></i></button>
                <button class="action-btn delete" data-id="' . $row['menu_item_id'] . '"><i class="fas fa-trash"></i></button>
            </td>
        </tr>';
}

echo '</tbody></table>';

// Pagination
echo '<div class="pagination">';
for ($i = 1; $i <= $total_pages; $i++) {
    echo '<a href="#" data-page="' . $i . '">' . $i . '</a> ';
}
echo '</div>';
?>
