<!DOCTYPE html>
<html lang="en">
	<?php
		include_once('menu.php');
		include_once('db.php');
		?>
	<head>
		<meta charset="utf-8">
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<title>Kilinochchi Courts Management System</title>
		<meta name="description" content="Kilinochchi Courts Management System for efficient case management, scheduling, and document handling.">
		<meta name="keywords" content="Kilinochchi Courts, Case Management, Court System, Legal System">
		<link href="assets/img/favicon.png" rel="icon">
		<link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
		<link href="https://fonts.googleapis.com" rel="preconnect">
		<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
		<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
		<link href="assets/vendor/aos/aos.css" rel="stylesheet">
		<link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
		<link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
		<link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
		<link href="assets/css/main.css" rel="stylesheet">
		<link href="assets/css/ragu.css" rel="stylesheet">
	</head>
	<body>
		<?php
			if(isset($_GET['pg'])){
			    $page = $_GET['pg'];
			    include_once("$page");
			}else{
			    include_once("body.php");
			    include_once("footer.php");
			}
			?>
		<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
		<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="assets/vendor/php-email-form/validate.js"></script>
		<script src="assets/vendor/aos/aos.js"></script>
		<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
		<script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
		<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
		<script src="assets/vendor/jquery3.7/jquery.min.js"></script>
		<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<!-- <script src="assets/js/main.js"></script> -->
	</body>
</html>