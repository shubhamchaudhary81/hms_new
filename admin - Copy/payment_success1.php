<?php
if (!isset($_GET['booking_id'])) { die('Booking ID missing.'); }
$booking_id = intval($_GET['booking_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful | Himalaya Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .success-animation {
            width: 120px; height: 120px; margin: 40px auto 20px auto;
            position: relative;
        }
        .checkmark {
            width: 120px; height: 120px; border-radius: 50%; background: #4caf50; position: absolute; left: 0; top: 0;
            display: flex; align-items: center; justify-content: center;
            animation: pop 0.6s cubic-bezier(.8,2,0.6,1.5);
        }
        @keyframes pop { 0% { transform: scale(0.5); } 100% { transform: scale(1); } }
        .checkmark svg { width: 60px; height: 60px; stroke: #fff; stroke-width: 6; stroke-linecap: round; stroke-linejoin: round; fill: none; animation: draw 0.7s 0.2s forwards; stroke-dasharray: 48; stroke-dashoffset: 48; }
        @keyframes draw { to { stroke-dashoffset: 0; } }
        .success-text { text-align: center; font-size: 2rem; color: #388e3c; font-weight: 700; margin-bottom: 1rem; }
        .btn-invoice { display: block; margin: 30px auto 0 auto; font-size: 1.2rem; padding: 12px 32px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="success-animation">
            <div class="checkmark">
                <svg viewBox="0 0 52 52">
                    <polyline points="14,27 22,35 38,17" />
                </svg>
            </div>
        </div>
        <div class="success-text">Payment Successful!</div>
        <div class="text-center">
            <a href="invoice.php?booking_id=<?= $booking_id ?>" class="btn btn-success btn-invoice">Generate & View Invoice</a>
        </div>
    </div>
</body>
</html> 