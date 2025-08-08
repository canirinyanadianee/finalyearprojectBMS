<?php
require_once '../includes/auth.php';
$auth->requireRole('admin');

// Get dashboard statistics
try {
    // Total users by role
    $userStats = $db->query("
        SELECT role, COUNT(*) as count 
        FROM users 
        WHERE status = 'active' 
        GROUP BY role
    ")->fetchAll();
    
    // Total blood requests
    $requestStats = $db->query("
        SELECT status, COUNT(*) as count 
        FROM blood_requests 
        GROUP BY status
    ")->fetchAll();
    
    // Total donations
    $donationStats = $db->query("
        SELECT COUNT(*) as total_donations,
               SUM(units_donated) as total_units
        FROM donations 
        WHERE status = 'completed'
    ")->fetch();
    
    // Inventory status
    $inventoryStats = $db->query("
        SELECT status, COUNT(*) as count 
        FROM blood_inventory 
        GROUP BY status
    ")->fetchAll();
    
    // Recent activities
    $recentActivities = $db->query("
        SELECT 'request' as type, br.created_at, br.blood_type, br.units_required, h.hospital_name
        FROM blood_requests br
        JOIN hospitals h ON br.hospital_id = h.id
        WHERE br.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT 'donation' as type, d.created_at, d.blood_type, d.units_donated, bb.bank_name
        FROM donations d
        JOIN blood_banks bb ON d.blood_bank_id = bb.id
        WHERE d.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY created_at DESC
        LIMIT 10
    ")->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $userStats = $requestStats = $donationStats = $inventoryStats = $recentActivities = [];
}

include '../includes/header.php';
?>

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
                            <?php echo array_sum(array_column($userStats, 'count')); ?>
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
                            <?php echo $donationStats['total_donations'] ?? 0; ?>
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
                            <?php echo $donationStats['total_units'] ?? 0; ?>
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
                            <?php 
                            $pendingCount = 0;
                            foreach ($requestStats as $stat) {
                                if ($stat['status'] === 'pending') {
                                    $pendingCount = $stat['count'];
                                    break;
                                }
                            }
                            echo $pendingCount;
                            ?>
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

<!-- Charts Row -->
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
                            <?php 
                            $totalUsers = array_sum(array_column($userStats, 'count'));
                            foreach ($userStats as $stat): 
                                $percentage = $totalUsers > 0 ? round(($stat['count'] / $totalUsers) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary"><?php echo ucfirst($stat['role']); ?></span>
                                </td>
                                <td><?php echo $stat['count']; ?></td>
                                <td><?php echo $percentage; ?>%</td>
                            </tr>
                            <?php endforeach; ?>
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
                    <?php foreach ($recentActivities as $activity): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-<?php echo $activity['type'] === 'request' ? 'hospital' : 'heart'; ?> me-2"></i>
                            <strong><?php echo ucfirst($activity['type']); ?></strong>
                            <span class="blood-type-badge blood-type-<?php echo strtolower(str_replace(['+', '-'], ['-plus', '-minus'], $activity['blood_type'])); ?>">
                                <?php echo $activity['blood_type']; ?>
                            </span>
                            <?php echo $activity['units_required'] ?? $activity['units_donated']; ?> units
                        </div>
                        <small class="text-muted">
                            <?php echo date('M j, Y', strtotime($activity['created_at'])); ?>
                        </small>
                    </div>
                    <?php endforeach; ?>
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
                        <a href="users.php" class="btn btn-primary w-100">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="analytics.php" class="btn btn-info w-100">
                            <i class="fas fa-chart-bar me-2"></i>View Analytics
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="../hospital/requests.php" class="btn btn-warning w-100">
                            <i class="fas fa-clipboard-list me-2"></i>Blood Requests
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="../bloodbank/inventory.php" class="btn btn-success w-100">
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
document.addEventListener('DOMContentLoaded', function() {
    // Donation Trend Chart
    const trendCtx = document.getElementById('donationTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Donations',
                data: [65, 59, 80, 81, 56, 55],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // User Distribution Chart
    const userCtx = document.getElementById('userDistributionChart').getContext('2d');
    new Chart(userCtx, {
        type: 'doughnut',
        data: {
            labels: ['Donors', 'Hospitals', 'Blood Banks', 'Admins'],
            datasets: [{
                data: [45, 25, 20, 10],
                backgroundColor: ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?> 