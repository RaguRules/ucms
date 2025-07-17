<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Kilinochchi Courts Management System - Login</title>
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
						<button id="theme-toggle" class="btn btn-sm btn-outline-secondary">
						<i class="fas fa-moon"></i>
						</button>
					</div>
					<form>
						<div class="mb-3">
							<label>Enter your New Password</label>
							<input type="email" class="form-control" placeholder="Enter your new password">
						</div>
						<div class="mb-3">
							<label>Re-enter your New Password</label>
							<input type="password" class="form-control" placeholder="Re-Enter your new password">
							<button type="submit" class="btn btn-custom mt-3">Change Password</button>
					</form>
					<div class="footer">
					© 2025 Unified Courts Management System - Kilinochchi. All rights reserved.
					</div>
					</div>
				</div>
			</div>
		</div>
		<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="assets/vendor/jquery3.7/jquery.min.js"></script>
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
			        themeIcon.classList.replace("fa-moon", "fa-sun");
			    } else {
			        body.classList.add("light-mode");
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
			
			
			applySavedTheme();
		</script>
	</body>
</html>