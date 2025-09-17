<?php
session_start();
include_once '../../config/configdatabse.php';

$headerTitle = "Swimming Pool Charges";
$headerSubtitle = "Manage and charge guests for facility usage";
$buttonText = "Back to Facilities";
$buttonLink = "../facilities.php";
$showButton = true;

$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guest_id = intval($_POST['guest_id']);
    $charge   = floatval($_POST['charge']);

    if ($guest_id > 0 && $charge > 0) {
        $stmt = $conn->prepare("INSERT INTO GuestCharges (guest_id, facility, amount) VALUES (?, 'Pool', ?)");
        $stmt->bind_param("id", $guest_id, $charge);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Charge added for guest ID $guest_id.";
            header("Location: pool.php");
            exit();
        }
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

      <div class="info-box">
        <h5><i class="fas fa-info-circle me-2"></i>Information</h5>
        <p>Enter the guest ID and the charge amount to add a pool charge to the guest's account.</p>
      </div>

      <div class="card card-form">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-receipt pool-icon"></i>Add New Charge</h5>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Guest ID</label>
                <input type="number" name="guest_id" class="form-control" required min="1">
                <div class="form-text">Enter the guest's identification number</div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Charge Amount ($)</label>
                <input type="number" name="charge" class="form-control" step="0.01" required min="0.01">
                <div class="form-text">Enter the amount to charge</div>
              </div>
            </div>
            <div class="text-center mt-4">
              <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-plus-circle me-2"></i>Add Charge</button>
            </div>
          </form>
        </div>
      </div>

      <div class="footer">
        <p>© <?php echo date('Y'); ?> Resort Management System | Swimming Pool Charges</p>
      </div>
    </div>
  </div>
</body>
</html>