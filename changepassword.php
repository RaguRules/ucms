<?php
if (!isset($_SESSION)){
    session_start();
}

include_once('db.php');
include_once('jsfunctions.php');

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
                    <form action="#" method="POST" id="changepasswordForm">
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

                        <button type="submit" class="btn btn-custom mt-3">Chage Password</button>
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
    

</body>
</html>
