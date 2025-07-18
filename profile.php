<?php
include 'lib/sms_beep.php';

if($systemUsertype == 'GUEST'){
    echo "<script>location.href='index.php?pg=404.php';</script>";
}

$userId = $helper->getId($_SESSION["LOGIN_USERNAME"], $_SESSION["LOGIN_USERTYPE"]);
$row = null;

if($systemUsertype == 'R01' || $systemUsertype == 'R02' || $systemUsertype == 'R03' || $systemUsertype == 'R04' || $systemUsertype == 'R05') {
    $row = $helper->getStaffData($userId);
} elseif ($systemUsertype == 'R06') {
    $row = $helper->getLawyerData($userId);
} elseif ($systemUsertype == 'R07') {
    $row = $helper->getPoliceData($userId);
} else {
    die("Invalid user type.");
}

if (isset($_POST["update_profile_photo"])) {

    // Upload image only if a new one was selected
    $newImageUploaded = ($_FILES['img_profile_photo']['error'] !== 4); // Error 4 = no file uploaded

    if ($newImageUploaded) {
        $uploadResult = $security->uploadImage('img_profile_photo');
        if (!$uploadResult['success']) {
            die("Image upload failed: " . $uploadResult['error']);
        }
        $imagePath = 'uploads/' . $uploadResult['filename'];
    }

    if ($systemUsertype === 'R01' || $systemUsertype === 'R02' || $systemUsertype === 'R03' || $systemUsertype === 'R04' || $systemUsertype === 'R05') {
        $whom = 'staff';
        $id = 'staff_id';
    } elseif ($systemUsertype === 'R06') {
        $whom = 'lawyer';
        $id = 'lawyer_id';
    } elseif ($systemUsertype === 'R07') {
        $whom = 'police';
        $id = 'police_id';
    }else{
        $whom = '';
    }

    // Start Transaction
    $conn->begin_transaction();

    try {
        $stmtUpdate = $conn->prepare("UPDATE $whom SET image_path=? WHERE $id=?");
        
		$stmtUpdate->bind_param(
            "ss",
			$imagePath,
			$userId
        );
        $stmtUpdate->execute();

        $conn->commit();

        echo '<script>alert("Successfully changed profile picture.");</script>';
        echo "<script>location.href='index.php?pg=profile.php';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();

		// If new image was uploaded, delete it
        if ($newImageUploaded && file_exists($txtImagePath)) {
            unlink($txtImagePath);
        }

        Security::logError($e->getMessage());
        echo '<script>alert("An error occurred while updating. Please try again.");</script>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Unified Courts Management</title>
</head>
<body>
    <div class="container py-5">
        <h2>Profile</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Profile Photo</h5>
                        <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Profile Photo" class="img-fluid rounded-circle mb-3" width="150">
                    </div>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">Upload New Photo</button>
                   
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Personal Details</h5>
                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['mobile']); ?></p>
                        <p><strong>National Identity Card No:</strong> <?php echo htmlspecialchars($row['nic_number']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($row['address']); ?></p>
                        <p><strong>Status:</strong>
                            <?php if ($row['is_active']) { ?>
                                <span class="badge bg-success">Active</span>
                            <?php } else { ?>
                                <span class="badge bg-danger">Deleted</span>
                            <?php } ?>
                        </p>
                    </div>
                </div>
                <!-- Change Password Section -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Change Password</h5>
                        <form action="forgot.php" method="POST">
                            <button type="submit" name="change_password" class="btn btn-info">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Uploading Profile Photo -->
    <div class="modal fade" id="uploadPhotoModal" tabindex="-1" aria-labelledby="uploadPhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadPhotoModalLabel">Upload New Profile Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="index.php?pg=profile.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="img_profile_photo" class="form-label">Upload New Photo</label>
                            <input type="file" name="img_profile_photo" class="form-control" id="img_profile_photo">
                        </div>
                        <button type="submit" name="update_profile_photo" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
