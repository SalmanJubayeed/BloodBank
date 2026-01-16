<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $age = (int)$_POST['age'];
    $gender = $_POST['gender'];
    $blood_group = $_POST['blood_group'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "Name, email, password, and role are required!";
    } elseif (!in_array($role, ['donor', 'recipient'])) {
        $error = "Invalid role selected!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif (!in_array($blood_group, $blood_groups)) {
        $error = "Invalid blood group selected!";
    } else {
        // Check if email already exists
        $checkEmail = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $checkEmail->execute([$email]);
        
        if ($checkEmail->fetch()) {
            $error = "Email already registered!";
        } else {
            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertUser = $pdo->prepare("INSERT INTO users (name, email, password, role, age, gender, blood_group, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($insertUser->execute([$name, $email, $hashedPassword, $role, $age, $gender, $blood_group, $phone, $address])) {
                $success = "Registration successful! You can now login and start " . ($role === 'donor' ? 'saving lives' : 'finding blood donors') . ".";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Blood Donation System</title>
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
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </nav>
        </div>
    </header>

    <!-- Registration Form -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-form">
                <div class="form-header">
                    <h2><i class="fas fa-user-plus"></i> Join Our Community</h2>
                    <p>Register as a donor to save lives or as a recipient to find blood donors</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                        <br><a href="login.php" class="btn btn-primary btn-sm" style="margin-top: 10px;">Login now</a>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i> Full Name *
                        </label>
                        <input type="text" id="name" name="name" required
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                               placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email Address *
                        </label>
                        <input type="email" id="email" name="email" required
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                               placeholder="Enter your email address">
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password *
                        </label>
                        <input type="password" id="password" name="password" required minlength="6"
                               placeholder="Enter password (minimum 6 characters)">
                        <small>Minimum 6 characters</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="role">
                                <i class="fas fa-user-tag"></i> I am a *
                            </label>
                            <select id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="donor" <?= (isset($_POST['role']) && $_POST['role'] == 'donor') ? 'selected' : '' ?>>
                                    Blood Donor
                                </option>
                                <option value="recipient" <?= (isset($_POST['role']) && $_POST['role'] == 'recipient') ? 'selected' : '' ?>>
                                    Blood Recipient
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="blood_group">
                                <i class="fas fa-tint"></i> Blood Group *
                            </label>
                            <select id="blood_group" name="blood_group" required>
                                <option value="">Select Blood Group</option>
                                <?php foreach ($blood_groups as $group): ?>
                                    <option value="<?= $group ?>" <?= (isset($_POST['blood_group']) && $_POST['blood_group'] == $group) ? 'selected' : '' ?>>
                                        <?= $group ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="age">
                                <i class="fas fa-birthday-cake"></i> Age
                            </label>
                            <input type="number" id="age" name="age" min="18" max="65"
                                   value="<?= isset($_POST['age']) ? htmlspecialchars($_POST['age']) : '' ?>"
                                   placeholder="Enter your age">
                        </div>

                        <div class="form-group">
                            <label for="gender">
                                <i class="fas fa-venus-mars"></i> Gender
                            </label>
                            <select id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : '' ?>>
                                    Male
                                </option>
                                <option value="Female" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : '' ?>>
                                    Female
                                </option>
                                <option value="Other" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'selected' : '' ?>>
                                    Other
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone"></i> Phone Number
                        </label>
                        <input type="tel" id="phone" name="phone"
                               value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>"
                               placeholder="Enter your phone number">
                    </div>

                    <div class="form-group">
                        <label for="address">
                            <i class="fas fa-map-marker-alt"></i> Address
                        </label>
                        <textarea id="address" name="address" rows="3"
                                  placeholder="Enter your address"><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-full">
                        <i class="fas fa-user-plus"></i> Register Now
                    </button>
                </form>

                <div class="form-footer">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </section>

    <script src="JS/script.js"></script>
</body>
</html>