<?php
require_once 'includes/auth.php';
$auth->requireLogin();

$user = $auth->getCurrentUser();
$message = '';
$message_type = '';

// Get user details based on role
try {
    if ($user['role'] === 'donor') {
        $detailsQuery = $db->prepare("SELECT * FROM donors WHERE user_id = ?");
        $detailsQuery->execute([$user['id']]);
        $userDetails = $detailsQuery->fetch();
    } elseif ($user['role'] === 'hospital') {
        $detailsQuery = $db->prepare("SELECT * FROM hospitals WHERE user_id = ?");
        $detailsQuery->execute([$user['id']]);
        $userDetails = $detailsQuery->fetch();
    } elseif ($user['role'] === 'blood_bank') {
        $detailsQuery = $db->prepare("SELECT * FROM blood_banks WHERE user_id = ?");
        $detailsQuery->execute([$user['id']]);
        $userDetails = $detailsQuery->fetch();
    } else {
        $userDetails = [];
    }
} catch (PDOException $e) {
    error_log("Profile error: " . $e->getMessage());
    $userDetails = [];
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    try {
        // Update user table
        $updateUser = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $updateUser->execute([$name, $email, $user['id']]);
        
        // Update role-specific details
        if ($user['role'] === 'donor') {
            $updateDetails = $db->prepare("
                UPDATE donors SET phone = ?, address = ? WHERE user_id = ?
            ");
            $updateDetails->execute([$phone, $address, $user['id']]);
        } elseif ($user['role'] === 'hospital') {
            $updateDetails = $db->prepare("
                UPDATE hospitals SET phone = ?, address = ? WHERE user_id = ?
            ");
            $updateDetails->execute([$phone, $address, $user['id']]);
        } elseif ($user['role'] === 'blood_bank') {
            $updateDetails = $db->prepare("
                UPDATE blood_banks SET phone = ?, address = ? WHERE user_id = ?
            ");
            $updateDetails->execute([$phone, $address, $user['id']]);
        }
        
        $message = 'Profile updated successfully!';
        $message_type = 'success';
        
        // Refresh user data
        $user['name'] = $name;
        $user['email'] = $email;
        
    } catch (PDOException $e) {
        $message = 'Error updating profile. Please try again.';
        $message_type = 'danger';
    }
}

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-user me-2"></i>My Profile
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card dashboard-card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($userDetails['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" id="role" 
                                   value="<?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($userDetails['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <?php if ($user['role'] === 'donor' && isset($userDetails['blood_type'])): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="blood_type" class="form-label">Blood Type</label>
                            <input type="text" class="form-control" id="blood_type" 
                                   value="<?php echo htmlspecialchars($userDetails['blood_type']); ?>" readonly>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="eligibility" class="form-label">Eligibility Status</label>
                            <input type="text" class="form-control" id="eligibility" 
                                   value="<?php echo ucfirst($userDetails['eligibility_status'] ?? 'Unknown'); ?>" readonly>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>User ID:</strong><br>
                    <span class="text-muted"><?php echo $user['id']; ?></span>
                </div>
                
                <div class="mb-3">
                    <strong>Account Status:</strong><br>
                    <span class="badge bg-success">Active</span>
                </div>
                
                <div class="mb-3">
                    <strong>Member Since:</strong><br>
                    <span class="text-muted"><?php echo date('F j, Y'); ?></span>
                </div>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <a href="forgot_password.php" class="btn btn-outline-warning">
                        <i class="fas fa-key me-2"></i>Change Password
                    </a>
                    <a href="logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
        
        <?php if ($user['role'] === 'donor'): ?>
        <div class="card dashboard-card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Donor Statistics</h6>
            </div>
            <div class="card-body">
                <?php
                try {
                    $statsQuery = $db->prepare("
                        SELECT COUNT(*) as total_donations, SUM(units_donated) as total_units
                        FROM donations d
                        JOIN donors do ON d.donor_id = do.id
                        WHERE do.user_id = ? AND d.status = 'completed'
                    ");
                    $statsQuery->execute([$user['id']]);
                    $donorStats = $statsQuery->fetch();
                } catch (PDOException $e) {
                    $donorStats = ['total_donations' => 0, 'total_units' => 0];
                }
                ?>
                
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary"><?php echo $donorStats['total_donations']; ?></h4>
                        <small class="text-muted">Total Donations</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success"><?php echo $donorStats['total_units']; ?></h4>
                        <small class="text-muted">Units Donated</small>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 