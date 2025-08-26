<?php
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5 text-center">
        <h1 class="text-success mb-3">Payment Successful!</h1>
        <p class="mb-4">Your payment has been processed successfully.</p>
        <?php if ($booking_id): ?>
            <a href="generate_invoice.php?booking_id=<?= $booking_id ?>" class="btn btn-primary btn-lg">
                <i class="fa fa-file-invoice"></i> Generate & View Invoice
            </a>
        <?php else: ?>
            <a href="admin/invoice.php" class="btn btn-primary btn-lg">View All Invoices</a>
        <?php endif; ?>
    </div>
</body>
</html> 