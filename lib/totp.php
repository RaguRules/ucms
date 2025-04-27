<?php
require 'vendor/autoload.php'; // Load dependencies

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;

// Initialize QR Code provider
$qrcodeProvider = new EndroidQrCodeProvider();

// Create Two-Factor Authentication instance
$tfa = new TwoFactorAuth('KilinochchiCourts', 6, 30, 'sha1', $qrcodeProvider);

// Generate a unique secret for the user
$secret = $tfa->createSecret();

// Generate QR Code URL
$qrCodeUrl = $tfa->getQRCodeImageAsDataUri('User@KilinochchiCourts', $secret);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Two-Factor Authentication</title>
</head>
<body>
    <h2>Scan this QR Code to Enable 2FA</h2>
    <img src="<?php echo $qrCodeUrl; ?>" alt="TOTP QR Code">
    
    <p>Or manually enter this secret into your authentication app:</p>
    <strong><?php echo $secret; ?></strong>

    <p>Once scanned, enter the 6-digit OTP from your app to verify.</p>

    <form action="verify_otp.php" method="post">
        <input type="hidden" name="secret" value="<?php echo $secret; ?>">
        <label for="otp">Enter OTP:</label>
        <input type="text" id="otp" name="otp" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
