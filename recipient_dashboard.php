<?php
require_once 'config.php';

// Check if user is logged in and is a recipient
if (!isLoggedIn() || !isRecipient()) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Handle blood request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_blood'])) {
    $recipient_id = $_SESSION['user_id'];
    $blood_group = trim($_POST['blood_group']);
    $units_needed = (int)$_POST['units_needed'];
    $urgency_level = $_POST['urgency_level'];
    $hospital_name = trim($_POST['hospital_name']);
    $contact_person = trim($_POST['contact_person']);
    $contact_phone = trim($_POST['contact_phone']);
    $needed_by = !empty($_POST['needed_by']) ? $_POST['needed_by'] : null;
    $additional_notes = trim($_POST['additional_notes']);
    
    // Validation
    if (empty($blood_group) || $units_needed <= 0) {
        $message = "Blood group and units needed are required!";
        $messageType = "error";
    } elseif (!in_array($blood_group, $blood_groups)) {
        $message = "Invalid blood group selected!";
        $messageType = "error";
    } elseif (!in_array($urgency_level, $urgency_levels)) {
        $message = "Invalid urgency level selected!";
        $messageType = "error";
    } else {
        // Check if recipient already has an open request
        $checkPending = $pdo->prepare("SELECT request_id FROM blood_requests WHERE recipient_id = ? AND status = 'Open'");
        $checkPending->execute([$recipient_id]);
        
        if ($checkPending->fetch()) {
            $message = "You already have an open blood request! Please wait for it to be fulfilled or cancel it first.";
            $messageType = "error";
        } else {
            // Insert blood request
            $insertRequest = $pdo->prepare("INSERT INTO blood_requests (recipient_id, blood_group, units_needed, urgency_level, hospital_name, contact_person, contact_phone, needed_by, additional_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($insertRequest->execute([$recipient_id, $blood_group, $units_needed, $urgency_level, $hospital_name, $contact_person, $contact_phone, $needed_by, $additional_notes])) {
                $message = "Blood request posted successfully! Donors will be able to see and apply to help you.";
                $messageType = "success";
            } else {
                $message = "Failed to submit blood request. Please try again.";
                $messageType = "error";
            }
        }
    }
}

// Handle application approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['handle_application'])) {
    $application_id = (int)$_POST['application_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    
    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'Approved' : 'Rejected';
        $updateApplication = $pdo->prepare("UPDATE donation_applications SET status = ?, responded_at = NOW() WHERE application_id = ?");
        
        if ($updateApplication->execute([$status, $application_id])) {
            // If approved, optionally mark the blood request as fulfilled
            if ($action === 'approve') {
                $message = "Donor application approved! You can now contact the donor to coordinate the donation.";
                $messageType = "success";
            } else {
                $message = "Donor application rejected.";
                $messageType = "info";
            }
        } else {
            $message = "Failed to process application. Please try again.";
            $messageType = "error";
        }
    }
}

// Fetch recipient's requests with application counts
$requestsQuery = $pdo->prepare("
    SELECT br.*, 
           (SELECT COUNT(*) FROM donation_applications da WHERE da.request_id = br.request_id AND da.status = 'Pending') as pending_count,
           (SELECT COUNT(*) FROM donation_applications da WHERE da.request_id = br.request_id AND da.status = 'Approved') as approved_count
    FROM blood_requests br 
    WHERE br.recipient_id = ? 
    ORDER BY br.created_at DESC
");
$requestsQuery->execute([$_SESSION['user_id']]);
$myRequests = $requestsQuery->fetchAll();

// Fetch applications for recipient's requests
$applicationsQuery = $pdo->prepare("
    SELECT da.*, u.name as donor_name, u.email as donor_email, u.phone as donor_phone, 
           u.blood_group as donor_blood_group, br.blood_group as requested_blood_group, br.units_needed, br.urgency_level
    FROM donation_applications da
    JOIN users u ON da.donor_id = u.user_id
    JOIN blood_requests br ON da.request_id = br.request_id
    WHERE br.recipient_id = ?
    ORDER BY da.applied_at DESC
");
$applicationsQuery->execute([$_SESSION['user_id']]);
$applications = $applicationsQuery->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Dashboard - Blood Donation System</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-heart text-red"></i>
                <a href="index.php">BloodBank</a>
            </div>
            <nav class="nav-links">
                <span class="user-greeting">
                    <i class="fas fa-user"></i>
                    Welcome, <?= htmlspecialchars($_SESSION['name']) ?>
                </span>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="logout.php" class="btn btn-primary">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Dashboard Content -->
    <section class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1><i class="fas fa-search"></i> Recipient Dashboard</h1>
                <p>Post your blood needs and connect directly with donors who can help.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <i class="fas fa-<?= $messageType == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Request Blood Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-plus-circle"></i> Post Blood Request</h2>
                    <button class="btn btn-primary" onclick="showRequestModal()">
                        <i class="fas fa-plus"></i> New Request
                    </button>
                </div>
                
                <!-- My Requests -->
                <h3><i class="fas fa-list-alt"></i> My Blood Requests</h3>
                <?php if (empty($myRequests)): ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p>No blood requests yet. Post your first request above!</p>
                    </div>
                <?php else: ?>
                    <div class="requests-grid">
                        <?php foreach ($myRequests as $request): ?>
                            <div class="request-card my-request">
                                <div class="request-header">
                                    <div class="blood-type"><?= htmlspecialchars($request['blood_group']) ?></div>
                                    <div class="status-badge status-<?= strtolower($request['status']) ?>">
                                        <?= htmlspecialchars($request['status']) ?>
                                    </div>
                                </div>
                                
                                <div class="request-body">
                                    <p><i class="fas fa-tint"></i> <strong>Units needed:</strong> <?= htmlspecialchars($request['units_needed']) ?></p>
                                    <p><i class="fas fa-exclamation-triangle"></i> <strong>Priority:</strong> <?= htmlspecialchars($request['urgency_level']) ?></p>
                                    
                                    <?php if ($request['hospital_name']): ?>
                                        <p><i class="fas fa-hospital"></i> <?= htmlspecialchars($request['hospital_name']) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($request['needed_by']): ?>
                                        <p><i class="fas fa-calendar-alt"></i> <strong>Needed by:</strong> <?= date('M d, Y', strtotime($request['needed_by'])) ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="application-stats">
                                        <span class="stat-item">
                                            <i class="fas fa-clock"></i>
                                            <?= $request['pending_count'] ?> Pending
                                        </span>
                                        <span class="stat-item">
                                            <i class="fas fa-check"></i>
                                            <?= $request['approved_count'] ?> Approved
                                        </span>
                                    </div>
                                    
                                    <div class="request-meta">
                                        <small><i class="fas fa-calendar"></i> Posted <?= date('M d, Y H:i', strtotime($request['created_at'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Donor Applications Section -->
            <div class="dashboard-section">
                <h2><i class="fas fa-users"></i> Donor Applications</h2>
                <?php if (empty($applications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p>No donor applications yet. Once you post a blood request, donors will be able to apply to help you.</p>
                    </div>
                <?php else: ?>
                    <div class="applications-grid">
                        <?php foreach ($applications as $app): ?>
                            <div class="application-card">
                                <div class="application-header">
                                    <div class="donor-info">
                                        <h3><?= htmlspecialchars($app['donor_name']) ?></h3>
                                        <span class="blood-type"><?= htmlspecialchars($app['donor_blood_group']) ?></span>
                                    </div>
                                    <div class="status-badge status-<?= strtolower($app['status']) ?>">
                                        <?= htmlspecialchars($app['status']) ?>
                                    </div>
                                </div>
                                
                                <div class="application-body">
                                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($app['donor_email']) ?></p>
                                    <?php if ($app['donor_phone']): ?>
                                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($app['donor_phone']) ?></p>
                                    <?php endif; ?>
                                    <p><i class="fas fa-tint"></i> For: <?= htmlspecialchars($app['requested_blood_group']) ?> (<?= $app['units_needed'] ?> units)</p>
                                    <p><i class="fas fa-clock"></i> Applied: <?= date('M d, Y H:i', strtotime($app['applied_at'])) ?></p>
                                    
                                    <?php if ($app['message']): ?>
                                        <div class="donor-message">
                                            <strong>Message:</strong>
                                            <p>"<?= htmlspecialchars($app['message']) ?>"</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($app['status'] === 'Pending'): ?>
                                    <div class="application-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" name="handle_application" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" name="handle_application" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                <?php elseif ($app['status'] === 'Approved'): ?>
                                    <div class="approved-contact">
                                        <p><strong>✅ Approved!</strong> Contact this donor:</p>
                                        <a href="mailto:<?= htmlspecialchars($app['donor_email']) ?>" class="btn btn-outline btn-sm">
                                            <i class="fas fa-envelope"></i> Send Email
                                        </a>
                                        <?php if ($app['donor_phone']): ?>
                                            <a href="tel:<?= htmlspecialchars($app['donor_phone']) ?>" class="btn btn-outline btn-sm">
                                                <i class="fas fa-phone"></i> Call
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Request Blood Modal -->
    <div id="requestModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRequestModal()">&times;</span>
            <h2><i class="fas fa-plus-circle"></i> Post Blood Request</h2>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="blood_group">
                            <i class="fas fa-tint"></i> Blood Group *
                        </label>
                        <select name="blood_group" id="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <?php foreach ($blood_groups as $group): ?>
                                <option value="<?= $group ?>"><?= $group ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="units_needed">
                            <i class="fas fa-plus"></i> Units Needed *
                        </label>
                        <input type="number" name="units_needed" id="units_needed" min="1" max="10" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="urgency_level">
                            <i class="fas fa-exclamation-triangle"></i> Urgency Level *
                        </label>
                        <select name="urgency_level" id="urgency_level" required>
                            <option value="">Select Urgency</option>
                            <?php foreach ($urgency_levels as $level): ?>
                                <option value="<?= $level ?>"><?= $level ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="needed_by">
                            <i class="fas fa-calendar-alt"></i> Needed By
                        </label>
                        <input type="date" name="needed_by" id="needed_by" min="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="hospital_name">
                        <i class="fas fa-hospital"></i> Hospital/Medical Center
                    </label>
                    <input type="text" name="hospital_name" id="hospital_name" placeholder="Enter hospital or medical center name">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_person">
                            <i class="fas fa-user-md"></i> Contact Person
                        </label>
                        <input type="text" name="contact_person" id="contact_person" placeholder="Doctor or contact person name">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_phone">
                            <i class="fas fa-phone"></i> Contact Phone
                        </label>
                        <input type="tel" name="contact_phone" id="contact_phone" placeholder="Emergency contact number">
                    </div>
                </div>

                <div class="form-group">
                    <label for="additional_notes">
                        <i class="fas fa-sticky-note"></i> Additional Notes
                    </label>
                    <textarea name="additional_notes" id="additional_notes" rows="3" placeholder="Any additional information that might help donors..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeRequestModal()">Cancel</button>
                    <button type="submit" name="request_blood" class="btn btn-danger">
                        <i class="fas fa-plus-circle"></i> Post Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="JS/script.js"></script>
    <script>
        function showRequestModal() {
            document.getElementById('requestModal').style.display = 'block';
        }
        
        function closeRequestModal() {
            document.getElementById('requestModal').style.display = 'none';
            // Reset form
            document.querySelector('#requestModal form').reset();
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('requestModal');
            if (event.target === modal) {
                closeRequestModal();
            }
        }
    </script>
</body>
</html>