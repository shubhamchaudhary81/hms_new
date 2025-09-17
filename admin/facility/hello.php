<?php
// session_start();
// include_once '../../config/configdatabse.php';

// $headerTitle = "Swimming Pool Charges";
// $headerSubtitle = "Manage and charge guests for facility usage";
// $buttonText = "Back to Facilities";
// $buttonLink = "../facilities.php";
// $showButton = true;

// $success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
// unset($_SESSION['success_message']);

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     $guest_id = intval($_POST['guest_id']);
//     $charge = floatval($_POST['charge']);

//     if ($guest_id > 0 && $charge > 0) {
//         $stmt = $conn->prepare("INSERT INTO GuestCharges (guest_id, facility, amount) VALUES (?, 'Pool', ?)");
//         $stmt->bind_param("id", $guest_id, $charge);
//         if ($stmt->execute()) {
//             $_SESSION['success_message'] = "Charge added for guest ID $guest_id.";
//             header("Location: pool.php");
//             exit();
//         }
//     }
// }


session_start();
include_once '../../config/configdatabse.php';

$headerTitle = "Swimming Pool Charges";
$headerSubtitle = "Manage and charge guests for facility usage";
$buttonText = "Back to Facilities";
$buttonLink = "../facilities.php";
$showButton = true;

$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guest_id   = intval($_POST['guest_id']);
    $booking_id = intval($_POST['booking_id']);
    $charge     = floatval($_POST['charge']);

    if ($guest_id > 0 && $booking_id > 0 && $charge > 0) {
        $stmt = $conn->prepare("INSERT INTO GuestCharges (guest_id, booking_id, facility, amount) VALUES (?, ?, 'Pool', ?)");
        $stmt->bind_param("iid", $guest_id, $booking_id, $charge);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Pool charge added for guest ID $guest_id (Booking ID $booking_id).";
            header("Location: pool.php");
            exit();
        }
    }
}

// Fetch guest names + room numbers for autocomplete
$guests = [];
$query = "
    SELECT 
        c.id AS guest_id,
        CONCAT(c.first_name, ' ', c.last_name) AS guest_name,
        r.room_number,
        b.booking_id
    FROM Customers c
    JOIN Reservations res ON c.id = res.customer_id
    JOIN Bookings b ON res.reservation_id = b.reservation_id
    JOIN Room r ON b.room_id = r.room_id
    WHERE b.status = 'Active'
";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $guests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swimming Pool Charges | Resort Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin/content.css">
    <style>
        :root {
            --primary-color: #8b7355;
            --primary-light: #a89276;
            --primary-dark: #6c5a43;
            --accent-color: #4a8bb5;
            --light-bg: #f8f6f3;
            --text-color: #333333;
            --text-light: #777777;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .autocomplete-suggestions {
            position: absolute;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            width: 100%;
        }

        .autocomplete-suggestion {
            padding: 8px 12px;
            cursor: pointer;
        }

        .autocomplete-suggestion:hover {
            background: #f1f1f1;
        }

        /* Sidebar + Content wrapper */
        .page-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-left: 290px;
            margin-top: 10px;
            padding: 20px;
        }

        /* --- ONLY page-specific elements below --- */

        /* Card form */
        .card-form {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
            background: #fff;
        }

        .card-form .card-header {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-color) 100%);
            color: white;
            padding: 1.2rem 1.5rem;
            border-bottom: none;
        }

        .card-form .card-body {
            padding: 2rem;
        }

        /* Form elements */
        .card-form .form-label {
            font-weight: 500;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }

        .card-form .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .card-form .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(139, 115, 85, 0.2);
        }

        .card-form .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .card-form .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Alerts */
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: none;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        /* Info box */
        .info-box {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            border-left: 4px solid var(--accent-color);
        }

        .info-box h5 {
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }

        .info-box p {
            color: var(--text-light);
            margin-bottom: 0;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 3rem;
            padding: 1.5rem 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <?php include_once '../sidebar.php'; ?>

    <div class="page-container">
        <?php include '../header-content.php'; ?>

        <div class="container-fluid">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card card-form">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-receipt pool-icon"></i> Add New Pool Charge</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <!-- Guest Name Autocomplete -->
                            <div class="col-md-6 mb-3 position-relative">
                                <label class="form-label">Guest Name</label>
                                <input type="text" id="guestName" class="form-control" placeholder="Type guest name..."
                                    autocomplete="off" required>
                                <div id="guestList" class="autocomplete-suggestions"></div>
                            </div>

                            <!-- Charge Amount -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Charge Amount</label>
                                <input type="number" name="charge" class="form-control" step="0.01" required min="0.01">
                            </div>
                        </div>

                        <!-- Hidden Fields -->
                        <input type="hidden" name="guest_id" id="guestId">
                        <input type="hidden" name="booking_id" id="bookingId">

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-plus-circle me-2"></i>
                                Add Charge</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="footer text-center mt-4">
                <p>© <?php echo date('Y'); ?> Resort Management System | Swimming Pool Charges</p>
            </div>
        </div>
    </div>

    <script>
        const guests = <?= json_encode($guests); ?>;
        const guestNameInput = document.getElementById("guestName");
        const guestList = document.getElementById("guestList");
        const guestIdField = document.getElementById("guestId");
        const bookingIdField = document.getElementById("bookingId");

        guestNameInput.addEventListener("input", function () {
            const query = this.value.toLowerCase();
            guestList.innerHTML = "";
            if (query.length > 1) {
                const matches = guests.filter(g => g.guest_name.toLowerCase().includes(query));
                matches.forEach(g => {
                    const div = document.createElement("div");
                    div.classList.add("autocomplete-suggestion");
                    div.textContent = `${g.guest_name} (Room ${g.room_number})`;
                    div.onclick = function () {
                        guestNameInput.value = g.guest_name;
                        guestIdField.value = g.guest_id;
                        bookingIdField.value = g.booking_id;
                        guestList.innerHTML = "";
                    };
                    guestList.appendChild(div);
                });
            }
        });
    </script>
</body>

</html>