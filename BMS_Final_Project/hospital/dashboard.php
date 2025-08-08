<?php
require_once '../includes/auth.php';
$auth->requireRole('hospital');

$user = $auth->getCurrentUser();

// Get hospital information
try {
    $hospitalQuery = $db->prepare("
        SELECT * FROM hospitals WHERE user_id = ?
    ");
    $hospitalQuery->execute([$user['id']]);
    $hospital = $hospitalQuery->fetch();
    
    // Get recent blood requests
    $requestsQuery = $db->prepare("
        SELECT * FROM blood_requests 
        WHERE hospital_id = ?
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $requestsQuery->execute([$hospital['id']]);
    $requests = $requestsQuery->fetchAll();
    
    // Get request statistics
    $requestStats = $db->prepare("
        SELECT status, COUNT(*) as count
        FROM blood_requests 
        WHERE hospital_id = ?
        GROUP BY status
    ");
    $requestStats->execute([$hospital['id']]);
    $requestStatistics = $requestStats->fetchAll();
    
    // Get total requests and units
    $totalStats = $db->prepare("
        SELECT COUNT(*) as total_requests, 
               SUM(units_required) as total_units_requested,
               SUM(CASE WHEN status = 'completed' THEN units_required ELSE 0 END) as units_received
        FROM blood_requests 
        WHERE hospital_id = ?
    ");
    $totalStats->execute([$hospital['id']]);
    $stats = $totalStats->fetch();
    
    // Get blood availability from all blood banks
    $bloodAvailability = $db->query("
        SELECT blood_type, 
               SUM(units_available) as total_available,
               SUM(CASE WHEN status = 'urgent' THEN 1 ELSE 0 END) as urgent_count
        FROM blood_inventory 
        GROUP BY blood_type
        ORDER BY blood_type
    ")->fetchAll();
    
    // Get recent activities
    $recentActivities = $db->prepare("
        SELECT 'request' as type, created_at, blood_type, units_required, status, patient_name
        FROM blood_requests 
        WHERE hospital_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $recentActivities->execute([$hospital['id']]);
    $activities = $recentActivities->fetchAll();
    
} catch (PDOException $e) {
    error_log("Hospital dashboard error: " . $e->getMessage());
    $hospital = $requests = $requestStatistics = $stats = $bloodAvailability = $activities = [];
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-hospital me-2"></i>Hospital Dashboard
        </h1>
    </div>
</div>

<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4>Welcome, <?php echo htmlspecialchars($hospital['hospital_name'] ?? $user['name']); ?>!</h4>
                        <p class="mb-0">
                            Region: <strong><?php echo htmlspecialchars($hospital['region'] ?? 'Not specified'); ?></strong>
                            <?php if ($hospital['license_number']): ?>
                                | License: <strong><?php echo htmlspecialchars($hospital['license_number']); ?></strong>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="requests.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Blood Request
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="dashboard-label">Total Requests</div>
                        <div class="dashboard-stat text-primary">
                            <?php echo $stats['total_requests'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                        <div class="dashboard-label">Units Received</div>
                        <div class="dashboard-stat text-success">
                            <?php echo $stats['units_received'] ?? 0; ?>
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
        <div class="card dashboard-card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="dashboard-label">Pending Requests</div>
                        <div class="dashboard-stat text-info">
                            <?php 
                            $pendingCount = 0;
                            foreach ($requestStatistics as $stat) {
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

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="dashboard-label">Urgent Requests</div>
                        <div class="dashboard-stat text-warning">
                            <?php 
                            $urgentCount = 0;
                            foreach ($requestStatistics as $stat) {
                                if ($stat['status'] === 'urgent') {
                                    $urgentCount = $stat['count'];
                                    break;
                                }
                            }
                            echo $urgentCount;
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Blood Availability and Recent Requests -->
<div class="row">
    <div class="col-lg-8">
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Blood Requests</h6>
                <a href="requests.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($requests)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5>No blood requests yet</h5>
                        <p class="text-muted">Start by creating your first blood request!</p>
                        <a href="requests.php" class="btn btn-primary">Create Request</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Blood Type</th>
                                    <th>Units</th>
                                    <th>Patient</th>
                                    <th>Status</th>
                                    <th>Urgency</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                                    <td>
                                        <span class="blood-type-badge blood-type-<?php echo strtolower(str_replace(['+', '-'], ['-plus', '-minus'], $request['blood_type'])); ?>">
                                            <?php echo $request['blood_type']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $request['units_required']; ?> units</span>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['patient_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $request['status']; ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $request['urgency'] === 'emergency' ? 'danger' : ($request['urgency'] === 'urgent' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($request['urgency']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Blood Availability</h6>
            </div>
            <div class="card-body">
                <?php if (empty($bloodAvailability)): ?>
                    <div class="text-center py-3">
                        <i class="fas fa-tint fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No blood availability data</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($bloodAvailability as $blood): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="blood-type-badge blood-type-<?php echo strtolower(str_replace(['+', '-'], ['-plus', '-minus'], $blood['blood_type'])); ?>">
                                    <?php echo $blood['blood_type']; ?>
                                </span>
                            </div>
                            <div class="text-end">
                                <strong><?php echo $blood['total_available']; ?> units</strong>
                                <?php if ($blood['urgent_count'] > 0): ?>
                                    <br><small class="text-danger"><?php echo $blood['urgent_count']; ?> banks urgent</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="../bloodbank/inventory.php" class="btn btn-info w-100">
                        <i class="fas fa-search me-2"></i>Check Detailed Inventory
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Statistics Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card dashboard-card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Request Status Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="requestStatusChart" width="400" height="200"></canvas>
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
                        <a href="requests.php" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>New Blood Request
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="requests.php" class="btn btn-warning w-100">
                            <i class="fas fa-clock me-2"></i>Pending Requests
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="../bloodbank/inventory.php" class="btn btn-info w-100">
                            <i class="fas fa-search me-2"></i>Check Availability
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="../admin/analytics.php" class="btn btn-success w-100">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Request Status Chart
    const ctx = document.getElementById('requestStatusChart').getContext('2d');
    const requestData = <?php echo json_encode($requestStatistics); ?>;
    
    const labels = requestData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
    const data = requestData.map(item => item.count);
    const colors = ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d'];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors.slice(0, data.length),
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