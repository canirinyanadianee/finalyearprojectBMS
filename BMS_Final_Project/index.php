<?php
require_once 'includes/auth.php';

// Redirect to appropriate dashboard if logged in
if ($auth->isLoggedIn()) {
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
            header('Location: login.php');
    }
    exit();
}

        // If not logged in, redirect to home page
        header('Location: home.php');
        exit();
?> 