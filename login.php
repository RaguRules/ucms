<?php
if (!isset($_SESSION)){
    session_start();
}

include_once('db.php');


if (isset($_POST["btn_login"])) {

    $txt_username = mysqli_real_escape_string($conn, $_POST["txt_username"]);
    $txt_password = mysqli_real_escape_string($conn, $_POST["txt_password"]);
		
    // $hashedPassword = password_hash($txt_password, PASSWORD_DEFAULT);
    $hashedPassword = 'abc';

    $sqlUser = "SELECT * FROM login WHERE username = '$txt_username'";
    $resultUser = mysqli_query($conn, $sqlUser);
    if (mysqli_num_rows($resultUser) > 0){
        $rowUser = mysqli_fetch_assoc($resultUser);
        
        $extractedUsername = $rowUser['username'];
        $extractedPassword = $rowUser['password'];

        if($hashedPassword == $extractedPassword){

            echo "$hashedPassword";
            echo "$extractedPassword";

            $attempt = 0 ;
            $sqlAttemptReset="UPDATE login SET attempt = '$attempt' WHERE username='$extractedUsername'";
            $sqlAttemptReset=mysqli_query($conn,$sqlAttemptReset)or die("Error in sql_update".mysqli_error($con));
                
            $status = $rowUser['status'];
            // if($status == "Active"){
            //     $_SESSION["USERNAME"] = $rowUser['username'];
            //     $_SESSION["USERTYPE"] = $rowUser['usertype'];
            //     echo "<script> location.href='index.php?pg=staff.php&option=view'; </script>";
			// exit;

            // }elseif{


            switch ($status) {
                case "Active":
                    $_SESSION["USERNAME"] = $rowUser['username'];
                    $_SESSION["USERTYPE"] = $rowUser['usertype'];
                    echo "<script> alert('Good to go')</script>";
                    echo "<script> location.href='index.php?pg=staff.php&option=view'; </script>";
                    break;

                case "Pending":
                    echo '<script> alert("Your membership is pending for approval. Check later"); </script>';
                    break;

                case "Blocked":
                    echo '<script> alert("Your account is Blocked. Contact Administrator"); </script>';
                    break;

                case "Deleted":
                    echo '<script> alert("Your account is deleted"); </script>';
                    break;

                default:
                    echo '<script> alert("Something went wrong. Conatact Admin Officer"); </script>';
                }








            // }else{

            // }












        }elseif($rowUser['attempt'] < 3){
                $attempt = $rowUser['attempt'];
                $attempt = $attempt + 1 ;
                $sqlUpdate="UPDATE login SET attempt = '$attempt' WHERE username='$extractedUsername'";
	
                $attemptUpdate=mysqli_query($conn,$sqlUpdate)or die("Error in sql_update".mysqli_error($con));
                echo "<script> alert('Incorrect Credentials 3, Please check your Username/ Password')</script>";

        }else{
            $_SESSION["ForgotUser"] = $extractedUsername;
            echo "<script> alert('You have exceeded maximum attemps, Kindly reset your password')</script>";
        }   


    }else{
        echo "<script> alert('Incorrect Credentials 2, Please check your Username/ Password')</script>";
    }

}







?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kilinochchi Courts Management System - Login</title>

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
                        <h3>Login to Your Account</h3>
                        <!-- Theme Toggle -->
                        <button id="theme-toggle" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-moon"></i> <!-- Default Moon Icon -->
                        </button>
                    </div>

                    <!-- Login Form -->
                    <form action="#" method="POST" id="loginForm">
                        <div class="mb-3">
                            <label>Email address</label>
                            <input type="email" class="form-control" placeholder="Enter your email" id="txt_username" name="txt_username">
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" class="form-control" placeholder="Enter your password" id="txt_password" name="txt_password">
                        </div>

                        <div class="d-flex justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe">
                                <label class="form-check-label small-text" for="rememberMe">Remember me</label>
                            </div>
                            <a href="forgot.php" class="small-text text-info">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-custom mt-3" id="btn_login" name="btn_login">Login</button>
						
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
