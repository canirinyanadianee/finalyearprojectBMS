<?php
require_once 'includes/auth.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $message = 'Please enter your email address.';
        $message_type = 'danger';
    } else {
        if ($auth->resetPassword($email)) {
            $message = 'Password reset instructions have been sent to your email address.';
            $message_type = 'success';
        } else {
            $message = 'Email address not found or account is inactive.';
            $message_type = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Blood Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="light-theme">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><i class="fas fa-key me-2"></i>Forgot Password</h2>
                <p class="mb-0">Reset your password</p>
            </div>
            <div class="login-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-text">Enter the email address associated with your account.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                    
                    <div class="text-center">
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i>Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Theme Toggle Button -->
    <button class="btn btn-outline-light position-fixed" id="themeToggle" style="top: 20px; right: 20px;">
        <i class="fas fa-moon"></i>
    </button>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html> 