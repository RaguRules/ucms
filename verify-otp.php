<?php
if (!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION["VERIFICATIONCODE_USERNAME"])){
    include_once('db.php');
    include_once('jsfunctions.php');

    if(isset($_POST['btn_verify'])){
        $code = $_POST["int_otp"];
        $username = $_SESSION["VERIFICATIONCODE_USERNAME"];

        $query = "SELECT otp FROM login WHERE username = '$username' AND status = 'active'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        $db_otp = $row['otp'];

        if ($code === $db_otp) {
            unset($_SESSION["VERIFICATIONCODE_USERNAME"]);
            $_SESSION["FORGOTCHANGEPASSWORD"] = $username;
            echo '<script>alert("OTP verified successfully. Now change your password");</script>';
            echo "<script>window.location.href='forgetchangepassword.php';</script>";

        } else {
            echo '<script>alert("Invalid OTP. Please try again.");</script>';
        }
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
        <link href="assets/css/auth.css" rel="stylesheet">
    </head>

    <body class="light-mode">
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
                            <h3 class="mb-4">Enter your OTP</h3>
                        </div>

                        <!-- Login Form -->
                        <form action="#" method="POST" id="verifyOtpForm">
                            <div class="mb-3">
                                <label>Enter your OTP that you received</label>
                                <input type="number" name="int_otp" id="int_otp" name="verify-otp" class="form-control" placeholder="Enter your OTP" onkeypress="return isNumberKey(event)" required>
                            </div>

                            <button name="btn_verify" id="btn_verify" type="submit" class="btn btn-custom mt-3">Verify OTP</button>
                        </form>

                        <!-- Footer -->
                        <div class="footer">
                            © 2025 Unified Courts Management System - Kilinochchi. All rights reserved.
                        </div>
                    </div>
                </div>

            </div>
        </div>

    
        <!-- Scripts -->
        <!-- <script src="assets/js/jsfunctions.js"></script>   -->
        <script src="assets/vendor/jquery3.7/jquery.min.js"></script>
        <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    </body>

<?php
}else{
    echo "<script>window.location.href='login.php';</script>";
}
?>