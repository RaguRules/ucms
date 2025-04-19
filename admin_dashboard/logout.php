<?php
// Logout script for the Unified Courts Management System Admin Dashboard
session_start();
include_once('includes/auth.php');

// Log the user out
logoutUser();

// Redirect to login page
header("Location: login.php");
exit;
?>
