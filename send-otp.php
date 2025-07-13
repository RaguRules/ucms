<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Courts Management System of Kilinochchi - Forgot Password</title>

    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">

    <link href="assets/css/auth.css" rel="stylesheet">
</head>

<body class="light-mode">


    <div class="container">
        <div class="row align-items-center justify-content-center vh-100">
            
            <div class="col-md-6 d-none d-md-block">
                <img src="assets/img/auth/signup-image.jpg" class="img-fluid login-image" alt="Login">
            </div>

            <div class="col-md-6">
                <div class="login-container">
                    <div class="d-flex justify-content-between">
                        <h3 class="mb-4">One Time Password</h3>

                        <button id="theme-toggle" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-moon"></i> <!-- Default Moon Icon -->
                        </button>
                    </div>

                    <form action="reset-pwd.php" method="POST" id="send-OtpForm">
                        <div class="mb-3">
                            <label>Enter the OTP you recieved via email/ mobile</label>
                            <input type="email" class="form-control" placeholder="Enter your OTP">
                        </div>

                        <button type="submit" class="btn btn-custom mt-3">Confirm OTP</button>
                    </form>

                    <div class="text-center mt-3 small-text">
                        Don't have an account? <a href="register.php" class="text-info">Register here</a>
                    </div>

                    <div class="footer">
                        © 2025 Unified Courts Management System - Kilinochchi. All rights reserved.
                    </div>
                </div>
            </div>

        </div>
    </div>

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


</body>
</html>
