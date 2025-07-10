<?php
if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION["LOGIN_USERTYPE"])) {
    $systemUsertype = $_SESSION["LOGIN_USERTYPE"];
    $systemUsername = $_SESSION["LOGIN_USERNAME"];
} else {
    $systemUsertype = "GUEST";
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    unset($_SESSION["LOGIN_USERNAME"]);
    unset($_SESSION["LOGIN_USERTYPE"]);
    session_destroy();
    echo "<script>location.href='login.php';</script>";
    exit;
}

include_once('lib/db.php');
require_once('lib/security.php');
require_once('lib/helper.php');

Security::logVisitor();
Security::blockAccessByIP([
    '123.456.789.000',
    '192.168.1.25',
    '203.0.113.42'
]);

$helper = new Helper($conn);
$security = new Security();

$allowed_pages = [
    'cases.php', 'appeal.php', 'motion.php', 'judgement.php',
    'warrant.php', 'parties.php', 'dailycaseactivities.php',
    'order.php', 'notification.php', 'notes.php', 'courts.php',
    'staff.php', 'lawyer.php', 'police.php', 'roles.php',
    'about.php', 'contact.php', 'approve.php', 'caselist.php'
];
?>

<!DOCTYPE html>
<html lang="en">
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
    <link rel="stylesheet" href="assets/css/font.css">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/ragu.css" rel="stylesheet">
    <link href="assets/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body>

<?php
include_once('menu.php');

if (isset($_GET['pg']) && in_array($_GET['pg'], $allowed_pages)) {
    echo "<div id='dynamic-content'>";
    include_once($_GET['pg']);
    echo "</div>";
} else {
    include_once("body.php");
    include_once("footer.php");
}
?>

<!-- JS Scripts -->
<script src="assets/vendor/jquery3.7/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="assets/js/jsfunctions.js"></script>
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/dataTables.bootstrap5.min.js"></script>

</body>
</html>
