@extends('layouts.app')
@section('title', 'Blood Management System - Home')
@section('content')
<div class="container-fluid bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">
                    <i class="fas fa-heartbeat me-3"></i>
                    Blood Management System
                </h1>
                <p class="lead mb-4">
                    Efficiently manage blood donations, requests, and inventory across hospitals, blood banks, and donors.
                    Join our network to save lives through better blood management.
                </p>
                <div class="d-flex gap-3">
                    <a href="{{ route('register') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Get Started
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="fas fa-heartbeat" style="font-size: 15rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>
<div id="features" class="container py-5">
    <div class="row text-center mb-5">
        <div class="col-12">
            <h2 class="display-5 fw-bold mb-3">Join Our Network</h2>
            <p class="lead text-muted">Choose your role and start contributing to our blood management system</p>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-4 col-md-6">
            <div class="card dashboard-card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-user-friends fa-3x text-danger"></i>
                    </div>
                    <h4 class="card-title">Blood Donor</h4>
                    <p class="card-text text-muted">
                        Register as a blood donor to help save lives. Track your donation history, 
                        schedule appointments, and check your eligibility status.
                    </p>
                    <ul class="list-unstyled text-start">
                        <li><i class="fas fa-check text-success me-2"></i>Donation history tracking</li>
                        <li><i class="fas fa-check text-success me-2"></i>Appointment scheduling</li>
                        <li><i class="fas fa-check text-success me-2"></i>Eligibility status</li>
                        <li><i class="fas fa-check text-success me-2"></i>Personal dashboard</li>
                    </ul>
                    <a href="{{ route('register') }}?role=donor" class="btn btn-danger w-100 mt-3">
                        <i class="fas fa-user-plus me-2"></i>Register as Donor
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card dashboard-card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-hospital fa-3x text-primary"></i>
                    </div>
                    <h4 class="card-title">Hospital</h4>
                    <p class="card-text text-muted">
                        Register your hospital to request blood units, track inventory status, 
                        and manage blood requests efficiently.
                    </p>
                    <ul class="list-unstyled text-start">
                        <li><i class="fas fa-check text-success me-2"></i>Blood request management</li>
                        <li><i class="fas fa-check text-success me-2"></i>Inventory status tracking</li>
                        <li><i class="fas fa-check text-success me-2"></i>Request history</li>
                        <li><i class="fas fa-check text-success me-2"></i>Hospital dashboard</li>
                    </ul>
                    <a href="{{ route('register') }}?role=hospital" class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-hospital me-2"></i>Register Hospital
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card dashboard-card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-tint fa-3x text-info"></i>
                    </div>
                    <h4 class="card-title">Blood Bank</h4>
                    <p class="card-text text-muted">
                        Register your blood bank to manage inventory, track donations, 
                        and coordinate with hospitals and donors.
                    </p>
                    <ul class="list-unstyled text-start">
                        <li><i class="fas fa-check text-success me-2"></i>Inventory management</li>
                        <li><i class="fas fa-check text-success me-2"></i>Donation tracking</li>
                        <li><i class="fas fa-check text-success me-2"></i>Donor management</li>
                        <li><i class="fas fa-check text-success me-2"></i>Blood bank dashboard</li>
                    </ul>
                    <a href="{{ route('register') }}?role=blood_bank" class="btn btn-info w-100 mt-3">
                        <i class="fas fa-tint me-2"></i>Register Blood Bank
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid bg-light py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-5 fw-bold mb-3">How It Works</h2>
                <p class="lead text-muted">Simple steps to join our blood management network</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-user-plus fa-2x"></i>
                    </div>
                </div>
                <h5>1. Register</h5>
                <p class="text-muted">Create your account with your specific role</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-sign-in-alt fa-2x"></i>
                    </div>
                </div>
                <h5>2. Login</h5>
                <p class="text-muted">Access your personalized dashboard</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-cogs fa-2x"></i>
                    </div>
                </div>
                <h5>3. Manage</h5>
                <p class="text-muted">Use the system features for your role</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="fas fa-heart fa-2x"></i>
                    </div>
                </div>
                <h5>4. Save Lives</h5>
                <p class="text-muted">Contribute to saving lives through better blood management</p>
            </div>
        </div>
    </div>
</div>
@endsection