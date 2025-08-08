<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\BloodBankController;
use App\Http\Controllers\BloodRequestController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BloodInventoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    
    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::put('/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('users.status');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    });

    // Donor routes
    Route::middleware('role:donor')->prefix('donor')->name('donor.')->group(function () {
        Route::get('/dashboard', [DonorController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [DonorController::class, 'profile'])->name('profile');
        Route::put('/profile', [DonorController::class, 'updateProfile'])->name('profile.update');
        Route::get('/donations', [DonorController::class, 'donations'])->name('donations');
        Route::get('/appointments', [DonorController::class, 'appointments'])->name('appointments');
        Route::post('/appointments', [DonorController::class, 'storeAppointment'])->name('appointments.store');
        Route::put('/appointments/{appointment}', [DonorController::class, 'updateAppointment'])->name('appointments.update');
        Route::delete('/appointments/{appointment}', [DonorController::class, 'cancelAppointment'])->name('appointments.cancel');
    });

    // Hospital routes
    Route::middleware('role:hospital')->prefix('hospital')->name('hospital.')->group(function () {
        Route::get('/dashboard', [HospitalController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [HospitalController::class, 'profile'])->name('profile');
        Route::put('/profile', [HospitalController::class, 'updateProfile'])->name('profile.update');
        Route::get('/requests', [HospitalController::class, 'requests'])->name('requests');
        Route::get('/requests/create', [HospitalController::class, 'createRequest'])->name('requests.create');
        Route::post('/requests', [HospitalController::class, 'storeRequest'])->name('requests.store');
        Route::get('/requests/{request}', [HospitalController::class, 'showRequest'])->name('requests.show');
        Route::put('/requests/{request}', [HospitalController::class, 'updateRequest'])->name('requests.update');
        Route::delete('/requests/{request}', [HospitalController::class, 'cancelRequest'])->name('requests.cancel');
    });

    // Blood Bank routes
    Route::middleware('role:blood_bank')->prefix('bloodbank')->name('bloodbank.')->group(function () {
        Route::get('/dashboard', [BloodBankController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [BloodBankController::class, 'profile'])->name('profile');
        Route::put('/profile', [BloodBankController::class, 'updateProfile'])->name('profile.update');
        Route::get('/inventory', [BloodBankController::class, 'inventory'])->name('inventory');
        Route::get('/donations', [BloodBankController::class, 'donations'])->name('donations');
        Route::get('/appointments', [BloodBankController::class, 'appointments'])->name('appointments');
        Route::put('/appointments/{appointment}', [BloodBankController::class, 'updateAppointment'])->name('appointments.update');
    });

    // Blood Inventory routes (for blood banks)
    Route::middleware('role:blood_bank')->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [BloodInventoryController::class, 'index'])->name('index');
        Route::get('/create', [BloodInventoryController::class, 'create'])->name('create');
        Route::post('/', [BloodInventoryController::class, 'store'])->name('store');
        Route::get('/{inventory}', [BloodInventoryController::class, 'show'])->name('show');
        Route::get('/{inventory}/edit', [BloodInventoryController::class, 'edit'])->name('edit');
        Route::put('/{inventory}', [BloodInventoryController::class, 'update'])->name('update');
        Route::delete('/{inventory}', [BloodInventoryController::class, 'destroy'])->name('destroy');
    });

    // Blood Request routes (for admins and blood banks)
    Route::middleware('role:admin,blood_bank')->prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [BloodRequestController::class, 'index'])->name('index');
        Route::get('/{request}', [BloodRequestController::class, 'show'])->name('show');
        Route::put('/{request}/approve', [BloodRequestController::class, 'approve'])->name('approve');
        Route::put('/{request}/reject', [BloodRequestController::class, 'reject'])->name('reject');
        Route::put('/{request}/complete', [BloodRequestController::class, 'complete'])->name('complete');
    });

    // Donation routes (for blood banks)
    Route::middleware('role:blood_bank')->prefix('donations')->name('donations.')->group(function () {
        Route::get('/', [DonationController::class, 'index'])->name('index');
        Route::get('/create', [DonationController::class, 'create'])->name('create');
        Route::post('/', [DonationController::class, 'store'])->name('store');
        Route::get('/{donation}', [DonationController::class, 'show'])->name('show');
        Route::get('/{donation}/edit', [DonationController::class, 'edit'])->name('edit');
        Route::put('/{donation}', [DonationController::class, 'update'])->name('update');
        Route::delete('/{donation}', [DonationController::class, 'destroy'])->name('destroy');
    });

    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        Route::put('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Report routes (for admins)
    Route::middleware('role:admin')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::post('/', [ReportController::class, 'store'])->name('store');
        Route::get('/{report}', [ReportController::class, 'show'])->name('show');
        Route::get('/{report}/download', [ReportController::class, 'download'])->name('download');
    });

    // Profile route (for all authenticated users)
    Route::get('/profile', [HomeController::class, 'profile'])->name('profile');
    Route::put('/profile', [HomeController::class, 'updateProfile'])->name('profile.update');
});

// Fallback route
Route::fallback(function () {
    return view('errors.404');
});
