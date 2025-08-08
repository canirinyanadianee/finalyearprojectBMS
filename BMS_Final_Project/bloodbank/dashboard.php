<?php
require_once '../includes/auth.php';
$auth->requireRole('blood_bank');

$user = $auth->getCurrentUser();

// Get blood bank information
try {
    $bloodBankQuery = $db->prepare("
        SELECT * FROM blood_banks WHERE user_id = ?
    ");
    $bloodBankQuery->execute([$user['id']]);
    $bloodBank = $bloodBankQuery->fetch();
    
    // Get inventory statistics
    $inventoryStats = $db->prepare("
        SELECT 
            COUNT(*) as total_types,
            SUM(units_available) as total_units,
            SUM(CASE WHEN status = 'urgent' THEN 1 ELSE 0 END) as urgent_count,
            SUM(CASE WHEN status = 'low' THEN 1 ELSE 0 END) as low_count
        FROM blood_inventory 
        WHERE blood_bank_id = ?
    ");
    $inventoryStats->execute([$bloodBank['id']]);
    $inventory = $inventoryStats->fetch();
    
    // Get recent donations
    $donationsQuery = $db->prepare("
        SELECT d.*, u.name as donor_name 
        FROM donations d
        JOIN donors do ON d.donor_id = do.id
        JOIN users u ON do.user_id = u.id
        WHERE d.blood_bank_id = ?
        ORDER BY d.created_at DESC
        LIMIT 5
    ");
    $donationsQuery->execute([$bloodBank['id']]);
    $donations = $donationsQuery->fetchAll();
    
    // Get inventory by blood type
    $inventoryByType = $db->prepare("
        SELECT * FROM blood_inventory 
        WHERE blood_bank_id = ?
        ORDER BY blood_type
    ");
    $inventoryByType->execute([$bloodBank['id']]);
    $inventoryTypes = $inventoryByType->fetchAll();
    
    // Get recent appointments
    $appointmentsQuery = $db->prepare("
        SELECT a.*, u.name as donor_name 
        FROM appointments a
        JOIN donors do ON a.donor_id = do.id
        JOIN users u ON do.user_id = u.id
        WHERE a.blood_bank_id = ? AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC
        LIMIT 5
    ");
    $appointmentsQuery->execute([$bloodBank['id']]);
    $appointments = $appointmentsQuery->fetchAll();
    
    // Get donation statistics
    $donationStats = $db->prepare("
        SELECT 
            COUNT(*) as total_donations,
            SUM(units_donated) as total_units,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_donations
        FROM donations 
        WHERE blood_bank_id = ?
    ");
    $donationStats->execute([$bloodBank['id']]);
    $donationStatistics = $donationStats->fetch();
    
    // Get pending blood requests
    $pendingRequests = $db->query("
        SELECT br.*, h.hospital_name, h.region
        FROM blood_requests br
        JOIN hospitals h ON br.hospital_id = h.id
        WHERE br.status = 'pending'
        ORDER BY br.urgency DESC, br.created_at ASC
        LIMIT 5
    ")->fetchAll();
    
} catch (PDOException $e) {
    error_log("Blood bank dashboard error: " . $e->getMessage());
    $bloodBank = $inventory = $donations = $inventoryTypes = $appointments = $donationStatistics = $pendingRequests = [];
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-tint me-2"></i>Blood Bank Dashboard
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
                        <h4>Welcome, <?php echo htmlspecialchars($bloodBank['bank_name'] ?? $user['name']); ?>!</h4>
                        <p class="mb-0">
                            Region: <strong><?php echo htmlspecialchars($bloodBank['region'] ?? 'Not specified'); ?></strong>
                            <?php if ($bloodBank['license_number']): ?>
                                | License: <strong><?php echo htmlspecialchars($bloodBank['license_number']); ?></strong>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="inventory.php" class="btn btn-primary">
                            <i class="fas fa-boxes me-2"></i>Manage Inventory
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
                        <div class="dashboard-label">Total Units</div>
                        <div class="dashboard-stat text-primary">
                            <?php echo $inventory['total_units'] ?? 0; ?>
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
        <div class="card dashboard-card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="dashboard-label">Total Donations</div>
                        <div class="dashboard-stat text-success">
                            <?php echo $donationStatistics['total_donations'] ?? 0; ?>
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
        <div class="card dashboard-card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="dashboard-label">Low Stock</div>
                        <div class="dashboard-stat text-warning">
                            <?php echo $inventory['low_count'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card border-left-danger h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="dashboard-label">Urgent Stock</div>
                        <div class="dashboard-stat text-danger">
                            <?php echo $inventory['urgent_count'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-fire fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Overview and Recent Donations -->
<div class="row">
    <div class="col-lg-8">
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Blood Inventory Overview</h6>
                <a href="inventory.php" class="btn btn-sm btn-primary">Manage Inventory</a>
            </div>
            <div class="card-body">
                <?php if (empty($inventoryTypes)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                        <h5>No inventory data</h5>
                        <p class="text-muted">Start by adding blood inventory!</p>
                        <a href="inventory.php" class="btn btn-primary">Add Inventory</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Blood Type</th>
                                    <th>Available Units</th>
                                    <th>Reserved Units</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventoryTypes as $item): ?>
                                <tr>
                                    <td>
                                        <span class="blood-type-badge blood-type-<?php echo strtolower(str_replace(['+', '-'], ['-plus', '-minus'], $item['blood_type'])); ?>">
                                            <?php echo $item['blood_type']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?php echo $item['units_available']; ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $item['units_reserved']; ?></span>
                                    </td>
                                    <td>
                                        <span class="inventory-status-<?php echo $item['status']; ?>">
                                            <?php echo ucfirst($item['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($item['last_updated'])); ?>
                                        </small>
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
                <h6 class="m-0 font-weight-bold text-primary">Recent Donations</h6>
            </div>
            <div class="card-body">
                <?php if (empty($donations)): ?>
                    <div class="text-center py-3">
                        <i class="fas fa-heart fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No recent donations</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($donations as $donation): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($donation['donor_name']); ?></strong><br>
                                <small class="text-muted">
                                    <span class="blood-type-badge blood-type-<?php echo strtolower(str_replace(['+', '-'], ['-plus', '-minus'], $donation['blood_type'])); ?>">
                                        <?php echo $donation['blood_type']; ?>
                                    </span>
                                    <?php echo $donation['units_donated']; ?> units
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="status-badge status-<?php echo $donation['status']; ?>">
                                    <?php echo ucfirst($donation['status']); ?>
                                </span>
                                <br><small class="text-muted">
                                    <?php echo date('M j', strtotime($donation['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="donors.php" class="btn btn-success w-100">
                        <i class="fas fa-users me-2"></i>Manage Donors
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Requests and Upcoming Appointments -->
<div class="row">
    <div class="col-lg-6">
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Pending Blood Requests</h6>
            </div>
            <div class="card-body">
                <?php if (empty($pendingRequests)): ?>
                    <div class="text-center py-3">
                        <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No pending requests</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pendingRequests as $request): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($request['hospital_name']); ?></strong><br>
                                <small class="text-muted">
                                    <span class="blood-type-badge blood-type-<?php echo strtolower(str_replace(['+', '-'], ['-plus', '-minus'], $request['blood_type'])); ?>">
                                        <?php echo $request['blood_type']; ?>
                                    </span>
                                    <?php echo $request['units_required']; ?> units
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?php echo $request['urgency'] === 'emergency' ? 'danger' : ($request['urgency'] === 'urgent' ? 'warning' : 'secondary'); ?>">
                                    <?php echo ucfirst($request['urgency']); ?>
                                </span>
                                <br><small class="text-muted">
                                    <?php echo htmlspecialchars($request['region']); ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Upcoming Appointments</h6>
            </div>
            <div class="card-body">
                <?php if (empty($appointments)): ?>
                    <div class="text-center py-3">
                        <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No upcoming appointments</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($appointments as $appointment): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($appointment['donor_name']); ?></strong><br>
                                <small class="text-muted">
                                    <?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?>
                                    at <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                </small>
                            </div>
                            <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                <?php echo ucfirst($appointment['status']); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="appointments.php" class="btn btn-info w-100">
                        <i class="fas fa-calendar-plus me-2"></i>Schedule Appointments
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card dashboard-card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Blood Type Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="bloodTypeChart" width="400" height="200"></canvas>
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
                        <a href="inventory.php" class="btn btn-primary w-100">
                            <i class="fas fa-boxes me-2"></i>Manage Inventory
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="donors.php" class="btn btn-success w-100">
                            <i class="fas fa-users me-2"></i>Manage Donors
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="appointments.php" class="btn btn-info w-100">
                            <i class="fas fa-calendar me-2"></i>Appointments
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="../admin/analytics.php" class="btn btn-warning w-100">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Blood Type Distribution Chart
    const ctx = document.getElementById('bloodTypeChart').getContext('2d');
    const inventoryData = <?php echo json_encode($inventoryTypes); ?>;
    
    const labels = inventoryData.map(item => item.blood_type);
    const data = inventoryData.map(item => item.units_available);
    const colors = ['#ff6b6b', '#ff8e8e', '#4ecdc4', '#6ee7df', '#45b7d1', '#6bc5d8', '#96ceb4', '#b8e0c8'];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Available Units',
                data: data,
                backgroundColor: colors.slice(0, data.length),
                borderColor: colors.slice(0, data.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?> 