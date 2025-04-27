<?php
if (!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION["LOGIN_USERTYPE"])){
    $system_usertype = $_SESSION["LOGIN_USERTYPE"];
	$system_username = $_SESSION["LOGIN_USERNAME"];
}else{
	$system_usertype = "GUEST";
}

if($system_usertype != "GUEST"){
// if(!($system_usertype == "GUEST")){
    include_once('lib/db.php');

    if(isset($_POST['btn_changepassword'])){
        $username = $_POST["txt_email"];
        $currentPassword = $_POST["txt_password"];
        $newPassword = $_POST["txt_new_password"];
        $confirmPassword = $_POST["txt_confirm_password"];

        if ($newPassword === $confirmPassword) {
            // Check if the current password is correct
            $query = "SELECT * FROM login WHERE username = '$username' AND status = 'active'";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $db_password = $row['password'];

                if (password_verify($currentPassword, $db_password)) {
                    // Hash the new password
                    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    // Update the password in the database
                    $updateQuery = "UPDATE login SET password='$hashedNewPassword' WHERE username='$username'";
                    if (mysqli_query($conn, $updateQuery)) {
                        // session_regenerate_id(true);
                        session_destroy();
                        echo '<script>alert("Password changed successfully. Please Login to continue");</script>';
                        echo "<script>window.location.href='login.php';</script>";
                    } else {
                        echo '<script>alert("Error updating password. Please try again.");</script>';
                    }
                } else {
                    echo '<script>alert("Current password is incorrect.");</script>';
                }
            } else {
                echo '<script>alert("Username not found or account is inactive.");</script>';
            }
        }else {
            echo '<script>alert("New password and confirm password do not match.");</script>';
        }
    }
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Unified Courts Management System of Kilinochchi - Change Password</title>

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
                            <h3 class="mb-4">Change Password</h3>
                        </div>

                        <!-- Login Form -->
                        <form action="#" method="POST" id="changepasswordForm" onsubmit="return checkpwd()">
                            <div class="mb-3">
                                <label>Enter your Username</label>
                                <input type="email" value="<?php echo $system_username;?>" name="txt_email" id="txt_email" class="form-control" placeholder="Enter your email" onblur="validateEmail('txt_email')" readonly required>
                            </div>
                            <div class="mb-3">
                                <label>Enter your Current Password</label>
                                <input type="password" name="txt_password" id="txt_password" class="form-control" placeholder="Enter your current password" required>
                            </div>

                            <div class="mb-3">
                                <label>Enter your New Password</label>
                                <input type="password" name="txt_new_password" id="txt_new_password" class="form-control" placeholder="Enter your new password" required>
                            </div>

                            <div class="mb-3">
                                <label>Confirm your New Password</label>
                                <input type="password" name="txt_confirm_password" id="txt_confirm_password" class="form-control" placeholder="Confirm your new password" required>
                            </div>

                            <button type="submit" name="btn_changepassword" id="btn_changepassword" class="btn btn-custom mt-3">Change Password</button>
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
                const form = document.getElementById("changepasswordForm");
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
    echo "<script>alert('You are not logged in. Please log in to access this page.');</script>";
    echo "<script>window.location.href='index.php';</script>";
}
