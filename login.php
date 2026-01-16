<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Email and password are required!";
    } else {
        $stmt = $pdo->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role (no admin role anymore)
            switch ($user['role']) {
                case 'donor':
                    header('Location: donor_dashboard.php');
                    break;
                case 'recipient':
                    header('Location: recipient_dashboard.php');
                    break;
                default:
                    header('Location: index.php');
            }
            exit;
        } else {
            $error = "Invalid email or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blood Donation System</title>
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
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            </nav>
        </div>
    </header>

    <!-- Login Form -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-form">
                <div class="form-header">
                    <h2><i class="fas fa-sign-in-alt"></i> Welcome Back</h2>
                    <p>Sign in to your account to save lives or find blood donors</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" id="email" name="email" required
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                               placeholder="Enter your email">
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" id="password" name="password" required
                               placeholder="Enter your password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-full">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="form-footer">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </section>

    <script src="JS/script.js"></script>
</body>
</html>