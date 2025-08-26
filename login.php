<?php
session_start();
session_unset();
session_destroy();
include_once("config/configdatabse.php");

session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $userType = $_POST['userType'];
  $email = trim($_POST['email']);
  $password = $_POST['password'];

    if ($userType === "guest") {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Login success
                $_SESSION['customer_id'] = $row['id'];
                $_SESSION['customer_name'] = $row['first_name'] . " " . $row['last_name'];
                $_SESSION['customer_email'] = $row['email'];
                header("Location: guest/guestdash.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    } elseif ($userType === "admin") {
        $stmt = $conn->prepare("SELECT user_id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_id'] = $row['user_id'];
                $_SESSION['admin_name'] = $row['name'];
                $_SESSION['admin_email'] = $row['email'];
                header("Location: admin/dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    } elseif ($userType === "receptionist") {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM receptionist WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['receptionist_id'] = $row['id'];
                $_SESSION['receptionist_name'] = $row['name'];
                $_SESSION['receptionist_email'] = $row['email'];
                header("Location: receptionist/receptionistdash.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    } elseif ($userType === "manager") {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM manager WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['manager_id'] = $row['id'];
                $_SESSION['manager_name'] = $row['name'];
                $_SESSION['manager_email'] = $row['email'];
                header("Location: manager/managerdash.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    } else {
        $error = "Invalid user type.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Himalaya Hotel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/mainindex.css">
  <link rel="stylesheet" href="css/login.css">
  

</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1 class="login-title">Himalaya Hotel</h1>
        <p class="login-subtitle">Sign in to your account</p>
      </div>

      <div class="login-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="" method="POST">
          <div class="mb-4">
            <label for="userType" class="form-label">I am a</label>
            <select class="form-select" id="userType" name="userType" required>
              <option value="guest">
                <i class="bi bi-person user-type-icon"></i> Guest / Customer
              </option>
              <option value="admin">
                <i class="bi bi-shield-lock user-type-icon"></i> Admin
              </option>
              <option value="receptionist">
                <i class="bi bi-person-lines-fill user-type-icon"></i> Receptionist
              </option>
              <option value="manager">
                <i class="bi bi-person-gear user-type-icon"></i> Manager
              </option>
            </select>
          </div>

          <div class="mb-4">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent">
                <i class="bi bi-envelope" style="color: var(--accent);"></i>
              </span>
              <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent">
                <i class="bi bi-lock" style="color: var(--accent);"></i>
              </span>
              <input type="password" class="form-control" id="password" name="password"
                placeholder="Enter your password" required>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="rememberMe">
              <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>
            <a href="#" class="text-decoration-none" style="color: var(--accent); font-size: 0.9rem;">Forgot
              password?</a>
          </div>

          <button type="submit" class="btn btn-login mb-3">
            <i class="bi bi-box-arrow-in-right me-2"></i> Login
          </button>

          <div class="login-footer">
            New to Himalaya Hotel? <a href="guest/register.php">Create an account</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>