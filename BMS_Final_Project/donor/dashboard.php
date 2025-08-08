<?php
require_once '../includes/auth.php';
$auth->requireRole('donor');

$user = $auth->getCurrentUser();

// Get donor information
try {
    $donorQuery = $db->prepare("
        SELECT * FROM donors WHERE user_id = ?
    ");
    $donorQuery->execute([$user['id']]);
    $donor = $donorQuery->fetch();
    
    // Get donation history
    $donationHistory = $db->prepare("
        SELECT d.*, bb.bank_name 
        FROM donations d
        JOIN blood_banks bb ON d.blood_bank_id = bb.id
        WHERE d.donor_id = ?
        ORDER BY d.donation_date DESC
        LIMIT 5
    ");
    $donationHistory->execute([$donor['id']]);
    $donations = $donationHistory->fetchAll();
    
    // Get upcoming appointments
    $appointmentsQuery = $db->prepare("
        SELECT a.*, bb.bank_name 
        FROM appointments a
        JOIN blood_banks bb ON a.blood_bank_id = bb.id
        WHERE a.donor_id = ? AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC
        LIMIT 5
    ");
    $appointmentsQuery->execute([$donor['id']]);
    $appointments = $appointmentsQuery->fetchAll();
    
    // Get total donations and units
    $statsQuery = $db->prepare("
        SELECT COUNT(*) as total_donations, SUM(units_donated) as total_units
        FROM donations 
        WHERE donor_id = ? AND status = 'completed'
    ");
    $statsQuery->execute([$donor['id']]);
    $stats = $statsQuery->fetch();
    
    // Check eligibility
    $lastDonation = $db->prepare("
        SELECT donation_date FROM donations 
        WHERE donor_id = ? AND status = 'completed'
        ORDER BY donation_date DESC 
        LIMIT 1
    ");
    $lastDonation->execute([$donor['id']]);
    $lastDonationDate = $lastDonation->fetchColumn();
    
    $isEligible = true;
    $nextEligibleDate = null;
    
    if ($lastDonationDate) {
        $nextEligibleDate = date('Y-m-d', strtotime($lastDonationDate . ' + 56 days')); // 8 weeks
        $isEligible = date('Y-m-d') >= $nextEligibleDate;
    }
    
} catch (PDOException $e) {
    error_log("Donor dashboard error: " . $e->getMessage());
    $donor = $donations = $appointments = $stats = [];
    $isEligible = false;
    $nextEligibleDate = null;
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-heart me-2"></i>Donor Dashboard
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
                        <h4>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h4>
                        <p class="mb-0">
                            Blood Type: 
                            <span class="blood-type-badge blood-type-<?php echo strtolower(str_replace(['+', '-'], ['-plus', '-minus'], $donor['blood_type'] ?? 'O+')); ?>">
                                <?php echo $donor['blood_type'] ?? 'O+'; ?>
                            </span>
                            <?php if ($donor['eligibility_status']): ?>
                                | Status: 
                                <span class="badge bg-<?php echo $donor['eligibility_status'] === 'eligible' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($donor['eligibility_status']); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <?php if ($isEligible): ?>
                            <span class="badge bg-success fs-6">Ready to Donate</span>
                        <?php else: ?>
                            <span class="badge bg-warning fs-6">Next Eligible: <?php echo date('M j, Y', strtotime($nextEligibleDate)); ?></span>
                        <?php endif; ?>
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
                        <div class="dashboard-label">Total Donations</div>
                        <div class="dashboard-stat text-primary">
                            <?php echo $stats['total_donations'] ?? 0; ?>
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
        <div class="card dashboard-card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="dashboard-label">Units Donated</div>
                        <div class="dashboard-stat text-success">
                            <?php echo $stats['total_units'] ?? 0; ?>
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
                        <div class="dashboard-label">Upcoming Appointments</div>
                        <div class="dashboard-stat text-info">
                            <?php echo count($appointments); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                        <div class="dashboard-label">Lives Saved</div>
                        <div class="dashboard-stat text-warning">
                            <?php echo ($stats['total_units'] ?? 0) * 3; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Donation History and Appointments -->
<div class="row">
    <div class="col-lg-8">
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Donation History</h6>
                <a href="history.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($donations)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                        <h5>No donations yet</h5>
                        <p class="text-muted">Start your journey by making your first blood donation!</p>
                        <a href="appointments.php" class="btn btn-primary">Schedule Appointment</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Blood Bank</th>
                                    <th>Units</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($donation['donation_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($donation['bank_name']); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $donation['units_donated']; ?> units</span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $donation['status']; ?>">
                                            <?php echo ucfirst($donation['status']); ?>
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
                                <strong><?php echo htmlspecialchars($appointment['bank_name']); ?></strong><br>
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
                    <a href="appointments.php" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Schedule New Appointment
                    </a>
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
                        <a href="appointments.php" class="btn btn-primary w-100">
                            <i class="fas fa-calendar-plus me-2"></i>Schedule Appointment
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="history.php" class="btn btn-info w-100">
                            <i class="fas fa-history me-2"></i>Donation History
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="../profile.php" class="btn btn-warning w-100">
                            <i class="fas fa-user-edit me-2"></i>Update Profile
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="../bloodbank/inventory.php" class="btn btn-success w-100">
                            <i class="fas fa-search me-2"></i>Check Blood Availability
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Eligibility Information -->
<?php if (!$isEligible && $nextEligibleDate): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle me-2"></i>Donation Eligibility</h6>
            <p class="mb-0">
                You can donate blood again starting from <strong><?php echo date('F j, Y', strtotime($nextEligibleDate)); ?></strong>. 
                This is to ensure your body has enough time to replenish the donated blood.
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?> 