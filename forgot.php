<?php

if (!isset($_SESSION)){
    session_start();
}

include_once('lib/db.php');
include_once('lib/sms_beep.php');

if(isset($_SESSION["FORGOT_USERNAME"])){
    $username = $_SESSION["FORGOT_USERNAME"];
    $readonlystatus = "readOnly";

}else{
    $username = "";
    $readonlystatus = "";
    
}

if(isset($_POST["btn_forgot"])){
    $username = $_POST["txt_email"];
    $mobile = $_POST["int_mobile"];

    $query = "SELECT * FROM login WHERE username = '$username' AND status = 'active'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0){
        $dbMobile ='';
        $row = mysqli_fetch_assoc($result);
        $db_role = $row['role_id'];
        switch($db_role){
            case "R01":
            case "R02":
            case "R03":
            case "R04":
            case "R05":
                $query = "SELECT * FROM staff WHERE email = '$username'";
                break;
            case "R06":
                $query = "SELECT * FROM lawyer WHERE email = '$username'";
                break;
            case "R07":
                $query = "SELECT * FROM police WHERE email = '$username'";
                break;
            default:
                echo '<script> alert("Something went wrong. Conatact Admin Officer"); </script>';
                break;
        }

        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result); 

        $dbMobile = $row['mobile'];

        if (mysqli_num_rows($result) > 0) {
        
            if($mobile == $dbMobile){
                $otp = rand(100000, 999999); // Generate a random 6-digit OTP
                $sqlUpdate = "UPDATE login SET otp = '$otp' WHERE username = '$username'";
                mysqli_query($conn, $sqlUpdate);
                echo "<script> alert('OTP has been Generated'); </script>";
                $to = "94".$dbMobile;
                $message = "If you've requested to reset your password, your verification code is $otp. - Kilinochchi Courts";

                $response = sendSms($to, $message);

                if ($response['status']) {
                    unset($_SESSION["FORGOT_USERNAME"]);
                    $_SESSION["VERIFICATIONCODE_USERNAME"] = $username;
                    echo "<script>window.location.href='verify-otp.php';</script>";
                } else {
                    echo "<script>alert('{$response['message']}');</script>";
                }

            } elseif ($mobile == "") {
                echo '<script> alert("Please enter your registered mobile number"); </script>';
            } else {
                echo '<script> alert("Invalid mobile number that you have given for username"); </script>';
            }
        }else{
            echo '<script> alert("No matching record found."); </script>';
        }

    } else {
        echo '<script> alert("Either invalid username or Account has not been activated yet."); </script>';
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

            <!-- Right Fotgot Form -->
            <div class="col-md-6">
                <div class="login-container">
                    <div class="d-flex justify-content-between">
                        <h3 class="mb-4">Forgot your password?</h3>
                    </div>

                    <!-- Forgot Form -->
                    <form action="#" method="POST" id="forgotPwdForm">
                        <div class="mb-3">
                            <label>Enter your Username</label>
                            <input type="email" value="<?php echo $username;?>" name="txt_email" id="txt_email" class="form-control" placeholder="Enter your email" onblur="validateEmail('txt_email')" required>
                        </div>

                        <div class="mb-3">
                            <label>Enter your Registered Mobile</label>
                            <input type="int" name="int_mobile" id="int_mobile" class="form-control" placeholder="Enter your mobile"  onkeypress="return isNumberKey(event)" onblur="validateMobileNumber('int_mobile')" required>
                        </div>

                        <button name="btn_forgot" id="btn_forgot" type="submit" class="btn btn-custom mt-3">Reset Password</button>
                    </form>

                    <!-- Register Link -->
                    <div class="text-center mt-3 small-text">
                        Go back to Login? <a href="login.php" class="text-info">Back to Login</a>
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
    <script src="assets/js/jsfunctions.js"></script>    

</body>
</html>
