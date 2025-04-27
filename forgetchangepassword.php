<?php
if (!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION["FORGOTCHANGEPASSWORD"])){
    $username = $_SESSION["FORGOTCHANGEPASSWORD"];
    $readonlystatus = "readonly";
        include_once('lib/db.php');

    if(isset($_POST['btn_forgot_change_password'])){
        $password = $_POST["txt_new_password"];
        $confirm_password = $_POST["txt_confirm_password"];

        if($password == $confirm_password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE login SET password = '$hashedPassword' WHERE username = '$username' AND status = 'active'";
            mysqli_query($conn, $query) or die("Error in sql_update".mysqli_error($conn));;

            $attempt = 0 ;
            $sqlAttemptReset="UPDATE login SET attempt = '$attempt' WHERE username='$username'";
            $sqlAttemptReset=mysqli_query($conn,$sqlAttemptReset) or die("Error in sql_update".mysqli_error($conn));

            unset($_SESSION["FORGOTCHANGEPASSWORD"]);
            $_SESSION["FORGOTPASSWORDCHANGED"] = $username;

            echo '<script>alert("Password changed successfully. You can now log in with your new password.");</script>';
            echo "<script>window.location.href='login.php';</script>";

        } else {
            echo '<script>alert("Passwords do not match. Please try again.");</script>';
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

    <body class="light-mode">


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
                            <h3 class="mb-4">Reset Password</h3>
                        </div>

                        <!-- Forgotchangepassword Form -->
                        <form action="#" method="POST" id="forgotPwdForm" onsubmit="return checkpwd()">
                            <div class="mb-3">
                                <label>Enter your Username</label>
                                <input type="email" value="<?php echo $username; ?>" name="txt_email" id="txt_email" class="form-control" placeholder="Enter your email" onblur="validateEmail('txt_email')" $readonlystatus required>
                            </div>

                            <div class="mb-3">
                                <label>Enter your New Password</label>
                                <input type="text" name="txt_new_password" id="txt_new_password" class="form-control" placeholder="Enter your new password" required>
                            </div>

                            <div class="mb-3">
                                <label>Confirm your New Password</label>
                                <input type="text" name="txt_confirm_password" id="txt_confirm_password" class="form-control" placeholder="Confirm your new password" required>
                            </div>

                            <button name="btn_forgot_change_password" id="btn_forgot_change_password" type="submit" class="btn btn-custom mt-3">Reset Password</button>
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
        <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/vendor/jquery3.7/jquery.min.js"></script>
        <script src="assets/js/jsfunctions.js"></script>
        
        <script>
            function checkpwd () {
                const form = document.getElementById("forgotPwdForm");
                const newPassword = document.getElementById("txt_new_password");
                const confirmPassword = document.getElementById("txt_confirm_password");

                if (newPassword.value == confirmPassword.value) {
                    return true;
                }else{
                    alert("Passwords do not match. Check, try again.");
                    document.getElementById("txt_new_password").value = "";
                    document.getElementById("txt_confirm_password").value = "";
                    return false;
                }                
            };
        </script>
    </body>
    </html>

<?php
}else{
    echo "<script>window.location.href='login.php';</script>";
}
?>