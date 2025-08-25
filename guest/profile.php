<?php
include_once '../config/configdatabse.php';
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login.php?redirect=profile');
    exit();
}
$customer_id = $_SESSION['customer_id'];
$customer = null;
$stmt = $conn->prepare("SELECT * FROM Customers WHERE id = ?");
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$stmt->close();
if (!$customer) { die('Customer not found.'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile | Himalaya Hotel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <!-- <style>
    :root {
      --primary-color: #5d4037;
      --secondary-color: #d4a762;
      --accent-color: #8d6e63;
      --light-bg: #fff9f5;
      --dark-text: #2a2a2a;
      --light-text: #f5f5f5;
      --shadow-sm: 0 2px 8px rgba(93,64,55,0.08);
      --shadow-md: 0 4px 16px rgba(93,64,55,0.12);
      --shadow-lg: 0 8px 32px rgba(93,64,55,0.16);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    
    body {
      font-family: 'Montserrat', sans-serif;
      background: var(--light-bg);
      color: var(--dark-text);
      line-height: 1.6;
    }
    
    h1, h2, h3, h4, h5 {
      font-family: 'Playfair Display', serif;
      font-weight: 700;
      letter-spacing: -0.5px;
    }
    
    .section-title {
      color: var(--primary-color);
      font-size: 2.5rem;
      margin-bottom: 2rem;
      position: relative;
      display: inline-block;
    }
    
    .section-title:after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 60px;
      height: 4px;
      background: var(--secondary-color);
      border-radius: 2px;
    }
    
    .profile-card {
      background: white;
      color: var(--primary-color);
      border-radius: 20px;
      box-shadow: var(--shadow-lg);
      padding: 40px;
      margin-bottom: 40px;
      position: relative;
      overflow: hidden;
      transition: var(--transition);
      border: none;
    }
    
    .profile-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(93,64,55,0.2);
    }
    
    .profile-card:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 8px;
      background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
    }
    
    .profile-icon {
      font-size: 3rem;
      color: var(--secondary-color);
      margin-bottom: 15px;
    }
    
    .form-label {
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 8px;
    }
    
    .form-control {
      border-radius: 10px;
      padding: 12px 15px;
      border: 1px solid rgba(93,64,55,0.1);
      transition: var(--transition);
    }
    
    .form-control:focus {
      border-color: var(--secondary-color);
      box-shadow: 0 0 0 0.25rem rgba(212, 167, 98, 0.25);
    }
    
    .btn-premium {
      background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
      color: white;
      border: none;
      border-radius: 50px;
      padding: 12px 30px;
      font-weight: 600;
      letter-spacing: 0.8px;
      transition: var(--transition);
      box-shadow: 0 4px 15px rgba(212, 167, 98, 0.4);
      text-transform: uppercase;
      font-size: 0.9rem;
    }
    
    .btn-premium:hover {
      background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(212, 167, 98, 0.6);
    }
    
    .profile-detail {
      margin-bottom: 12px;
      display: flex;
      align-items: center;
    }
    
    .profile-detail i {
      color: var(--secondary-color);
      margin-right: 10px;
      font-size: 1.1rem;
      width: 24px;
      text-align: center;
    }
    
    .profile-avatar {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid white;
      box-shadow: var(--shadow-md);
      margin-bottom: 20px;
    }
    
    .profile-header {
      position: relative;
      margin-bottom: 30px;
    }
    
    .profile-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background: var(--secondary-color);
      color: white;
      padding: 5px 15px;
      border-radius: 50px;
      font-size: 0.8rem;
      font-weight: 600;
      box-shadow: var(--shadow-sm);
    }
    
    .edit-profile-btn {
      position: absolute;
      top: 20px;
      right: 20px;
      background: rgba(255,255,255,0.9);
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary-color);
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
      border: none;
    }
    
    .edit-profile-btn:hover {
      background: white;
      transform: rotate(15deg);
    }
    
    .nav-tabs {
      border-bottom: 1px solid rgba(93,64,55,0.1);
    }
    
    .nav-tabs .nav-link {
      color: var(--primary-color);
      font-weight: 500;
      border: none;
      padding: 12px 20px;
      position: relative;
    }
    
    .nav-tabs .nav-link:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 3px;
      background: var(--secondary-color);
      transition: var(--transition);
    }
    
    .nav-tabs .nav-link:hover,
    .nav-tabs .nav-link.active {
      color: var(--secondary-color);
      background: transparent;
      border: none;
    }
    
    .nav-tabs .nav-link:hover:after,
    .nav-tabs .nav-link.active:after {
      width: 100%;
    }
    
    .tab-content {
      padding: 30px 0;
    }
    
    .profile-section {
      background: white;
      border-radius: 20px;
      box-shadow: var(--shadow-md);
      padding: 30px;
      margin-bottom: 30px;
    }
    
    .profile-section-title {
      font-size: 1.5rem;
      color: var(--primary-color);
      margin-bottom: 20px;
      position: relative;
      padding-bottom: 10px;
    }
    
    .profile-section-title:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 40px;
      height: 3px;
      background: var(--secondary-color);
    }
    
    .animate-delay-1 {
      animation-delay: 0.1s;
    }
    
    .animate-delay-2 {
      animation-delay: 0.2s;
    }
  </style> -->
</head>
<body>
  <?php include '../include/header.php'; ?>
  
  <div class="container py-5">
    <div class="text-center mb-5 animate__animated animate__fadeIn">
      <h2 class="section-title">My Profile</h2>
      <p class="text-muted">Manage your account information and preferences</p>
    </div>
    
    <div class="row">
      <div class="col-lg-4 animate__animated animate__fadeInLeft">
        <div class="profile-card text-center">
          <button class="edit-profile-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <i class="bi bi-pencil"></i>
          </button>
          
          <div class="profile-header">
            <?php if (!empty($customer['profile_pic'])): ?>
              <img src="../uploads/profile_pics/<?= htmlspecialchars($customer['profile_pic']) ?>" alt="Profile Picture" class="profile-avatar">
            <?php else: ?>
              <div class="profile-avatar mx-auto bg-light d-flex align-items-center justify-content-center">
                <i class="bi bi-person-fill" style="font-size: 3rem; color: var(--secondary-color);"></i>
              </div>
            <?php endif; ?>
            <h4 class="mb-1"><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></h4>
            <p class="text-muted">Member since <?= date('M Y', strtotime($customer['created_at'] ?? 'now')) ?></p>
            <!-- <span class="profile-badge animate__animated animate__pulse animate__infinite">
              <i class="bi bi-star-fill me-1"></i> Premium Member
            </span> -->
          </div>
          
          <div class="text-start">
            <div class="profile-detail">
              <i class="bi bi-envelope"></i>
              <span><?= htmlspecialchars($customer['email']) ?></span>
            </div>
            <div class="profile-detail">
              <i class="bi bi-telephone"></i>
              <span><?= htmlspecialchars($customer['number']) ?></span>
            </div>
            <div class="profile-detail">
              <i class="bi bi-gender-ambiguous"></i>
              <span><?= htmlspecialchars($customer['gender']) ?></span>
            </div>
            <div class="profile-detail">
              <i class="bi bi-calendar"></i>
              <span><?= htmlspecialchars($customer['dob']) ?></span>
            </div>
            <div class="profile-detail">
              <i class="bi bi-geo-alt"></i>
              <span><?= htmlspecialchars($customer['city'] . ', ' . $customer['district'] . ', ' . $customer['province']) ?></span>
            </div>
          </div>
          
          <button class="btn btn-premium mt-4 w-100">
            <i class="bi bi-gem me-2"></i> Upgrade to VIP
          </button>
        </div>
      </div>
      
      <div class="col-lg-8 animate__animated animate__fadeInRight">
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">My Bookings</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">Settings</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">Preferences</button>
          </li>
        </ul>
        
        <div class="tab-content" id="profileTabsContent">
          <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="profile-section animate__animated animate__fadeIn animate-delay-1">
              <h5 class="profile-section-title">Recent Activity</h5>
              <div class="d-flex align-items-center mb-3">
                <div class="bg-light p-3 rounded-circle me-3">
                  <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                  <h6 class="mb-0">Booking confirmed</h6>
                  <small class="text-muted">Deluxe Suite - Check-in: 15 Jul 2023</small>
                </div>
              </div>
              <div class="d-flex align-items-center mb-3">
                <div class="bg-light p-3 rounded-circle me-3">
                  <i class="bi bi-star-fill text-warning" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                  <h6 class="mb-0">Review submitted</h6>
                  <small class="text-muted">5-star rating for your last stay</small>
                </div>
              </div>
            </div>
            
            <div class="profile-section animate__animated animate__fadeIn animate-delay-2">
              <h5 class="profile-section-title">Loyalty Points</h5>
              <div class="row text-center">
                <div class="col-md-4 mb-3 mb-md-0">
                  <div class="display-4 text-primary">1,250</div>
                  <small class="text-muted">Current Points</small>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                  <div class="display-4 text-primary">250</div>
                  <small class="text-muted">Points to Gold</small>
                </div>
                <div class="col-md-4">
                  <div class="display-4 text-primary">3</div>
                  <small class="text-muted">Upcoming Rewards</small>
                </div>
              </div>
              <div class="progress mt-4" style="height: 10px;">
                <div class="progress-bar bg-warning" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          </div>
          
          <div class="tab-pane fade" id="bookings" role="tabpanel">
            <div class="profile-section">
              <h5 class="profile-section-title">Upcoming Bookings</h5>
              <p class="text-muted">You have no upcoming bookings.</p>
            </div>
          </div>
          
          <div class="tab-pane fade" id="settings" role="tabpanel">
            <div class="profile-section">
              <h5 class="profile-section-title">Account Settings</h5>
      <form>
                <div class="row mb-4">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($customer['first_name']) ?>">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($customer['last_name']) ?>">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($customer['number']) ?>">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" value="<?= htmlspecialchars($customer['dob']) ?>">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Gender</label>
                    <select class="form-select">
                      <option <?= $customer['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                      <option <?= $customer['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                      <option <?= $customer['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">Province</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($customer['province']) ?>">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">District</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($customer['district']) ?>">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label">City</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($customer['city']) ?>">
                  </div>
                </div>
                
                <h5 class="profile-section-title mt-5">Change Password</h5>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-control" placeholder="Enter current password">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" placeholder="Enter new password">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" placeholder="Confirm new password">
                  </div>
                </div>
                
                <button class="btn btn-premium mt-3">
                  <i class="bi bi-save me-2"></i> Save Changes
                </button>
              </form>
            </div>
          </div>
          
          <div class="tab-pane fade" id="preferences" role="tabpanel">
            <div class="profile-section">
              <h5 class="profile-section-title">Notification Preferences</h5>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                <label class="form-check-label" for="emailNotifications">Email Notifications</label>
              </div>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="smsNotifications">
                <label class="form-check-label" for="smsNotifications">SMS Notifications</label>
              </div>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="promotionalOffers" checked>
                <label class="form-check-label" for="promotionalOffers">Promotional Offers</label>
              </div>
              
              <h5 class="profile-section-title mt-5">Room Preferences</h5>
              <div class="mb-3">
                <label class="form-label">Preferred Room Type</label>
                <select class="form-select">
                  <option>Deluxe Room</option>
                  <option>Executive Suite</option>
                  <option>Presidential Suite</option>
                </select>
              </div>
        <div class="mb-3">
                <label class="form-label">Special Requests</label>
                <textarea class="form-control" rows="3" placeholder="Any special preferences for your stay..."></textarea>
              </div>
              
              <button class="btn btn-premium mt-3">
                <i class="bi bi-save me-2"></i> Save Preferences
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Profile Modal -->
  <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Profile Picture</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-4">
            <?php if (!empty($customer['profile_pic'])): ?>
              <img src="../uploads/profile_pics/<?= htmlspecialchars($customer['profile_pic']) ?>" alt="Profile Picture" class="profile-avatar mb-3">
            <?php else: ?>
              <div class="profile-avatar mx-auto bg-light d-flex align-items-center justify-content-center mb-3">
                <i class="bi bi-person-fill" style="font-size: 3rem; color: var(--secondary-color);"></i>
              </div>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-secondary me-2">
              <i class="bi bi-camera me-1"></i> Upload New
            </button>
            <button class="btn btn-sm btn-outline-danger">
              <i class="bi bi-trash me-1"></i> Remove
            </button>
        </div>
        <div class="mb-3">
            <label class="form-label">Crop Image</label>
            <div class="bg-light p-4 rounded text-center" style="height: 200px;">
              <p class="text-muted">Image cropping tool would appear here</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-premium">Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <?php include '../include/footer_guest.php'; ?>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Simple animation trigger
    document.addEventListener('DOMContentLoaded', function() {
      const animatedElements = document.querySelectorAll('.animate__animated');
      
      animatedElements.forEach((element) => {
        element.style.opacity = '0';
      });
      
      setTimeout(() => {
        animatedElements.forEach((element) => {
          element.style.opacity = '1';
        });
      }, 100);
    });
  </script>
</body>
</html> 