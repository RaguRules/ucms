<?php

if(isset($_SESSION["ForgotUser"])){
    $forgotUser = $_SESSION["ForgotUser"];
    $readOnly = "readOnly";

}else{
    $forgotUser = "";
    $readOnly = "";

}




?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Courts Management System of Kilinochchi - Forgot Password</title>

    <!-- Bootstrap & FontAwesome -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/auth.css" rel="stylesheet">
</head>

<body class="light-mode"> <!-- Default is Light Mode -->


    <div class="container">
        <div class="row align-items-center justify-content-center vh-100">
            
            <!-- Left Image Section -->
            <div class="col-md-6 d-none d-md-block">
                <img src="assets/img/auth/signup-image.jpg" class="img-fluid login-image" alt="Login">
            </div>

            <!-- Right Login Form -->
            <div class="col-md-6">
                <div class="login-container">
                    <div class="d-flex justify-content-between">
                        <h3 class="mb-4">Forgot your password?</h3>
                        <!-- Theme Toggle -->
                        <button id="theme-toggle" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-moon"></i> <!-- Default Moon Icon -->
                        </button>
                    </div>

                    <!-- Login Form -->
                    <form action="send-otp.php" method="POST" id="forgotPwdForm">
                        <div class="mb-3">
                            <label>Enter your Registered Email to reset password</label>
                            <input type="email" <?php echo "$readOnly"; ?> class="form-control" placeholder="Enter your email">
                        </div>

                        <div class="mb-3">
                            <label>Enter your Registered Email to reset password</label>
                            <input value="<?php echo $forgotUser; ?>" type="email" <?php echo "$readOnly"; ?> class="form-control" placeholder="Enter your email">
                        </div>

                        <button type="submit" class="btn btn-custom mt-3">Reset Password</button>
                    </form>

                    <!-- Register Link -->
                    <div class="text-center mt-3 small-text">
                        Don't have an account? <a href="register.php" class="text-info">Register here</a>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        © 2025 Unified Courts Management System - Kilinochchi. All rights reserved.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery3.7/jquery.min.js"></script>
    

    <!-- Scripts -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery3.7/jquery.min.js"></script>
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
                themeIcon.classList.replace("fa-moon", "fa-sun"); // Change to Sun Icon
            } else {
                body.classList.add("light-mode");
                themeIcon.classList.replace("fa-sun", "fa-moon"); // Change to Moon Icon
            }
        }

        // Toggle Theme Function
        document.getElementById("theme-toggle").addEventListener("click", () => {
            const body = document.body;
            const themeIcon = document.getElementById("theme-icon");

            if (body.classList.contains("dark-mode")) {
                body.classList.replace("dark-mode", "light-mode");
                themeIcon.classList.replace("fa-sun", "fa-moon"); // Change to Moon Icon
                setCookie("theme", "light", 30); // Save in cookie for 30 days
            } else {
                body.classList.replace("light-mode", "dark-mode");
                themeIcon.classList.replace("fa-moon", "fa-sun"); // Change to Sun Icon
                setCookie("theme", "dark", 30); // Save in cookie for 30 days
            }
        });

        // Apply theme on page load
        applySavedTheme();
    </script>
    
    
    
    <!-- <script>
        // Theme Toggle Script
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            body.classList.toggle('light-mode');

            if (body.classList.contains('dark-mode')) {
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>'; // Change to Sun Icon
            } else {
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>'; // Change to Moon Icon
            }
        });
    </script> -->

</body>
</html>
