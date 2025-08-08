<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Blood Management System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            @auth
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
                <div class="position-sticky pt-3">
                    <h4 class="text-white text-center">BMS System</h4>
                    <ul class="nav flex-column">
                        @if(Auth::user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('admin.users') }}">Users</a>
                            </li>
                        @elseif(Auth::user()->isDonor())
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('donor.dashboard') }}">Dashboard</a>
                            </li>
                        @elseif(Auth::user()->isHospital())
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('hospital.dashboard') }}">Dashboard</a>
                            </li>
                        @elseif(Auth::user()->isBloodBank())
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('bloodbank.dashboard') }}">Dashboard</a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('profile') }}">Profile</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link text-white">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
            @endauth
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 