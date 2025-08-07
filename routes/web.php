<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/login', function () {
    return 'Login page coming soon';
})->name('login');

Route::get('/signup', function () {
    return 'Signup page coming soon';
})->name('signup');
