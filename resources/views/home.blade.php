<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Management System - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="#">BMS</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ url('login') }}">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('signup') }}">Sign Up</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="display-4 mb-3">Blood Management System</h1>
                <p class="lead mb-4">A digital platform connecting donors, hospitals, and administrators to efficiently manage blood donation and inventory. Real-time tracking, smart notifications, and multilingual support for Rwanda and beyond.</p>
                <a href="{{ url('signup') }}" class="btn btn-danger btn-lg">Get Started</a>
            </div>
        </div>
    </div>
    <footer class="text-center py-4 bg-white border-top mt-5">
        <small>&copy; {{ date('Y') }} Blood Management System. All rights reserved.</small>
    </footer>
</body>
</html>