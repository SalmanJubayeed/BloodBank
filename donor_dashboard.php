<?php
require_once 'config.php';

// Check if user is logged in and is a donor
if (!isLoggedIn() || !isDonor()) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Handle donation application
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_donation'])) {
    $request_id = (int)$_POST['request_id'];
    $donor_id = $_SESSION['user_id'];
    $message_text = trim($_POST['message']);
    
    // Check if request exists and is open
    $checkRequest = $pdo->prepare("SELECT * FROM blood_requests WHERE request_id = ? AND status = 'Open'");
    $checkRequest->execute([$request_id]);
    $request = $checkRequest->fetch();
    
    if (!$request) {
        $message = "Blood request not found or already fulfilled!";
        $messageType = "error";
    } else {
        // Check if donor already applied for this request
        $checkApplication = $pdo->prepare("SELECT application_id FROM donation_applications WHERE request_id = ? AND donor_id = ?");
        $checkApplication->execute([$request_id, $donor_id]);
        
        if ($checkApplication->fetch()) {
            $message = "You have already applied for this blood request!";
            $messageType = "error";
        } else {
            // Insert donation application
            $insertApplication = $pdo->prepare("INSERT INTO donation_applications (request_id, donor_id, message) VALUES (?, ?, ?)");
            if ($insertApplication->execute([$request_id, $donor_id, $message_text])) {
                $message = "Your donation application has been submitted successfully! The recipient will review it.";
                $messageType = "success";
            } else {
                $message = "Failed to submit application. Please try again.";
                $messageType = "error";
            }
        }
    }
}

// Fetch donor's applications with request details
$applicationsQuery = $pdo->prepare("
    SELECT da.*, br.blood_group, br.units_needed, br.urgency_level, br.hospital_name, 
           br.needed_by, u.name as recipient_name 
    FROM donation_applications da
    JOIN blood_requests br ON da.request_id = br.request_id
    JOIN users u ON br.recipient_id = u.user_id
    WHERE da.donor_id = ?
    ORDER BY da.applied_at DESC
");
$applicationsQuery->execute([$_SESSION['user_id']]);
$myApplications = $applicationsQuery->fetchAll();

// Fetch all open blood requests that donor hasn't applied to
$availableRequestsQuery = $pdo->prepare("
    SELECT br.*, u.name as recipient_name, u.phone as recipient_phone,
           (SELECT COUNT(*) FROM donation_applications da WHERE da.request_id = br.request_id AND da.status = 'Pending') as pending_applications
    FROM blood_requests br 
    JOIN users u ON br.recipient_id = u.user_id 
    WHERE br.status = 'Open' 
    AND br.request_id NOT IN (
        SELECT request_id FROM donation_applications WHERE donor_id = ?
    )
    ORDER BY 
        CASE br.urgency_level 
            WHEN 'Critical' THEN 1 
            WHEN 'High' THEN 2 
            WHEN 'Medium' THEN 3 
            WHEN 'Low' THEN 4 
        END,
        br.created_at DESC
");
$availableRequestsQuery->execute([$_SESSION['user_id']]);
$availableRequests = $availableRequestsQuery->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard - Blood Donation System</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        .close {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        .close:hover {
            color: #000;
        }
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .urgency-critical { background: #dc3545; color: white; }
        .urgency-high { background: #fd7e14; color: white; }
        .urgency-medium { background: #ffc107; color: black; }
        .urgency-low { background: #28a745; color: white; }
        .urgency-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
    </style>
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
                <h1><i class="fas fa-heart"></i> Donor Dashboard</h1>
                <p>Thank you for being a life-saver! Find people who need your help.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <i class="fas fa-<?= $messageType == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- My Applications Status -->
            <div class="dashboard-section">
                <h2><i class="fas fa-list-alt"></i> My Donation Applications</h2>
                <?php if (empty($myApplications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p>No applications yet. Apply to help people in need below!</p>
                    </div>
                <?php else: ?>
                    <div class="applications-grid">
                        <?php foreach ($myApplications as $app): ?>
                            <div class="application-card">
                                <div class="application-header">
                                    <div class="blood-type"><?= htmlspecialchars($app['blood_group']) ?></div>
                                    <div class="status-badge status-<?= strtolower($app['status']) ?>">
                                        <?= htmlspecialchars($app['status']) ?>
                                    </div>
                                </div>
                                <div class="application-body">
                                    <h3>For: <?= htmlspecialchars($app['recipient_name']) ?></h3>
                                    <p><i class="fas fa-tint"></i> <?= htmlspecialchars($app['units_needed']) ?> units needed</p>
                                    <p><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($app['urgency_level']) ?> priority</p>
                                    <?php if ($app['hospital_name']): ?>
                                        <p><i class="fas fa-hospital"></i> <?= htmlspecialchars($app['hospital_name']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($app['needed_by']): ?>
                                        <p><i class="fas fa-calendar-alt"></i> Needed by: <?= date('M d, Y', strtotime($app['needed_by'])) ?></p>
                                    <?php endif; ?>
                                    <p><i class="fas fa-clock"></i> Applied: <?= date('M d, Y H:i', strtotime($app['applied_at'])) ?></p>
                                    <?php if ($app['message']): ?>
                                        <p><i class="fas fa-comment"></i> Your message: "<?= htmlspecialchars($app['message']) ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Available Blood Requests Section -->
            <div class="dashboard-section">
                <h2><i class="fas fa-search"></i> People Who Need Your Help</h2>
                <?php if (empty($availableRequests)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>No available requests</h3>
                        <p>You've applied to all current requests, or there are no open requests at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="requests-grid">
                        <?php foreach ($availableRequests as $request): ?>
                            <div class="request-card">
                                <div class="request-header">
                                    <div class="blood-type"><?= htmlspecialchars($request['blood_group']) ?></div>
                                    <div class="urgency-badge urgency-<?= strtolower($request['urgency_level']) ?>">
                                        <?= htmlspecialchars($request['urgency_level']) ?>
                                    </div>
                                </div>
                                
                                <div class="request-body">
                                    <div class="patient-info">
                                        <h3><?= htmlspecialchars($request['recipient_name']) ?></h3>
                                        <p><i class="fas fa-tint"></i> <strong>Units needed:</strong> <?= htmlspecialchars($request['units_needed']) ?></p>
                                        
                                        <?php if ($request['hospital_name']): ?>
                                            <p><i class="fas fa-hospital"></i> <?= htmlspecialchars($request['hospital_name']) ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($request['contact_person']): ?>
                                            <p><i class="fas fa-user-md"></i> <?= htmlspecialchars($request['contact_person']) ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($request['contact_phone']): ?>
                                            <p><i class="fas fa-phone"></i> <?= htmlspecialchars($request['contact_phone']) ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($request['needed_by']): ?>
                                            <p><i class="fas fa-calendar-alt"></i> <strong>Needed by:</strong> <?= date('M d, Y', strtotime($request['needed_by'])) ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($request['additional_notes']): ?>
                                            <p><i class="fas fa-sticky-note"></i> <?= htmlspecialchars($request['additional_notes']) ?></p>
                                        <?php endif; ?>
                                        
                                        <p><i class="fas fa-users"></i> <?= $request['pending_applications'] ?> donors applied</p>
                                    </div>
                                    
                                    <div class="request-meta">
                                        <small><i class="fas fa-clock"></i> Posted <?= date('M d, Y H:i', strtotime($request['created_at'])) ?></small>
                                    </div>
                                </div>
                                
                                <div class="request-actions">
                                    <button class="btn btn-primary" onclick="showApplyModal(<?= $request['request_id'] ?>, '<?= htmlspecialchars($request['recipient_name']) ?>', '<?= htmlspecialchars($request['blood_group']) ?>')">
                                        <i class="fas fa-heart"></i> Apply to Help
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Application Modal -->
    <div id="applyModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeApplyModal()">&times;</span>
            <h2><i class="fas fa-heart"></i> Apply to Help</h2>
            <p>You are applying to help: <strong id="recipientName"></strong></p>
            <p>Blood Group: <strong id="bloodGroup"></strong></p>
            
            <form method="POST">
                <input type="hidden" name="request_id" id="requestId">
                <div class="form-group">
                    <label for="message">Message to Recipient (Optional)</label>
                    <textarea name="message" id="message" rows="4" placeholder="Tell the recipient why you want to help or any relevant information..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeApplyModal()">Cancel</button>
                    <button type="submit" name="apply_donation" class="btn btn-primary">
                        <i class="fas fa-heart"></i> Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="JS/script.js"></script>
    <script>
        function showApplyModal(requestId, recipientName, bloodGroup) {
            document.getElementById('requestId').value = requestId;
            document.getElementById('recipientName').textContent = recipientName;
            document.getElementById('bloodGroup').textContent = bloodGroup;
            document.getElementById('applyModal').style.display = 'block';
        }
        
        function closeApplyModal() {
            document.getElementById('applyModal').style.display = 'none';
            document.getElementById('message').value = '';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('applyModal');
            if (event.target === modal) {
                closeApplyModal();
            }
        }
    </script>
</body>
</html>