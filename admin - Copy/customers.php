<?php
session_start();
include_once '../config/configdatabse.php';

// Fetch all customers
$customers = [];
$sql = "SELECT * FROM Customers ORDER BY registered_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Fetch booking counts for each customer (optional: can be optimized with a JOIN)
$booking_counts = [];
$booking_sql = "SELECT customer_id, COUNT(*) as bookings FROM Reservations GROUP BY customer_id";
$booking_result = $conn->query($booking_sql);
if ($booking_result && $booking_result->num_rows > 0) {
    while ($row = $booking_result->fetch_assoc()) {
        $booking_counts[$row['customer_id']] = $row['bookings'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customers - Admin | Hotel System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/customersinfo.css">
    <link rel="stylesheet" href="adminsidebar.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="content">
    <div class="header">
        <h2><i class="bi bi-person-lines-fill me-2"></i>Customer Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="bi bi-plus-circle me-2"></i>Add Customer
        </button>
    </div>
    
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Bookings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($customers) > 0): ?>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="customer-avatar">
                                    <?php if (!empty($customer['profile_pic'])): ?>
                                        <img src="../uploads/profile_pics/<?php echo htmlspecialchars($customer['profile_pic']); ?>" alt="Profile" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                                    <?php else: ?>
                                        <i class="bi bi-person-fill"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($customer['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($customer['number']); ?></td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <?php echo isset($booking_counts[$customer['id']]) ? $booking_counts[$customer['id']] : 0; ?> bookings
                            </span>
                        </td>
                        <td>
                            <button class="action-btn me-2 view-btn"
                                data-id="<?php echo $customer['id']; ?>"
                                data-name="<?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>"
                                data-email="<?php echo htmlspecialchars($customer['email']); ?>"
                                data-number="<?php echo htmlspecialchars($customer['number']); ?>"
                                data-dob="<?php echo htmlspecialchars($customer['dob']); ?>"
                                data-gender="<?php echo htmlspecialchars($customer['gender']); ?>"
                                data-province="<?php echo htmlspecialchars($customer['province']); ?>"
                                data-district="<?php echo htmlspecialchars($customer['district']); ?>"
                                data-city="<?php echo htmlspecialchars($customer['city']); ?>"
                                data-profile="<?php echo htmlspecialchars($customer['profile_pic']); ?>"
                                data-registered="<?php echo htmlspecialchars($customer['registered_at']); ?>"
                                data-bookings="<?php echo isset($booking_counts[$customer['id']]) ? $booking_counts[$customer['id']] : 0; ?>"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="action-btn delete-btn" data-id="<?php echo $customer['id']; ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No customers found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing <span class="fw-bold">1</span> to <span class="fw-bold">3</span> of <span class="fw-bold">15</span> customers
            </div>
            <nav>
                <ul class="pagination mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="customerName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="customerName" placeholder="Enter full name">
                    </div>
                    <div class="mb-3">
                        <label for="customerEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="customerEmail" placeholder="Enter email">
                    </div>
                    <div class="mb-3">
                        <label for="customerPhone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="customerPhone" placeholder="Enter phone number">
                    </div>
                    <div class="mb-3">
                        <label for="customerAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="customerAddress" rows="2" placeholder="Enter address"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Add Customer</button>
            </div>
        </div>
    </div>
</div>

<!-- View Customer Modal -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-person-lines-fill me-2"></i>Customer Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="customerDetailsBody">
        <!-- Details will be loaded here -->
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View button
    document.querySelectorAll('.view-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const profilePic = btn.getAttribute('data-profile');
            let profileHtml = '';
            if (profilePic) {
                profileHtml = `<img src="../uploads/profile_pics/${profilePic}" alt="Profile" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:10px;">`;
            } else {
                profileHtml = `<div class="customer-avatar" style="width:80px;height:80px;font-size:2.5rem;display:flex;align-items:center;justify-content:center;margin-bottom:10px;"><i class="bi bi-person-fill"></i></div>`;
            }
            document.getElementById('customerDetailsBody').innerHTML = `
                <div class="text-center mb-3">${profileHtml}</div>
                <p><strong>Name:</strong> ${btn.getAttribute('data-name')}</p>
                <p><strong>Email:</strong> ${btn.getAttribute('data-email')}</p>
                <p><strong>Phone:</strong> ${btn.getAttribute('data-number')}</p>
                <p><strong>DOB:</strong> ${btn.getAttribute('data-dob')}</p>
                <p><strong>Gender:</strong> ${btn.getAttribute('data-gender')}</p>
                <p><strong>Address:</strong> ${btn.getAttribute('data-province')}, ${btn.getAttribute('data-district')}, ${btn.getAttribute('data-city')}</p>
                <p><strong>Registered At:</strong> ${btn.getAttribute('data-registered')}</p>
                <p><strong>Bookings:</strong> ${btn.getAttribute('data-bookings')}</p>
            `;
            var modal = new bootstrap.Modal(document.getElementById('viewCustomerModal'));
            modal.show();
        });
    });

    // Delete button
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const customerId = btn.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
                fetch('delete_customer.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'id=' + encodeURIComponent(customerId)
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'success') {
                        // Remove the row
                        btn.closest('tr').remove();
                    } else {
                        alert('Failed to delete customer: ' + data);
                    }
                })
                .catch(err => alert('Error: ' + err));
            }
        });
    });
});
</script>
</body>
</html>