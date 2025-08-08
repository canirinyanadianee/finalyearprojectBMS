@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
        </h1>
    </div>
</div>
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="dashboard-label">Total Users</div>
                        <div class="dashboard-stat text-primary">
                            {{ $userStats->sum('count') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="dashboard-label">Total Donations</div>
                        <div class="dashboard-stat text-success">
                            {{ $donationStats->total_donations ?? 0 }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-heart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="dashboard-label">Blood Units</div>
                        <div class="dashboard-stat text-info">
                            {{ $donationStats->total_units ?? 0 }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tint fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="dashboard-label">Pending Requests</div>
                        <div class="dashboard-stat text-warning">
                            {{ $requestStats->firstWhere('status', 'pending')->count ?? 0 }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Charts Row (placeholders) -->
<div class="row mb-4">
    <div class="col-xl-8 col-lg-7">
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Blood Donation Trends</h6>
            </div>
            <div class="card-body">
                <canvas id="donationTrendChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">User Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="userDistributionChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
<!-- User Statistics and Recent Activities -->
<div class="row">
    <div class="col-lg-6">
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Statistics</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalUsers = $userStats->sum('count'); @endphp
                            @foreach($userStats as $stat)
                                <tr>
                                    <td><span class="badge bg-primary">{{ ucfirst($stat->role) }}</span></td>
                                    <td>{{ $stat->count }}</td>
                                    <td>{{ $totalUsers > 0 ? round(($stat->count / $totalUsers) * 100, 1) : 0 }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($recentActivities as $activity)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-{{ $activity['type'] === 'request' ? 'hospital' : 'heart' }} me-2"></i>
                            <strong>{{ ucfirst($activity['type']) }}</strong>
                            <span class="blood-type-badge blood-type-{{ strtolower(str_replace(['+', '-'], ['-plus', '-minus'], $activity['blood_type'])) }}">
                                {{ $activity['blood_type'] }}
                            </span>
                            {{ $activity['units_required'] ?? $activity['units_donated'] }} units
                        </div>
                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($activity['created_at'])->format('M j, Y') }}
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card dashboard-card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.users') }}" class="btn btn-primary w-100">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.analytics') }}" class="btn btn-info w-100">
                            <i class="fas fa-chart-bar me-2"></i>View Analytics
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('hospital.requests') }}" class="btn btn-warning w-100">
                            <i class="fas fa-clipboard-list me-2"></i>Blood Requests
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('bloodbank.inventory') }}" class="btn btn-success w-100">
                            <i class="fas fa-boxes me-2"></i>Inventory Status
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Sample data for charts - in a real application, this would come from AJAX calls
// You can replace this with real chart data as needed
</script>
@endsection 