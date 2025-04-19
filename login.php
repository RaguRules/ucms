<?php
if (!isset($_SESSION)){
    session_start();
}

include_once('db.php');
include_once('jsfunctions.php');

$username = '';
if(isset($_SESSION["FORGOTPASSWORDCHANGED"])){
    $username = $_SESSION["FORGOTPASSWORDCHANGED"] ;
    unset($_SESSION["FORGOTPASSWORDCHANGED"]);
}


if (isset($_POST["btn_login"])) {

    $username = mysqli_real_escape_string($conn, $_POST["txt_email"]);
    $password = mysqli_real_escape_string($conn, $_POST["txt_password"]);
		
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sqlUser = "SELECT * FROM login WHERE username = '$username'";
    $resultUser = mysqli_query($conn, $sqlUser);
    
    if (mysqli_num_rows($resultUser) > 0){
        $rowUser = mysqli_fetch_assoc($resultUser);
        
        $extractedPassword = $rowUser['password'];

        if (password_verify($password, $extractedPassword)) {

            if($rowUser['attempt'] < 3) {
  
                $status = $rowUser['status'];

                switch ($status) {
                    case "active":
                        $_SESSION["LOGIN_USERNAME"] = $rowUser['username'];
                        $_SESSION["LOGIN_USERTYPE"] = $rowUser['role_id'];

                        $attempt = 0 ;
                        $sqlAttemptReset="UPDATE login SET attempt = '$attempt' WHERE username='$username'";
                        $sqlAttemptReset=mysqli_query($conn,$sqlAttemptReset)or die("Error in sql_update".mysqli_error($conn));
                        
                        echo "<script>window.location.href='index.php';</script>";
                        break;

                    case "pending":
                        echo '<script> alert("Your membership is pending for approval. Check later"); </script>';
                        break;

                    case "Blocked":
                        echo '<script> alert("Your account is Blocked. Contact Administrator"); </script>';
                        break;

                    case "deleted":
                        echo '<script> alert("Your account is deleted. Contact Administrator"); </script>';
                        break;

                    default:
                        echo '<script> alert("Something went wrong. Conatact Admin Officer"); </script>';
                        break;
                }

            }else{
                echo "<script> alert('Your account is blocked due to multiple incorrect password attempts. Please reset your password to regain access.')</script>";
                $_SESSION["FORGOT_USERNAME"] = $username;
                echo "<script> location.href='forgot.php'; </script>";
            }

        }elseif($rowUser['attempt'] < 3){
                $attempt = $rowUser['attempt'];
                $attempt = $attempt + 1 ;
                $sqlUpdate="UPDATE login SET attempt = '$attempt' WHERE username='$username'";
	
                $attemptUpdate=mysqli_query($conn,$sqlUpdate)or die("Error in sql_update".mysqli_error($conn));
                echo "<script> alert('Incorrect Credentials, Please check your Username/ Password')</script>";

        }else{
            $_SESSION["FORGOT_USERNAME"] = $username;
            echo "<script> alert('You have exceeded maximum attemps, Kindly reset your password')</script>";
            $_SESSION["FORGOT_USERNAME"] = $username;
            echo "<script> location.href='forgot.php'; </script>";
        }   


    }else{
        echo "<script> alert('Your account doesn\\'t  exist. Create new account with our system in order to use.')</script>";
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

<body> 
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
                    </div>

                    <!-- Login Form -->
                    <form action="#" method="POST" id="loginForm">
                        <div class="mb-3">
                            <label>Enter your Username</label>
                            <input type="email" value="<?php echo $username;?>" name="txt_email" id="txt_email" class="form-control" placeholder="Enter your email" onblur="validateEmail('txt_email')" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="txt_password" id="txt_password" class="form-control" placeholder="Enter your password" required>
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


<!-- Bootstrap Modal for Custom Alert -->
<!-- <div class="modal fade" id="customAlertModal" tabindex="-1" aria-labelledby="customAlertLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="customAlertLabel">Notice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="customAlertBody">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>


<script>
function showCustomAlert(message, title = 'Notice') {
    document.getElementById('customAlertLabel').innerText = title;
    document.getElementById('customAlertBody').innerHTML = message;
    
    var myModal = new bootstrap.Modal(document.getElementById('customAlertModal'));
    myModal.show();
}
</script> -->

<!-- Scripts -->
<script src="assets/vendor/jquery3.7/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
