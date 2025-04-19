<?php
// Login page for the Unified Courts Management System Admin Dashboard
session_start();
include_once('config/database.php');
include_once('includes/auth.php');

// Check if user is already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Process login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $result = authenticateUser($conn, $username, $password);
        
        if (isset($result['success']) && $result['success']) {
            // Set session variables
            $_SESSION['USERNAME'] = $result['username'];
            $_SESSION['USER_ID'] = $result['user_id'];
            $_SESSION['ROLE_ID'] = $result['role_id'];
            $_SESSION['ROLE_NAME'] = $result['role_name'];
            $_SESSION['FIRST_NAME'] = $result['first_name'];
            $_SESSION['LAST_NAME'] = $result['last_name'];
            
            // Redirect to dashboard
            header("Location: index.php");
            exit;
        } else {
            $error = $result['error'] ?? 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Unified Courts Management System</title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/img/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    
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

            <!-- Right Login Form -->
            <div class="col-md-6">
                <div class="login-container">
                    <div class="d-flex justify-content-between">
                        <h3>Admin Dashboard Login</h3>
                        <!-- Theme Toggle -->
                        <button id="theme-toggle" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </button>
                    </div>

                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form action="login.php" method="POST" id="loginForm">
                        <div class="mb-3">
                            <label for="username">Email address</label>
                            <input type="email" class="form-control" id="username" name="username" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                                <label class="form-check-label small-text" for="rememberMe">Remember me</label>
                            </div>
                            <a href="forgot-password.php" class="small-text text-info">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3 w-100">Login</button>
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
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    
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
