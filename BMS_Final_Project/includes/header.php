<?php
require_once 'auth.php';
$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="light-theme">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-heartbeat me-2"></i>BMS
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($auth->isLoggedIn()): ?>
                        <?php if ($user['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../admin/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../admin/users.php">Users</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../admin/analytics.php">Analytics</a>
                            </li>
                        <?php elseif ($user['role'] === 'donor'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../donor/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../donor/history.php">Donation History</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../donor/appointments.php">Appointments</a>
                            </li>
                        <?php elseif ($user['role'] === 'hospital'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../hospital/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../hospital/requests.php">Blood Requests</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../hospital/inventory.php">Inventory</a>
                            </li>
                        <?php elseif ($user['role'] === 'blood_bank'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../bloodbank/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../bloodbank/inventory.php">Inventory</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../bloodbank/donors.php">Donors</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($auth->isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-outline-light btn-sm ms-2" id="themeToggle">
                                <i class="fas fa-moon"></i>
                            </button>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?> 