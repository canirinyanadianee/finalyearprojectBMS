<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BloodRequest;
use App\Models\Donation;
use App\Models\BloodInventory;
use App\Models\Notification;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function dashboard()
    {
        // Total users by role
        $userStats = \App\Models\User::select('role', \DB::raw('COUNT(*) as count'))
            ->where('status', 'active')
            ->groupBy('role')
            ->get();

        // Total blood requests by status
        $requestStats = \App\Models\BloodRequest::select('status', \DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Total donations and units
        $donationStats = \App\Models\Donation::selectRaw('COUNT(*) as total_donations, SUM(units_donated) as total_units')
            ->where('status', 'completed')
            ->first();

        // Inventory status
        $inventoryStats = \App\Models\BloodInventory::select('status', \DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Recent activities (last 7 days)
        $recentRequests = \App\Models\BloodRequest::with('hospital')
            ->where('created_at', '>=', now()->subDays(7))
            ->get()
            ->map(function($r) {
                return [
                    'type' => 'request',
                    'created_at' => $r->created_at,
                    'blood_type' => $r->blood_type,
                    'units_required' => $r->units_required,
                    'name' => $r->hospital->hospital_name ?? '',
                ];
            });
        $recentDonations = \App\Models\Donation::with('bloodBank')
            ->where('created_at', '>=', now()->subDays(7))
            ->get()
            ->map(function($d) {
                return [
                    'type' => 'donation',
                    'created_at' => $d->created_at,
                    'blood_type' => $d->blood_type,
                    'units_donated' => $d->units_donated,
                    'name' => $d->bloodBank->bank_name ?? '',
                ];
            });
        $recentActivities = $recentRequests->merge($recentDonations)->sortByDesc('created_at')->take(10);

        return view('admin.dashboard', compact('userStats', 'requestStats', 'donationStats', 'inventoryStats', 'recentActivities'));
    }

    /**
     * Show all users
     */
    public function users()
    {
        $users = User::with(['donor', 'hospital', 'bloodBank'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.users', compact('users'));
    }

    /**
     * Show specific user
     */
    public function showUser(User $user)
    {
        $user->load(['donor', 'hospital', 'bloodBank']);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Update user status
     */
    public function updateUserStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $user->update(['status' => $request->status]);

        // Create notification
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Account Status Updated',
            'message' => "Your account status has been updated to {$request->status}.",
            'type' => $request->status === 'active' ? 'success' : 'warning',
        ]);

        return redirect()->back()->with('success', 'User status updated successfully!');
    }

    /**
     * Show analytics
     */
    public function analytics()
    {
        // Blood type distribution
        $bloodTypeStats = Donation::where('status', 'completed')
            ->select('blood_type', DB::raw('SUM(units_donated) as total_units'))
            ->groupBy('blood_type')
            ->get();

        // Monthly donation trends
        $monthlyDonations = Donation::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(units_donated) as total_units')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Request status distribution
        $requestStatusStats = BloodRequest::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // User registration trends
        $userRegistrationTrends = User::where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.analytics', compact(
            'bloodTypeStats',
            'monthlyDonations',
            'requestStatusStats',
            'userRegistrationTrends'
        ));
    }

    /**
     * Show reports
     */
    public function reports()
    {
        $reports = Report::with('generatedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.reports', compact('reports'));
    }
} 