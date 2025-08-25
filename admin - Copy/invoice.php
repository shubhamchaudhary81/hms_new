<?php
session_start();
include_once '../config/configdatabse.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .table thead th { background: #5d4037; color: #fff; }
        .table tbody tr:hover { background: #f1f5fb; }
        .badge-status { font-size: 0.95rem; padding: 0.45em 1.1em; border-radius: 8px; font-weight: 600; }
        .badge-success { background: #4caf50; color: #fff; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #e53935; color: #fff; }
        .invoice-title { font-size: 2rem; color: #3a5a78; font-weight: 700; margin-bottom: 2rem; }
        .table td, .table th { vertical-align: middle !important; }
        .btn-view { font-weight: 600; letter-spacing: 0.5px; }
        .room-info { color: #6c757d; font-size: 0.97rem; }
        .customer-info { color: #495057; font-size: 1rem; font-weight: 500; }
        @media (max-width: 900px) {
            .invoice-title { font-size: 1.3rem; }
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
                <div class="invoice-title"><i class="fas fa-file-invoice-dollar me-2"></i>Invoices</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Room</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sql = "SELECT i.*, CONCAT(c.first_name, ' ', c.last_name) AS customer_name, c.email, c.number, b.room_id, rm.room_number, rt.room_type_name
                                FROM invoices i
                                LEFT JOIN Customers c ON i.customer_id = c.id
                                LEFT JOIN Bookings b ON i.booking_id = b.booking_id
                                LEFT JOIN Room rm ON b.room_id = rm.room_id
                                LEFT JOIN RoomType rt ON rm.room_type = rt.room_type_id
                                ORDER BY i.invoice_date DESC";
                        $res = $conn->query($sql);
                        if ($res && $res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold">#<?= htmlspecialchars($row['invoice_id']) ?></td>
                                    <td>
                                        <div class="customer-info"> <?= htmlspecialchars($row['customer_name'] ?? 'Unknown') ?> </div>
                                        <div class="room-info"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($row['email'] ?? '-') ?> <span class="ms-2"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($row['number'] ?? '-') ?></span></div>
                                    </td>
                                    <td>
                                        <?php if ($row['room_number']): ?>
                                            <span class="fw-bold">Room <?= htmlspecialchars($row['room_number']) ?></span><br>
                                            <span class="room-info">(<?= htmlspecialchars($row['room_type_name']) ?>)</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($row['invoice_date'])) ?></td>
                                    <td class="fw-bold">रु<?= number_format($row['total_amount'],2) ?></td>
                                    <td class="text-green">रु<?= number_format($row['amount_paid'] ?? 0,2) ?></td>
                                    <td class="text-red">रु<?= number_format($row['balance_due'] ?? 0,2) ?></td>
                                    <td>
                                        <?php if ($row['balance_due'] > 0): ?>
                                            <span class="badge badge-warning badge-status">Partial</span>
                                        <?php else: ?>
                                            <span class="badge badge-success badge-status">Paid</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="invoice_detail.php?invoice_id=<?= urlencode($row['invoice_id']) ?>" class="btn btn-primary btn-sm btn-view">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                        <?php endwhile;
                        } else {
                            echo '<tr><td colspan="9">No invoices found or error: ' . htmlspecialchars($conn->error) . '</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html> 