<?php
// Reset password page for the Unified Courts Management System Admin Dashboard
session_start();
include_once('config/database.php');

// Check if user is already logged in
if (isset($_SESSION['USER_ID'])) {
    header("Location: index.php");
    exit;
}

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: login.php");
    exit;
}

$token = $_GET['token'];
$message = '';
$messageType = '';
$validToken = false;

// Verify token
$sql = "SELECT * FROM login WHERE reset_token = '$token' AND reset_expiry > NOW() AND status = 'Active'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $validToken = true;
    $user = mysqli_fetch_assoc($result);
    
    // Process password reset form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        if (empty($password) || empty($confirmPassword)) {
            $message = 'Please enter both password fields.';
            $messageType = 'danger';
        } elseif ($password !== $confirmPassword) {
            $message = 'Passwords do not match.';
            $messageType = 'danger';
        } elseif (strlen($password) < 8) {
            $message = 'Password must be at least 8 characters long.';
            $messageType = 'danger';
        } else {
            // In production, hash the password
            // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $hashedPassword = $password; // For demo purposes
            
            // Update password and clear reset token
            $sql = "UPDATE login SET password = '$hashedPassword', reset_token = NULL, reset_expiry = NULL WHERE reset_token = '$token'";
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                $message = 'Your password has been reset successfully. You can now <a href="login.php">login</a> with your new password.';
                $messageType = 'success';
                $validToken = false; // Hide the form
            } else {
                $message = 'Error resetting password. Please try again.';
                $messageType = 'danger';
            }
        }
    }
} else {
    $message = 'Invalid or expired reset token. Please request a new password reset link.';
    $messageType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Unified Courts Management System</title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/img/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/login.css" rel="stylesheet">
</head>

<body class="light-mode">
    <div class="container">
        <div class="row align-items-center justify-content-center vh-100">
            <!-- Left Image Section -->
            <div class="col-md-6 d-none d-md-block">
                <img src="assets/img/login-image.jpg" class="img-fluid login-image" alt="Login">
            </div>

            <!-- Right Form -->
            <div class="col-md-6">
                <div class="login-container">
                    <div class="d-flex justify-content-between">
                        <h3>Reset Password</h3>
                        <!-- Theme Toggle -->
                        <button id="theme-toggle" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </button>
                    </div>

                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($validToken): ?>
                    <p class="mb-4">Please enter your new password below.</p>

                    <!-- Reset Password Form -->
                    <form action="reset-password.php?token=<?php echo $token; ?>" method="POST" id="resetPasswordForm">
                        <div class="mb-3">
                            <label for="password">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required>
                                <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                                <button class="btn btn-outline-secondary password-toggle" type="button" data-target="confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3 w-100">Reset Password</button>
                    </form>
                    <?php else: ?>
                    <div class="text-center mt-3">
                        <a href="login.php" class="btn btn-outline-primary">Back to Login</a>
                    </div>
                    <?php endif; ?>

                    <!-- Footer -->
                    <div class="footer text-center mt-4">
                        <p>© 2025 Unified Courts Management System - Kilinochchi. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Function to set a cookie
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                let date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + value + "; path=/" + expires;
        }

        // Function to get a cookie
        function getCookie(name) {
            let nameEQ = name + "=";
            let cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                let c = cookies[i].trim();
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        // Apply theme on page load based on cookie
        function applySavedTheme() {
            const theme = getCookie("theme");
            const body = document.body;
            const themeIcon = document.getElementById("theme-icon");

            if (theme === "dark") {
                body.classList.add("dark-mode");
                body.classList.remove("light-mode");
                themeIcon.classList.replace("fa-moon", "fa-sun");
            } else {
                body.classList.add("light-mode");
                body.classList.remove("dark-mode");
                themeIcon.classList.replace("fa-sun", "fa-moon");
            }
        }

        // Toggle Theme Function
        document.getElementById("theme-toggle").addEventListener("click", () => {
            const body = document.body;
            const themeIcon = document.getElementById("theme-icon");

            if (body.classList.contains("dark-mode")) {
                body.classList.replace("dark-mode", "light-mode");
                themeIcon.classList.replace("fa-sun", "fa-moon");
                setCookie("theme", "light", 30);
            } else {
                body.classList.replace("light-mode", "dark-mode");
                themeIcon.classList.replace("fa-moon", "fa-sun");
                setCookie("theme", "dark", 30);
            }
        });

        // Password Toggle
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

        // Apply theme on page load
        applySavedTheme();
    </script>
</body>
</html>
