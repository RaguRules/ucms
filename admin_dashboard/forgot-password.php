<?php
// Forgot password page for the Unified Courts Management System Admin Dashboard
session_start();
include_once('config/database.php');

// Check if user is already logged in
if (isset($_SESSION['USER_ID'])) {
    header("Location: index.php");
    exit;
}

// Process forgot password form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($email)) {
        $message = 'Please enter your email address.';
        $messageType = 'danger';
    } else {
        // Check if email exists in the system
        $sql = "SELECT * FROM login WHERE username = '$email' AND status = 'Active'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Store token in database
            $sql = "UPDATE login SET reset_token = '$token', reset_expiry = '$expiry' WHERE username = '$email'";
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                // In a production environment, send email with reset link
                // For this demo, just display the reset link
                $resetLink = "http://{$_SERVER['HTTP_HOST']}/admin_dashboard/reset-password.php?token=$token";
                
                $message = "A password reset link has been sent to your email address. The link will expire in 24 hours.<br><br>
                            <strong>Demo Only:</strong> <a href=\"$resetLink\">$resetLink</a>";
                $messageType = 'success';
            } else {
                $message = 'Error generating reset token. Please try again.';
                $messageType = 'danger';
            }
        } else {
            // Don't reveal if email exists or not for security
            $message = 'If your email address is registered in our system, you will receive a password reset link.';
            $messageType = 'info';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Unified Courts Management System</title>
    
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
                        <h3>Forgot Password</h3>
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

                    <p class="mb-4">Enter your email address below and we'll send you a link to reset your password.</p>

                    <!-- Forgot Password Form -->
                    <form action="forgot-password.php" method="POST" id="forgotPasswordForm">
                        <div class="mb-3">
                            <label for="email">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3 w-100">Reset Password</button>
                        
                        <div class="text-center mt-3">
                            <a href="login.php" class="small-text text-info">Back to Login</a>
                        </div>
                    </form>

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

        // Apply theme on page load
        applySavedTheme();
    </script>
</body>
</html>
