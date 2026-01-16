<?php
require_once 'config.php';

// Fetch all active blood requests with recipient details
$requestsQuery = "SELECT br.*, u.name as recipient_name, u.phone as recipient_phone 
                  FROM blood_requests br 
                  JOIN users u ON br.recipient_id = u.user_id 
                  WHERE br.status = 'Open' 
                  ORDER BY 
                    CASE br.urgency_level 
                        WHEN 'Critical' THEN 1 
                        WHEN 'High' THEN 2 
                        WHEN 'Medium' THEN 3 
                        WHEN 'Low' THEN 4 
                    END,
                    br.created_at DESC";
$requests = $pdo->query($requestsQuery)->fetchAll();

// Fetch donors count for stats
$donorsCount = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'donor' AND is_active = 1")->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation System - Save Lives</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-heart text-red"></i>
                <span>BloodBank</span>
            </div>
            <nav class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <span class="user-greeting">
                        <i class="fas fa-user"></i>
                        Welcome, <?= htmlspecialchars($_SESSION['name']) ?>
                    </span>
                    <?php if (isDonor()): ?>
                        <a href="donor_dashboard.php" class="btn btn-outline">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    <?php elseif (isRecipient()): ?>
                        <a href="recipient_dashboard.php" class="btn btn-outline">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-primary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>
            </nav>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Save Lives Through Blood Donation</h1>
                <p>Connect directly with people who need blood. Every donation counts, every life matters.</p>
                <?php if (!isLoggedIn()): ?>
                    <div class="hero-buttons">
                        <a href="register.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-hand-holding-heart"></i> Become a Donor
                        </a>
                        <a href="register.php" class="btn btn-outline btn-lg">
                            <i class="fas fa-search"></i> Find Blood
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <h3><?= $donorsCount ?></h3>
                    <p>Active Donors</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3><?= count($requests) ?></h3>
                    <p>Urgent Requests</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-heart"></i>
                    <h3>24/7</h3>
                    <p>Emergency Support</p>
                </div>
                <div class="stat-item">
                    <i class="fas fa-handshake"></i>
                    <p>Direct Connection</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Blood Requests Section -->
    <section class="blood-requests">
        <div class="container">
            <h2><i class="fas fa-exclamation-triangle"></i> Urgent Blood Requests</h2>
            <p class="section-subtitle"><b>People in need of blood donation. Help save lives!</b></p>
            
            <?php if (empty($requests)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>No urgent requests at the moment</h3>
                    <p>All current blood requests have been fulfilled. Thank you to our donors!</p>
                </div>
            <?php else: ?>
                <div class="requests-grid">
                    <?php foreach ($requests as $request): ?>
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
                                </div>
                                
                                <div class="request-meta">
                                    <small><i class="fas fa-clock"></i> Posted <?= date('M d, Y H:i', strtotime($request['created_at'])) ?></small>
                                </div>
                            </div>
                            
                            <div class="request-actions">
                                <?php if (isDonor()): ?>
                                    <a href="donor_dashboard.php" class="btn btn-primary">
                                        <i class="fas fa-heart"></i> I Can Help
                                    </a>
                                <?php elseif (!isLoggedIn()): ?>
                                    <a href="login.php" class="btn btn-outline">
                                        <i class="fas fa-sign-in-alt"></i> Login to Help
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="container">
            <h2>How It Works</h2>
            <div class="steps-grid">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Recipients Post Requests</h3>
                        <p>Those in need of blood post detailed requests with urgency level and hospital information.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Donors See Requests</h3>
                        <p>Registered donors can view all blood requests and choose whom to help.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Direct Application</h3>
                        <p>Donors apply directly to help specific recipients - no admin approval needed.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Recipients Approve</h3>
                        <p>Recipients review and approve donor applications from their dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-heart"></i> BloodBank</h3>
                    <p>Connecting donors with recipients to save lives every day. Simple, direct, effective.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="register.php">Become a Donor</a></li>
                        <li><a href="register.php">Find Blood</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Emergency</h4>
                    <p><i class="fas fa-phone"></i> +1-800-BLOOD</p>
                    <p><i class="fas fa-envelope"></i> emergency@bloodbank.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 BloodBank. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="JS/script.js"></script>
</body>
</html>