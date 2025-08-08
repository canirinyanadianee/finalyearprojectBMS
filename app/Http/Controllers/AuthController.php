<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\BloodBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            if ($user->status !== 'active') {
                Auth::logout();
                return redirect()->back()->withErrors(['email' => 'Your account is not active.']);
            }

            $request->session()->regenerate();

            // Redirect based on role
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'donor':
                    return redirect()->route('donor.dashboard');
                case 'hospital':
                    return redirect()->route('hospital.dashboard');
                case 'blood_bank':
                    return redirect()->route('bloodbank.dashboard');
                default:
                    return redirect()->route('home');
            }
        }

        return redirect()->back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:donor,hospital,blood_bank',
            'blood_type' => 'required_if:role,donor|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'phone' => 'required|string|max:15',
            'address' => 'required|string',
            'hospital_name' => 'required_if:role,hospital|string|max:200',
            'bank_name' => 'required_if:role,blood_bank|string|max:200',
            'license_number' => 'required_if:role,hospital,blood_bank|string|max:50',
            'region' => 'required_if:role,hospital,blood_bank|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active',
        ]);

        // Create role-specific profile
        switch ($request->role) {
            case 'donor':
                Donor::create([
                    'user_id' => $user->id,
                    'blood_type' => $request->blood_type,
                    'phone' => $request->phone,
                    'address' => $request->address,
                ]);
                break;
            case 'hospital':
                Hospital::create([
                    'user_id' => $user->id,
                    'hospital_name' => $request->hospital_name,
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'license_number' => $request->license_number,
                    'region' => $request->region,
                ]);
                break;
            case 'blood_bank':
                BloodBank::create([
                    'user_id' => $user->id,
                    'bank_name' => $request->bank_name,
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'license_number' => $request->license_number,
                    'region' => $request->region,
                ]);
                break;
        }

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Registration successful!');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Here you would typically send a password reset email
        // For now, we'll just show a success message
        return redirect()->back()->with('success', 'Password reset link sent to your email.');
    }
} 