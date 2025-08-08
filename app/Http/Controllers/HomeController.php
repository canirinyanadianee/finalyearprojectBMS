<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Show the home page
     */
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();
            
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
            }
        }
        
        return view('home');
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = Auth::user();
        
        return view('profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check current password if provided
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
        }

        // Update user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->filled('new_password')) {
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
        }

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
} 