<?php
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        if ($auth->login($email, $password)) {
            $user = $auth->getCurrentUser();
            switch ($user['role']) {
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'donor':
                    header('Location: donor/dashboard.php');
                    break;
                case 'hospital':
                    header('Location: hospital/dashboard.php');
                    break;
                case 'blood_bank':
                    header('Location: bloodbank/dashboard.php');
                    break;
                default:
                    header('Location: index.php');
            }
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blood Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="light-theme">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><i class="fas fa-heartbeat me-2"></i>BMS Login</h2>
                <p class="mb-0">Blood Management System</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                    
                    <div class="text-center">
                        <a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center mb-3">
                    <span class="text-muted">Don't have an account?</span>
                    <a href="register.php" class="text-decoration-none ms-1">Create one here</a>
                </div>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <h6>Admin Access:</h6>
                    <small class="text-muted">admin@bms.com</small><br>
                    <small>password</small>
                </div>
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