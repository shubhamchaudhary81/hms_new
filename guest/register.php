<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//Database conection 
include_once("../config/configdatabse.php");

// Initialize errors array
$errors = array();

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $dob = trim($_POST['dob']);
    $number = trim($_POST['number']);
    $province = trim($_POST['province']);
    $district = trim($_POST['district']);
    $city = trim($_POST['city']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $gender = isset($_POST['gender']) ? $_POST['gender'] : 'Male';
    $profile_pic = $_FILES['profile_pic'];

    // Check if email already exists
    $email_check = $conn->prepare("SELECT email FROM customers WHERE email = ?");
    $email_check->bind_param("s", $email);
    $email_check->execute();
    $email_result = $email_check->get_result();
    if ($email_result->num_rows > 0) {
        $errors['email_error'] = "An account with this email already exists.";
    }

    // Check if phone number already exists
    $number_check = $conn->prepare("SELECT number FROM customers WHERE number = ?"); // Make sure your DB column is named 'number'
    $number_check->bind_param("s", $number);
    $number_check->execute();
    $number_result = $number_check->get_result();
    if ($number_result->num_rows > 0) {
        $errors['number_error'] = "An account with this phone number already exists.";
    }

    // First Name Validation
    $firstName = trim($firstName);
    if (!(strlen($firstName) > 0)) {
        $errors['firstName_error'] = "FirstName is required";
    } else {
        $pattern = "/^[a-zA-Z ]+$/";
        if (!preg_match($pattern, $firstName)) {
            $errors['firstName_error'] = "FirstName can't contain digits and special characters";
        }
    }

    // Last Name Validation
    $lastName = trim($lastName);
    if (!(strlen($lastName) > 0)) {
        $errors['lastName_error'] = "LastName is required";
    } else {
        $pattern = "/^[a-zA-Z ]+$/"; // it includes more than one alphabet with space
        if (!preg_match($pattern, $lastName)) {
            $errors['lastName_error'] = "LastName can't contain digits and special characters";
        }
    }

    // Email Validation
    $email = trim($email);
    if (!(strlen($email) > 0)) {
        $errors['email_error'] = "Email can't be blank";
    } else {
        $pattern = "/^[a-z0-9\.-_]+@[a-z]+\.[a-z]+(\.[a-z]{2})?$/";
        if (!preg_match($pattern, $email)) {
            $errors['email_error'] = "Email address is not valid";
        }
    }
    
    // Number validation
    $number = trim($number);
    $number_pattern = "/^9[87][0-9]{8}$/";
    if (!(strlen($number) > 0)) {
        $errors['number_error'] = "Phone number is required.";
    } else if (!preg_match($number_pattern, $number)) {
        $errors['number_error'] = "Phone number is not valid.";
    }

    // Password Validation
    $password = trim($password);
    if (!(strlen($password) > 0)) {
        $errors['password_error'] = "Password is required";
    } else if (strlen($password) <= 8) {
        $errors['password_error'] = "Password should be greater than 8 digits";
    } else {
        $pattern = "/^[a-zA-Z0-9@\.#]+$/";
        if (!preg_match($pattern, $password)) {
            $errors['password_error'] = "Password is not valid";
        }
    }

    // Confirm password validation
    $confirm_password = trim($confirm_password);
    if (!(strlen($confirm_password) > 0)) {
        $errors['confirm_password_error'] = "Re-enter your password is required";
    } else if ($confirm_password !== $password) {
        $errors['confirm_password_error'] = "Confirm password and password should be the same";
    }

    // Address Validation
    if (empty($province) || empty($district) || empty($city)) {
        $errors['province'] = "province is required.";
        $errors['district'] = "district is required.";
        $errors['city'] = "city is required.";
    }

    if (count($errors) === 0) {
    // Profile picture handling
    $profilePicName = '';
    if ($profile_pic['error'] === 0) {
        $targetDir = "../uploads/profile_pics/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        if (!is_writable($targetDir)) {
            $errors['profile_pic'] = "Profile picture directory is not writable.";
        } else {
            $profilePicName = uniqid() . "_" . basename($profile_pic['name']);
            $targetFile = $targetDir . $profilePicName;
            move_uploaded_file($profile_pic['tmp_name'], $targetFile);
        }
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into the database
    $stmt = $conn->prepare("INSERT INTO customers 
        (first_name, last_name, email, number, dob, gender, province, district, city, password, profile_pic) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param(
            "sssssssssss",
            $firstName,
            $lastName,
            $email,
            $number,
            $dob,
            $gender,
            $province,
            $district,
            $city,
            $hashedPassword,
            $profilePicName
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Registration successful!";
            header("Location: ../login.php");
            exit();
        } else {
            $errors['database_error'] = "Database error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $errors['database_error'] = "Database statement preparation failed: " . $conn->error;
    }
}

} // <-- This closes the if ($_SERVER["REQUEST_METHOD"] == 'POST') { block

// Show errors above the form
if (!empty($errors)) {
    echo '<div class="alert alert-danger">';
    foreach ($errors as $err) {
        echo '<div>' . htmlspecialchars($err) . '</div>';
    }
    echo '</div>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Himalaya Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/guestregister.css">
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2><i class="fas fa-user-plus me-2"></i>Guest Registration</h2>
            <p>Join Himalaya Hotel for exclusive benefits and personalized service. Please fill out all required fields.</p>
        </div>
        
        <form action="" method="POST" enctype="multipart/form-data" autocomplete="off" id="registrationForm">
            <!-- Personal Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-user-circle"></i> Personal Information
                </div>
                <div class="row form-row">
                    <div class="col-md-6 mb-3">
                        <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name" required>
                        </div>
                        <div class="invalid-feedback">Please provide a valid first name.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name" required>
                        </div>
                        <div class="invalid-feedback">Please provide a valid last name.</div>
                    </div>
                </div>
                
                <div class="row form-row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="example@gmail.com" required>
                        </div>
                        <div class="invalid-feedback">Please provide a valid email address.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="tel" class="form-control" id="number" name="number" pattern="[0-9]{10,15}" placeholder="98XXXXXXXX" required>
                        </div>
                        <div class="invalid-feedback">Please provide a valid phone number (10-15 digits).</div>
                    </div>
                </div>
                
                <div class="row form-row">
                    <div class="col-md-4 mb-3">
                        <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="dob" name="dob" required>
                        </div>
                        <div class="invalid-feedback">Please select your date of birth.</div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label d-block">Gender <span class="text-danger">*</span></label>
                        <div class="gender-options">
                            <div class="gender-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" required>
                                    <label class="form-check-label" for="genderMale"><i class="fas fa-male me-1"></i> Male</label>
                                </div>
                            </div>
                            <div class="gender-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" required>
                                    <label class="form-check-label" for="genderFemale"><i class="fas fa-female me-1"></i> Female</label>
                                </div>
                            </div>
                            <div class="gender-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderOther" value="Other" required>
                                    <label class="form-check-label" for="genderOther"><i class="fas fa-user me-1"></i> Other</label>
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback">Please select your gender.</div>
                    </div>
                </div>
            </div>
            
            <!-- Address Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-map-marker-alt"></i> Address Information
                </div>
                <div class="row form-row">
                    <div class="col-md-4 mb-3">
                        <label for="province" class="form-label">Province <span class="text-danger">*</span></label>
                        <select class="form-select" id="province" name="province" required>
                            <option value="">Select Province</option>
                            <!-- Options will be populated by JS -->
                        </select>
                        <div class="invalid-feedback">Please select your province.</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="district" class="form-label">District <span class="text-danger">*</span></label>
                        <select class="form-select" id="district" name="district" required>
                            <option value="">Select District</option>
                            <!-- Options will be populated by JS -->
                        </select>
                        <div class="invalid-feedback">Please select your district.</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="city" class="form-label">City/Municipality <span class="text-danger">*</span></label>
                        <select class="form-select" id="city" name="city" required>
                            <option value="">Select City</option>
                            <!-- Options will be populated by JS -->
                        </select>
                        <div class="invalid-feedback">Please select your city/municipality.</div>
                    </div>
                </div>
            </div>
            
            <!-- Account Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-lock"></i> Account Information
                </div>
                <div class="row form-row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" minlength="6" placeholder="At least 6 characters" required>
                            <span class="input-group-text toggle-password" style="cursor: pointer;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="password-strength mt-2">
                            <div class="progress" style="height: 6px;">
                                <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="password-strength-text" class="text-muted">Password strength: Very Weak</small>
                        </div>
                        <div class="invalid-feedback">Password must be at least 6 characters.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" placeholder="Confirm your password" required>
                            <span class="input-group-text toggle-password" style="cursor: pointer;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <small id="password-match-feedback" class="text-danger"></small>
                        <div class="invalid-feedback">Passwords must match.</div>
                    </div>
                </div>
                
                <div class="row form-row">
                    <div class="col-md-6 mb-3">
                        <label for="profile_pic" class="form-label">Profile Picture <span class="text-danger">*</span></label>
                        <div class="profile-pic-container">
                            <img id="profilePicPreview" class="profile-pic-preview" src="#" alt="Profile Preview">
                            <div class="profile-upload-btn">
                                <button type="button" class="btn btn-outline-secondary w-100" id="uploadBtn">
                                    <i class="fas fa-upload me-2"></i>Upload Photo
                                </button>
                                <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/*" required style="display:none;">
                            </div>
                        </div>
                        <small class="text-muted">Max file size: 2MB (JPEG, PNG)</small>
                        <div class="invalid-feedback">Please upload a profile picture.</div>
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-center">
                        <div class="form-check">
                            <!-- <input class="form-check-input" type="checkbox" id="terms" required> -->
                            <!-- <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="login-link">Terms & Conditions</a> and <a href="#" class="login-link">Privacy Policy</a> <span class="text-danger">*</span>
                            </label> -->
                            <div class="invalid-feedback">You must agree to the terms and conditions.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-brown">
                    <i class="fas fa-user-plus me-2"></i> Complete Registration
                </button>
            </div>
            
            <div class="mt-4 text-center">
                <span>Already have an account? <a href="../login.php" class="login-link"><i class="fas fa-sign-in-alt me-1"></i>Login Here</a></span>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <script>
        // Password visibility toggle
        document.querySelectorAll('.toggle-password').forEach(function(element) {
            element.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Password strength meter
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const result = zxcvbn(password);
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            
            let strength = 0;
            let color = 'bg-danger';
            let text = 'Very Weak';
            
            switch(result.score) {
                case 1:
                    strength = 25;
                    color = 'bg-danger';
                    text = 'Weak';
                    break;
                case 2:
                    strength = 50;
                    color = 'bg-warning';
                    text = 'Fair';
                    break;
                case 3:
                    strength = 75;
                    color = 'bg-info';
                    text = 'Strong';
                    break;
                case 4:
                    strength = 100;
                    color = 'bg-success';
                    text = 'Very Strong';
                    break;
                default:
                    strength = 0;
                    color = 'bg-danger';
                    text = 'Very Weak';
            }
            
            strengthBar.style.width = strength + '%';
            strengthBar.className = 'progress-bar ' + color;
            strengthText.textContent = 'Strength: ' + text;
            strengthText.className = strength < 50 ? 'text-danger' : 'text-success';
        });

        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const feedback = document.getElementById('password-match-feedback');
            
            if (confirmPassword.length > 0) {
                if (password !== confirmPassword) {
                    feedback.textContent = 'Passwords do not match!';
                } else {
                    feedback.textContent = 'Passwords match!';
                    feedback.className = 'text-success';
                }
            } else {
                feedback.textContent = '';
            }
        });

        // Profile picture preview
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('profilePicPreview');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            }
        });

        // Form submission validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Please make sure your passwords match!');
                document.getElementById('password').focus();
            }
        });

        document.getElementById('uploadBtn').addEventListener('click', function() {
            document.getElementById('profile_pic').click();
        });
    </script>
    <script>
// --- Nepal Province, District, City Data ---
const locationData = {
    "Province 1": {
        "Bhojpur": ["Bhojpur Municipality", "Shadanand Municipality", "Hatuwagadhi Rural Municipality", "Pauwadungma Rural Municipality", "Ramprasad Rai Rural Municipality", "Salpasilichho Rural Municipality", "Temkemaiyung Rural Municipality"],
        "Dhankuta": ["Dhankuta Municipality", "Chhathar Jorpati Rural Municipality", "Khalsa Chhintang Sahidbhumi Rural Municipality", "Mahalaxmi Municipality", "Pakhribas Municipality", "Sangurigadhi Rural Municipality", "Shahidbhumi Rural Municipality"],
        "Ilam": ["Ilam Municipality", "Deumai Municipality", "Mai Municipality", "Suryodaya Municipality", "Chulachuli Rural Municipality", "Fakfokathum Rural Municipality", "Maijogmai Rural Municipality", "Mangsebung Rural Municipality", "Rong Rural Municipality", "Sandakpur Rural Municipality"],
        "Jhapa": ["Bhadrapur Municipality", "Birtamod Municipality", "Damak Municipality", "Kankai Municipality", "Mechinagar Municipality", "Arjundhara Municipality", "Buddhashanti Rural Municipality", "Gauradaha Municipality", "Gauriganj Rural Municipality", "Haldibari Rural Municipality", "Jhapa Rural Municipality", "Kamal Rural Municipality", "Shivasatakshi Municipality"],
        "Khotang": ["Diktel Rupakot Majhuwagadhi Municipality", "Aiselukharka Rural Municipality", "Barahapokhari Rural Municipality", "Diprung Chuichumma Rural Municipality", "Halesi Tuwachung Municipality", "Jantedhunga Rural Municipality", "Kepilasgadhi Rural Municipality", "Khotehang Rural Municipality", "Lamidanda Rural Municipality", "Rawa Besi Rural Municipality", "Sakela Rural Municipality"],
        "Morang": ["Biratnagar Metropolitan City", "Belbari Municipality", "Budhiganga Rural Municipality", "Dhanpalthan Rural Municipality", "Gramthan Rural Municipality", "Jahada Rural Municipality", "Kanepokhari Rural Municipality", "Katahari Rural Municipality", "Kerabari Rural Municipality", "Letang Municipality", "Miklajung Rural Municipality", "Pathari Sanischare Municipality", "Rangeli Municipality", "Ratuwamai Municipality", "Sundarharaicha Municipality", "Urlabari Municipality"],
        "Okhaldhunga": ["Siddhicharan Municipality", "Champadevi Rural Municipality", "Chisankhugadhi Rural Municipality", "Khijidemba Rural Municipality", "Likhu Rural Municipality", "Manebhanjyang Rural Municipality", "Molung Rural Municipality", "Sunkoshi Rural Municipality"],
        "Panchthar": ["Phidim Municipality", "Falelung Rural Municipality", "Falgunanda Rural Municipality", "Hilihang Rural Municipality", "Kummayak Rural Municipality", "Miklajung Rural Municipality", "Tumbewa Rural Municipality", "Yangwarak Rural Municipality"],
        "Sankhuwasabha": ["Khandbari Municipality", "Chainpur Municipality", "Dharmadevi Municipality", "Madi Municipality", "Panchkhapan Municipality", "Bhotkhola Rural Municipality", "Chichila Rural Municipality", "Makalu Rural Municipality", "Sabhapokhari Rural Municipality", "Silichong Rural Municipality"],
        "Solukhumbu": ["Salleri Municipality", "Dudhkunda Municipality", "Khumbu Pasanglhamu Rural Municipality", "Mahakulung Rural Municipality", "Mapya Dudhkoshi Rural Municipality", "Necha Salyan Rural Municipality", "Sotang Rural Municipality", "Thulung Dudhkoshi Rural Municipality"],
        "Sunsari": ["Inaruwa Municipality", "Dharan Sub-Metropolitan City", "Duhabi Municipality", "Itahari Sub-Metropolitan City", "Barahachhetra Municipality", "Bhokraha Narsingh Rural Municipality", "Dewangunj Rural Municipality", "Gadhi Rural Municipality", "Harinagara Rural Municipality", "Koshi Rural Municipality", "Ramdhuni Municipality"],
        "Taplejung": ["Phungling Municipality", "Aathrai Tribeni Rural Municipality", "Mikwakhola Rural Municipality", "Meringden Rural Municipality", "Pathibhara Yangwarak Rural Municipality", "Phaktanglung Rural Municipality", "Sidingba Rural Municipality", "Sirijangha Rural Municipality"],
        "Tehrathum": ["Myanglung Municipality", "Aathrai Rural Municipality", "Chhathar Rural Municipality", "Laligurans Municipality", "Menchhayayem Rural Municipality"],
        "Udayapur": ["Triyuga Municipality", "Belaka Municipality", "Chaudandigadhi Municipality", "Katari Municipality", "Rautamai Rural Municipality", "Sunkoshi Rural Municipality", "Tapli Rural Municipality", "Udayapurgadhi Rural Municipality"]
    },
    "Madhesh Province": {
        "Bara": ["Kalaiya Sub-Metropolitan City", "Jeetpursimara Sub-Metropolitan City", "Kolhabi Municipality", "Nijgadh Municipality", "Pacharauta Municipality", "Parwanipur Rural Municipality", "Prasauni Rural Municipality", "Simraungadh Municipality", "Suwarna Rural Municipality"],
        "Dhanusha": ["Janakpur Sub-Metropolitan City", "Bateshwar Rural Municipality", "Chhireshwarnath Municipality", "Dhanauji Rural Municipality", "Dhanushadham Municipality", "Ganeshman Charnath Municipality", "Hansapur Municipality", "Kamala Municipality", "Laxminiya Rural Municipality", "Mithila Bihari Municipality", "Mithila Municipality", "Mukhiyapatti Musharniya Rural Municipality", "Nagarain Municipality", "Sabaila Municipality", "Shahidnagar Municipality"],
        "Mahottari": ["Jaleshwar Municipality", "Aurahi Municipality", "Bardibas Municipality", "Balwa Municipality", "Bhangaha Municipality", "Ekdara Rural Municipality", "Gaushala Municipality", "Loharpatti Municipality", "Manara Shiswa Municipality", "Matihani Municipality", "Pipra Rural Municipality", "Ramgopalpur Municipality", "Samsi Rural Municipality", "Sonama Rural Municipality"],
        "Parsa": ["Birgunj Metropolitan City", "Bahudarmai Municipality", "Bindabasini Rural Municipality", "Chhipaharmai Rural Municipality", "Jagarnathpur Rural Municipality", "Jirabhawani Rural Municipality", "Kalikamai Rural Municipality", "Pakaha Mainpur Rural Municipality", "Parsagadhi Municipality", "Pokhariya Municipality", "Sakhuwa Prasauni Rural Municipality", "Thori Rural Municipality"],
        "Rautahat": ["Gaur Municipality", "Baudhimai Municipality", "Brindaban Municipality", "Chandrapur Municipality", "Dewahi Gonahi Municipality", "Durga Bhagwati Rural Municipality", "Fatuwa Bijaypur Municipality", "Gadhimai Municipality", "Garuda Municipality", "Gujara Municipality", "Ishanath Municipality", "Katahariya Municipality", "Madhav Narayan Municipality", "Maulapur Municipality", "Paroha Municipality", "Phatuwa Bijaypur Municipality", "Rajdevi Municipality", "Rajpur Municipality", "Yamunamai Rural Municipality"],
        "Saptari": ["Rajbiraj Municipality", "Agnisair Krishna Savaran Rural Municipality", "Balan Bihul Rural Municipality", "Bodebarsain Municipality", "Chhinnamasta Rural Municipality", "Dakneshwori Municipality", "Hanumannagar Kankalini Municipality", "Kanchanrup Municipality", "Khadak Municipality", "Mahadeva Rural Municipality", "Rajgadh Rural Municipality", "Rupani Rural Municipality", "Saptakoshi Municipality", "Shambhunath Municipality", "Surunga Municipality", "Tilathi Koiladi Rural Municipality", "Tirhut Rural Municipality"],
        "Sarlahi": ["Malangwa Municipality", "Bagmati Municipality", "Balara Municipality", "Barahathawa Municipality", "Bishnu Rural Municipality", "Bramhapuri Rural Municipality", "Chakraghatta Rural Municipality", "Chandranagar Rural Municipality", "Dhankaul Rural Municipality", "Godaita Municipality", "Haripur Municipality", "Haripurwa Municipality", "Haripurwa Rural Municipality", "Ishworpur Municipality", "Kabilasi Municipality", "Kaudena Rural Municipality", "Lalbandi Municipality", "Parsa Rural Municipality", "Ramnagar Rural Municipality", "Sakhuwa Prasauni Rural Municipality"],
        "Siraha": ["Siraha Municipality", "Aurahi Rural Municipality", "Bariyarpatti Rural Municipality", "Bhagwanpur Rural Municipality", "Bishnupur Rural Municipality", "Dhangadhimai Municipality", "Golbazar Municipality", "Kalyanpur Municipality", "Karjanha Municipality", "Lahan Municipality", "Laxmipur Patari Rural Municipality", "Mirchaiya Municipality", "Naraha Rural Municipality", "Navarajpur Rural Municipality", "Sakhuwanankarkatti Rural Municipality", "Sukhipur Municipality"],
    },
    "Bagmati Province": {
        "Bhaktapur": ["Bhaktapur Municipality", "Changunarayan Municipality", "Madhyapur Thimi Municipality", "Suryabinayak Municipality"],
        "Chitwan": ["Bharatpur Metropolitan City", "Kalika Municipality", "Khairahani Municipality", "Madi Municipality", "Rapti Municipality", "Ratnanagar Municipality", "Ichchhakamana Rural Municipality"],
        "Dhading": ["Dhadingbesi (Nilkantha Municipality)", "Benighat Rorang Rural Municipality", "Dhunibesi Municipality", "Galchhi Rural Municipality", "Gajuri Rural Municipality", "Gangajamuna Rural Municipality", "Jwalamukhi Rural Municipality", "Khaniyabash Rural Municipality", "Netrawati Dabjong Rural Municipality", "Rubi Valley Rural Municipality", "Siddhalek Rural Municipality", "Thakre Rural Municipality", "Tripurasundari Rural Municipality"],
        "Dolakha": ["Bhimeshwor Municipality", "Baiteshwor Rural Municipality", "Bigu Rural Municipality", "Gaurishankar Rural Municipality", "Jiri Municipality", "Kalinchowk Rural Municipality", "Melung Rural Municipality", "Shailung Rural Municipality", "Tamakoshi Rural Municipality"],
        "Kathmandu": ["Kathmandu Metropolitan City", "Budhanilkantha Municipality", "Chandragiri Municipality", "Dakshinkali Municipality", "Gokarneshwor Municipality", "Kageshwori Manohara Municipality", "Kirtipur Municipality", "Nagarjun Municipality", "Shankharapur Municipality", "Tarakeshwar Municipality", "Tokha Municipality"],
        "Kavrepalanchok": ["Dhulikhel Municipality", "Banepa Municipality", "Bethanchok Rural Municipality", "Bhumlu Rural Municipality", "Chaurideurali Rural Municipality", "Khanikhola Rural Municipality", "Mandandeupur Municipality", "Namobuddha Municipality", "Panauti Municipality", "Panchkhal Municipality", "Roshi Rural Municipality", "Temal Rural Municipality"],
        "Lalitpur": ["Lalitpur Metropolitan City", "Bagmati Rural Municipality", "Godawari Municipality", "Konjyosom Rural Municipality", "Mahankal Rural Municipality", "Mahalaxmi Municipality", "Shankharapur Municipality"],
        "Makwanpur": ["Hetauda Sub-Metropolitan City", "Bakaiya Rural Municipality", "Bhimphedi Rural Municipality", "Bagmati Rural Municipality", "Indrasarowar Rural Municipality", "Kailash Rural Municipality", "Makawanpurgadhi Rural Municipality", "Manahari Rural Municipality", "Raksirang Rural Municipality", "Thaha Municipality"],
        "Nuwakot": ["Bidur Municipality", "Belkotgadhi Municipality", "Dupcheshwar Rural Municipality", "Kakani Rural Municipality", "Kispang Rural Municipality", "Likhu Rural Municipality", "Meghang Rural Municipality", "Panchakanya Rural Municipality", "Shivapuri Rural Municipality", "Suryagadhi Rural Municipality", "Tadi Rural Municipality", "Tarkeshwar Rural Municipality"],
        "Ramechhap": ["Manthali Municipality", "Doramba Rural Municipality", "Gokulganga Rural Municipality", "Khandadevi Rural Municipality", "Likhu Tamakoshi Rural Municipality", "Ramechhap Municipality", "Sunapati Rural Municipality", "Umakunda Rural Municipality"],
        "Rasuwa": ["Uttargaya Rural Municipality", "Aamachhodingmo Rural Municipality", "Gosaikunda Rural Municipality", "Kalika Rural Municipality", "Naukunda Rural Municipality"],
        "Sindhuli": ["Kamalamai Municipality", "Dudhauli Municipality", "Golanjor Rural Municipality", "Ghyanglekh Rural Municipality", "Hariharpurgadhi Rural Municipality", "Marin Rural Municipality", "Phikkal Rural Municipality", "Sunkoshi Rural Municipality", "Tinpatan Rural Municipality"],
        "Sindhupalchok": ["Chautara Sangachokgadhi Municipality", "Barhabise Municipality", "Balephi Rural Municipality", "Bhotekoshi Rural Municipality", "Helambu Rural Municipality", "Indrawati Rural Municipality", "Jugal Rural Municipality", "Lisankhu Pakhar Rural Municipality", "Melamchi Municipality", "Panchpokhari Thangpal Rural Municipality", "Sunkoshi Rural Municipality", "Tripurasundari Rural Municipality"]
    },
    "Gandaki Province": {
        "Baglung": ["Baglung Municipality", "Dhorpatan Municipality", "Galkot Municipality", "Jaimuni Municipality", "Bareng Rural Municipality", "Kanthekhola Rural Municipality", "Nisikhola Rural Municipality", "Taman Khola Rural Municipality", "Tara Khola Rural Municipality"],
        "Gorkha": ["Gorkha Municipality", "Palungtar Municipality", "Aarughat Rural Municipality", "Ajirkot Rural Municipality", "Bhimsen Thapa Rural Municipality", "Chum Nubri Rural Municipality", "Dharche Rural Municipality", "Gandaki Rural Municipality", "Sahid Lakhan Rural Municipality", "Siranchok Rural Municipality"],
        "Kaski": ["Pokhara Metropolitan City", "Annapurna Rural Municipality", "Machhapuchchhre Rural Municipality", "Madi Rural Municipality", "Rupa Rural Municipality"],
        "Lamjung": ["Besisahar Municipality", "Madhya Nepal Municipality", "Rainas Municipality", "Sundarbazar Municipality", "Dordi Rural Municipality", "Dudhpokhari Rural Municipality", "Kwholasothar Rural Municipality", "Marsyangdi Rural Municipality"],
        "Manang": ["Chame Rural Municipality", "Manang Ngisyang Rural Municipality", "Narpa Bhumi Rural Municipality", "Nashong Rural Municipality"],
        "Mustang": ["Gharpajhong Rural Municipality", "Lomanthang Rural Municipality", "Lo-Ghekar Damodarkunda Rural Municipality", "Thasang Rural Municipality", "Varagung Muktichhetra Rural Municipality"],
        "Myagdi": ["Beni Municipality", "Annapurna Rural Municipality", "Dhaulagiri Rural Municipality", "Malika Rural Municipality", "Mangala Rural Municipality", "Raghuganga Rural Municipality"],
        "Nawalpur": ["Kawasoti Municipality", "Devchuli Municipality", "Gaindakot Municipality", "Madhyabindu Municipality", "Binayi Tribeni Rural Municipality", "Bulingtar Rural Municipality", "Hupsekot Rural Municipality", "Baudikali Rural Municipality"],
        "Parbat": ["Kusma Municipality", "Jaljala Rural Municipality", "Mahashila Rural Municipality", "Modi Rural Municipality", "Paiyun Rural Municipality", "Phalebas Municipality", "Bihadi Rural Municipality"],
        "Syangja": ["Putalibazar Municipality", "Bhirkot Municipality", "Chapakot Municipality", "Galyang Municipality", "Waling Municipality", "Aandhikhola Rural Municipality", "Arjunchaupari Rural Municipality", "Biruwa Rural Municipality", "Harinas Rural Municipality", "Kaligandaki Rural Municipality", "Phedikhola Rural Municipality"],
        "Tanahun": ["Damauli (Byas Municipality)", "Bandipur Rural Municipality", "Bhimad Municipality", "Devghat Rural Municipality", "Ghiring Rural Municipality", "Myagde Rural Municipality", "Rhishing Rural Municipality", "Shuklagandaki Municipality", "Vyas Municipality"]
    },
    "Lumbini Province": {
        "Arghakhanchi": ["Sandhikharka Municipality", "Bhumikasthan Municipality", "Chhatradev Rural Municipality", "Malarani Rural Municipality", "Panini Rural Municipality", "Shitaganga Municipality"],
        "Banke": ["Nepalgunj Sub-Metropolitan City", "Baijanath Rural Municipality", "Duduwa Rural Municipality", "Janaki Rural Municipality", "Kohalpur Municipality", "Khajura Rural Municipality", "Narainapur Rural Municipality", "Rapti Sonari Rural Municipality"],
        "Bardiya": ["Gulariya Municipality", "Badhaiyatal Rural Municipality", "Barbardiya Municipality", "Basgadhi Municipality", "Geruwa Rural Municipality", "Madhuwan Municipality", "Rajapur Municipality", "Thakurbaba Municipality"],
        "Dang": ["Ghorahi Sub-Metropolitan City", "Lamahi Municipality", "Tulsipur Sub-Metropolitan City", "Banglachuli Rural Municipality", "Dangisharan Rural Municipality", "Gadhawa Rural Municipality", "Rajpur Rural Municipality", "Rapti Rural Municipality", "Shantinagar Rural Municipality"],
        "Eastern Rukum": ["Rukumkot (Putha Uttarganga Rural Municipality)", "Bhume Rural Municipality", "Sisne Rural Municipality"],
        "Gulmi": ["Tamghas (Resunga Municipality)", "Chandrakot Rural Municipality", "Chhatrakot Rural Municipality", "Dhurkot Rural Municipality", "Gulmidarbar Rural Municipality", "Isma Rural Municipality", "Kaligandaki Rural Municipality", "Madane Rural Municipality", "Malika Rural Municipality", "Musikot Municipality", "Ruru Rural Municipality", "Satyawati Rural Municipality"],
        "Kapilvastu": ["Taulihawa (Kapilvastu Municipality)", "Banganga Municipality", "Bijaynagar Rural Municipality", "Buddhabhumi Municipality", "Krishnanagar Municipality", "Maharajgunj Municipality", "Mayadevi Rural Municipality", "Shivaraj Municipality", "Suddhodhan Rural Municipality", "Yashodhara Rural Municipality"],
        "Parasi": ["Ramgram Municipality", "Bardaghat Municipality", "Sunwal Municipality", "Palhinandan Rural Municipality", "Pratappur Rural Municipality", "Sarawal Rural Municipality"],
        "Palpa": ["Tansen Municipality", "Bagnaskali Rural Municipality", "Mathagadhi Rural Municipality", "Nisdi Rural Municipality", "Purbakhola Rural Municipality", "Rainadevi Chhahara Rural Municipality", "Rampur Municipality", "Ribdikot Rural Municipality", "Tinau Rural Municipality"],
        "Pyuthan": ["Pyuthan Khalanga (Pyuthan Municipality)", "Airawati Rural Municipality", "Gaumukhi Rural Municipality", "Jhimruk Rural Municipality", "Mallarani Rural Municipality", "Mandavi Rural Municipality", "Naubahini Rural Municipality", "Sarumarani Rural Municipality", "Sworgadwari Municipality"],
        "Rolpa": ["Liwang (Rolpa Municipality)", "Gangadev Rural Municipality", "Lungri Rural Municipality", "Madi Rural Municipality", "Pariwartan Rural Municipality", "Runtigadhi Rural Municipality", "Sunchhahari Rural Municipality", "Thawang Rural Municipality", "Triveni Rural Municipality"],
        "Rupandehi": ["Butwal Sub-Metropolitan City", "Devdaha Municipality", "Gaidahawa Rural Municipality", "Kanchan Rural Municipality", "Kotahimai Rural Municipality", "Lumbini Sanskritik Municipality", "Marchawari Rural Municipality", "Mayadevi Rural Municipality", "Omsatiya Rural Municipality", "Rohini Rural Municipality", "Sainamaina Municipality", "Siddharthanagar Municipality", "Siyari Rural Municipality", "Sudhdhodhan Rural Municipality", "Tilottama Municipality"]
    },
    "Karnali Province": {
        "Dailekh": ["Dailekh Municipality", "Aathbis Municipality", "Bhairabi Rural Municipality", "Chamunda Bindrasaini Municipality", "Dullu Municipality", "Dungeshwor Rural Municipality", "Mahabu Rural Municipality", "Narayan Municipality", "Naumule Rural Municipality", "Thantikandh Rural Municipality"],
        "Dolpa": ["Dunai (Thuli Bheri Municipality)", "Chharka Tangsong Rural Municipality", "Dolpo Buddha Rural Municipality", "Jagadulla Rural Municipality", "Kaike Rural Municipality", "Mudkechula Rural Municipality", "Shey Phoksundo Rural Municipality", "Tripurasundari Municipality"],
        "Humla": ["Simikot Rural Municipality", "Adanchuli Rural Municipality", "Chankheli Rural Municipality", "Kharpunath Rural Municipality", "Sarkegad Rural Municipality", "Tanjakot Rural Municipality"],
        "Jajarkot": ["Khalanga (Bheri Municipality)", "Chhedagad Municipality", "Junichande Rural Municipality", "Kuse Rural Municipality", "Nalgad Municipality", "Shivalaya Rural Municipality"],
        "Jumla": ["Jumla Municipality", "Chandannath Municipality", "Guthichaur Rural Municipality", "Hima Rural Municipality", "Kanakasundari Rural Municipality", "Patrasi Rural Municipality", "Sinja Rural Municipality", "Tatopani Rural Municipality"],
        "Kalikot": ["Manma (Kalikot Municipality)", "Mahawai Rural Municipality", "Naraharinath Rural Municipality", "Pachaljharana Rural Municipality", "Palata Rural Municipality", "Raskot Municipality", "Sanni Triveni Rural Municipality", "Shubha Kalika Rural Municipality", "Tilagufa Municipality"],
        "Mugu": ["Gamgadhi (Chhayanath Rara Municipality)", "Khatyad Rural Municipality", "Mugum Karmarong Rural Municipality", "Soru Rural Municipality"],
        "Salyan": ["Salyan Khalanga (Sharada Municipality)", "Bagchaur Municipality", "Bangad Kupinde Municipality", "Chhatreshwori Rural Municipality", "Darma Rural Municipality", "Dhanwang Rural Municipality", "Kalimati Rural Municipality", "Kapurkot Rural Municipality", "Kumakh Rural Municipality", "Siddha Kumakh Rural Municipality", "Triveni Rural Municipality"],
        "Surkhet": ["Birendranagar Municipality", "Bheriganga Municipality", "Barahatal Rural Municipality", "Chaukune Rural Municipality", "Chingad Rural Municipality", "Gurbhakot Municipality", "Lekbeshi Municipality", "Panchapuri Municipality", "Simta Rural Municipality"],
        "Western Rukum": ["Musikot Municipality", "Aathbiskot Municipality", "Banphikot Rural Municipality", "Chaurjahari Municipality", "Sanibheri Rural Municipality", "Tribeni Rural Municipality"]
    },
    "Sudurpashchim Province": {
        "Achham": ["Mangalsen Municipality", "Bannigadhi Jayagadh Rural Municipality", "Chaurpati Rural Municipality", "Dhakari Rural Municipality", "Kamalbazar Municipality", "Mellekh Rural Municipality", "Ramaroshan Rural Municipality", "Sanphebagar Municipality", "Turmakhand Rural Municipality"],
        "Baitadi": ["Dasharathchand Municipality", "Dilasaini Rural Municipality", "Dogadakedar Rural Municipality", "Melauli Municipality", "Pancheshwor Rural Municipality", "Patan Municipality", "Purchaudi Municipality", "Shivanath Rural Municipality", "Sigas Rural Municipality"],
        "Bajhang": ["Chainpur Municipality", "Bungal Municipality", "Durgathali Rural Municipality", "Jaya Prithvi Municipality", "Kedarsyun Rural Municipality", "Khaptad Chhanna Rural Municipality", "Masta Rural Municipality", "Saipal Rural Municipality", "Surma Rural Municipality", "Talkot Rural Municipality", "Thalara Rural Municipality"],
        "Bajura": ["Martadi (Badimalika Municipality)", "Budhiganga Municipality", "Budhinanda Municipality", "Chhededaha Rural Municipality", "Gaumul Rural Municipality", "Himali Rural Municipality", "Jagannath Rural Municipality", "Khaptad Chhededaha Rural Municipality", "Swamikartik Khapar Rural Municipality", "Triveni Municipality"],
        "Dadeldhura": ["Amargadhi Municipality", "Aalital Rural Municipality", "Bhageshwar Rural Municipality", "Ganayapdhura Rural Municipality", "Nawadurga Rural Municipality", "Parashuram Municipality", "Sahajpur Rural Municipality"],
        "Darchula": ["Darchula Municipality", "Apihimal Rural Municipality", "Byas Rural Municipality", "Mahakali Municipality", "Malikarjun Rural Municipality", "Marma Rural Municipality", "Naugad Rural Municipality", "Shailyashikhar Municipality"],
        "Doti": ["Dipayal Silgadhi Municipality", "Adarsha Rural Municipality", "Badikedar Rural Municipality", "Bogatan Phudsil Rural Municipality", "Jorayal Rural Municipality", "K.I. Singh Rural Municipality", "Purbichauki Rural Municipality", "Sayal Rural Municipality", "Shikhar Municipality"],
        "Kailali": ["Dhangadhi Sub-Metropolitan City", "Bhajani Municipality", "Ghodaghodi Municipality", "Godawari Municipality", "Gauriganga Municipality", "Janaki Rural Municipality", "Joshipur Rural Municipality", "Kailari Rural Municipality", "Lamkichuha Municipality", "Mohanyal Rural Municipality", "Tikapur Municipality"],
        "Kanchanpur": ["Bhimdatta Municipality", "Bedkot Municipality", "Belauri Municipality", "Beldandi Rural Municipality", "Krishnapur Municipality", "Laljhadi Rural Municipality", "Mahakali Municipality", "Punarbas Municipality", "Shuklaphanta Municipality"]
    }
};

const provinceSelect = document.getElementById('province');
const districtSelect = document.getElementById('district');
const citySelect = document.getElementById('city');

// Populate provinces
for (const province in locationData) {
    const option = document.createElement('option');
    option.value = province;
    option.textContent = province;
    provinceSelect.appendChild(option);
}

// When province changes, update districts
provinceSelect.addEventListener('change', function() {
    districtSelect.innerHTML = '<option value="">Select District</option>';
    citySelect.innerHTML = '<option value="">Select City</option>';
    if (this.value && locationData[this.value]) {
        for (const district in locationData[this.value]) {
            const option = document.createElement('option');
            option.value = district;
            option.textContent = district;
            districtSelect.appendChild(option);
        }
    }
});

districtSelect.addEventListener('change', function() {
    citySelect.innerHTML = '<option value="">Select City</option>';
    const province = provinceSelect.value;
    if (province && this.value && locationData[province][this.value]) {
        for (const city of locationData[province][this.value]) {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        }
    }
});
    </script>
</body>
</html>