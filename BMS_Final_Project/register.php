<?php
require_once 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Additional fields based on role
    $blood_type = $_POST['blood_type'] ?? '';
    $hospital_name = $_POST['hospital_name'] ?? '';
    $license_number = $_POST['license_number'] ?? '';
    $region = $_POST['region'] ?? '';
    $bank_name = $_POST['bank_name'] ?? '';

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!in_array($role, ['donor', 'hospital', 'blood_bank'])) {
        $error = 'Invalid role selected.';
    } else {
        try {
            // Check if email already exists
            $checkEmail = $db->prepare("SELECT id FROM users WHERE email = ?");
            $checkEmail->execute([$email]);
            
            if ($checkEmail->rowCount() > 0) {
                $error = 'Email address already exists.';
            } else {
                // Start transaction
                $db->beginTransaction();
                
                // Create user account
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $createUser = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $createUser->execute([$name, $email, $hashedPassword, $role]);
                $userId = $db->lastInsertId();
                
                // Create role-specific record
                if ($role === 'donor') {
                    $createDonor = $db->prepare("INSERT INTO donors (user_id, blood_type, phone, address, eligibility_status) VALUES (?, ?, ?, ?, 'eligible')");
                    $createDonor->execute([$userId, $blood_type, $phone, $address]);
                } elseif ($role === 'hospital') {
                    $createHospital = $db->prepare("INSERT INTO hospitals (user_id, hospital_name, license_number, region, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
                    $createHospital->execute([$userId, $hospital_name, $license_number, $region, $phone, $address]);
                } elseif ($role === 'blood_bank') {
                    $createBloodBank = $db->prepare("INSERT INTO blood_banks (user_id, bank_name, license_number, region, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
                    $createBloodBank->execute([$userId, $bank_name, $license_number, $region, $phone, $address]);
                }
                
                $db->commit();
                $success = 'Account created successfully! You can now login.';
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $error = 'Error creating account. Please try again.';
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Blood Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="light-theme">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><i class="fas fa-user-plus me-2"></i>Create Account</h2>
                <p class="text-muted">Join the Blood Management System</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <br><a href="login.php" class="alert-link">Click here to login</a>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Account Type *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Account Type</option>
                            <option value="donor" <?php echo ($_POST['role'] ?? $_GET['role'] ?? '') === 'donor' ? 'selected' : ''; ?>>Blood Donor</option>
                            <option value="hospital" <?php echo ($_POST['role'] ?? $_GET['role'] ?? '') === 'hospital' ? 'selected' : ''; ?>>Hospital</option>
                            <option value="blood_bank" <?php echo ($_POST['role'] ?? $_GET['role'] ?? '') === 'blood_bank' ? 'selected' : ''; ?>>Blood Bank</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="region" class="form-label">Region</label>
                                <input type="text" class="form-control" id="region" name="region" value="<?php echo htmlspecialchars($_POST['region'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Donor-specific fields -->
                    <div id="donor-fields" style="display: none;">
                        <div class="mb-3">
                            <label for="blood_type" class="form-label">Blood Type *</label>
                            <select class="form-select" id="blood_type" name="blood_type">
                                <option value="">Select Blood Type</option>
                                <option value="A+" <?php echo ($_POST['blood_type'] ?? '') === 'A+' ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo ($_POST['blood_type'] ?? '') === 'A-' ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo ($_POST['blood_type'] ?? '') === 'B+' ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo ($_POST['blood_type'] ?? '') === 'B-' ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo ($_POST['blood_type'] ?? '') === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo ($_POST['blood_type'] ?? '') === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo ($_POST['blood_type'] ?? '') === 'O+' ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo ($_POST['blood_type'] ?? '') === 'O-' ? 'selected' : ''; ?>>O-</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Hospital-specific fields -->
                    <div id="hospital-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hospital_name" class="form-label">Hospital Name *</label>
                                    <input type="text" class="form-control" id="hospital_name" name="hospital_name" value="<?php echo htmlspecialchars($_POST['hospital_name'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="license_number" class="form-label">License Number</label>
                                    <input type="text" class="form-control" id="license_number" name="license_number" value="<?php echo htmlspecialchars($_POST['license_number'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Blood Bank-specific fields -->
                    <div id="bloodbank-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bank_name" class="form-label">Blood Bank Name *</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($_POST['bank_name'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="license_number" class="form-label">License Number</label>
                                    <input type="text" class="form-control" id="license_number" name="license_number" value="<?php echo htmlspecialchars($_POST['license_number'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                    
                    <div class="text-center">
                        <span class="text-muted">Already have an account?</span>
                        <a href="login.php" class="text-decoration-none ms-1">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <button class="btn btn-outline-light position-fixed" id="themeToggle" style="top: 20px; right: 20px;">
        <i class="fas fa-moon"></i>
    </button>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Show/hide role-specific fields
        document.getElementById('role').addEventListener('change', function() {
            const role = this.value;
            const donorFields = document.getElementById('donor-fields');
            const hospitalFields = document.getElementById('hospital-fields');
            const bloodbankFields = document.getElementById('bloodbank-fields');
            
            // Hide all fields first
            donorFields.style.display = 'none';
            hospitalFields.style.display = 'none';
            bloodbankFields.style.display = 'none';
            
            // Show relevant fields
            if (role === 'donor') {
                donorFields.style.display = 'block';
            } else if (role === 'hospital') {
                hospitalFields.style.display = 'block';
            } else if (role === 'blood_bank') {
                bloodbankFields.style.display = 'block';
            }
        });
        
        // Trigger change event on page load if role is already selected
        if (document.getElementById('role').value) {
            document.getElementById('role').dispatchEvent(new Event('change'));
        }
    </script>
</body>
</html> 