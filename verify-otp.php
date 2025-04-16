<?php
require 'vendor/autoload.php';

use RobThree\Auth\TwoFactorAuth;

$tfa = new TwoFactorAuth('KilinochchiCourts');

$secret = $_POST['secret'];
$otp = $_POST['otp'];

if ($tfa->verifyCode($secret, $otp)) {
    echo "✅ OTP Verified Successfully!";
} else {
    echo "❌ Invalid OTP! Please try again.";
}
?>
