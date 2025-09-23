<?php
include_once '../config/configdatabse.php';

$limit = 10; // rows per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page-1)*$limit;

// Fetch total records
$total_res = $conn->query("SELECT COUNT(*) as total FROM RoomService");
$total_row = $total_res->fetch_assoc();
$total_pages = ceil($total_row['total'] / $limit);

// Fetch paginated data
$res = $conn->query("SELECT * FROM RoomService ORDER BY created_at ASC LIMIT $offset, $limit");

echo '<table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Service Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Availability</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

while($row = $res->fetch_assoc()){
    echo '<tr>
            <td>'.$row['room_service_id'].'</td>
            <td>'.$row['service_name'].'</td>
            <td>'.$row['description'].'</td>
            <td>'.$row['price'].'</td>
            <td>'.$row['availability_status'].'</td>
            <td>'.$row['created_at'].'</td>
            <td>
                <button class="action-btn edit" data-id="'.$row['room_service_id'].'"><i class="fas fa-edit"></i></button>
                <button class="action-btn delete" data-id="'.$row['room_service_id'].'"><i class="fas fa-trash"></i></button>
            </td>
        </tr>';
}

echo '</tbody></table>';

// Pagination
echo '<div class="pagination">';
for($i=1;$i<=$total_pages;$i++){
    echo '<a href="#" data-page="'.$i.'">'.$i.'</a> ';
}
echo '</div>';
?>
