<?php
// Include database connection
require_once('db.php');

// Start session if not already started
if (!isset($_SESSION)) {
    session_start();
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
// $type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?? '';
$type = htmlspecialchars(trim($_GET['type'] ?? ''));
$errors = [];
$formData = [];
$success = false;

// Validate user type
if ($type === 'lawyer') {
    $role = "LAWYER";
    $role_id = "R06";
} elseif ($type === 'police') {
    $role = "POLICE";
    $role_id = "R07";
} else {
    // Redirect to index if invalid type
    header("Location: index.php");
    exit;
}

// Function to generate next registration ID
function generateNextRegistrationID($conn) {
    // Find the highest existing reg_id
    $sql = "SELECT MAX(reg_id) AS max_id FROM registration";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $max_id = $row['max_id'];
        
        if ($max_id === null) {
            // No IDs exist yet, start with R0001
            return "R0001";
        } else {
            // Extract the numeric part, increment, and format
            $numeric_part = (int)substr($max_id, 1);
            $next_numeric_part = $numeric_part + 1;
            // Pad with leading zeros to 4 digits, then prepend "R"
            return "R" . str_pad($next_numeric_part, 4, "0", STR_PAD_LEFT);
        }
    } else {
        // Handle errors
        return "R0001";
    }
}

// Function to validate input
function validateInput($data, $field, $type = 'string') {
    $value = trim($data);
    
    // Check if required field is empty
    if (empty($value)) {
        return ["status" => false, "message" => "This field is required"];
    }
    
    // Validate based on type
    switch ($type) {
        case 'email':
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return ["status" => false, "message" => "Invalid email format"];
            }
            break;
        case 'mobile':
            if (!preg_match('/^[0-9]{10}$/', $value)) {
                return ["status" => false, "message" => "Mobile number must be 10 digits"];
            }
            break;
        case 'nic':
            if (!(preg_match('/^[0-9]{9}[vVxX]$/', $value) || preg_match('/^[0-9]{12}$/', $value))) {
                return ["status" => false, "message" => "NIC must be 9 digits followed by V/X or 12 digits"];
            }
            break;
        case 'password':
            if (strlen($value) < 8) {
                return ["status" => false, "message" => "Password must be at least 8 characters"];
            }
            if (!preg_match('/[A-Z]/', $value)) {
                return ["status" => false, "message" => "Password must contain at least one uppercase letter"];
            }
            if (!preg_match('/[a-z]/', $value)) {
                return ["status" => false, "message" => "Password must contain at least one lowercase letter"];
            }
            if (!preg_match('/[0-9]/', $value)) {
                return ["status" => false, "message" => "Password must contain at least one number"];
            }
            if (!preg_match('/[^A-Za-z0-9]/', $value)) {
                return ["status" => false, "message" => "Password must contain at least one special character"];
            }
            break;
    }
    
    return ["status" => true, "value" => $value];
}

// Function to check if value exists in database
function checkDuplicate($conn, $table, $field, $value) {
    $sql = "SELECT COUNT(*) as count FROM $table WHERE $field = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] > 0;
}

// Function to securely upload image
function secureImageUpload($fileInputName) {
    // Define allowed file types and max size
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    // Check if file was uploaded
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return ["success" => false, "error" => "File upload failed or no file selected"];
    }
    
    $file = $_FILES[$fileInputName];
    
    // Validate file size
    if ($file['size'] > $maxSize) {
        return ["success" => false, "error" => "File size exceeds the 2MB limit"];
    }
    
    // Validate file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $fileType = $finfo->file($file['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        return ["success" => false, "error" => "Invalid file type. Only JPEG, PNG, and GIF are allowed"];
    }
    
    // Generate a secure filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;
    $uploadDir = 'uploads/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $destination = $uploadDir . $newFilename;
    
    // Move the file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ["success" => false, "error" => "Failed to move uploaded file"];
    }
    
    return ["success" => true, "filename" => $newFilename, "path" => $destination];
}

// Get next registration ID
$next_reg_id = generateNextRegistrationID($conn);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn_add'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = "Invalid form submission";
    } else {
        // Validate and sanitize inputs
        $fields = [
            'txt_reg_id' => ['value' => $_POST['txt_reg_id'] ?? '', 'type' => 'string', 'required' => true],
            'txt_first_name' => ['value' => $_POST['txt_first_name'] ?? '', 'type' => 'string', 'required' => true],
            'txt_last_name' => ['value' => $_POST['txt_last_name'] ?? '', 'type' => 'string', 'required' => true],
            'int_mobile' => ['value' => $_POST['int_mobile'] ?? '', 'type' => 'mobile', 'required' => true],
            'txt_email' => ['value' => $_POST['txt_email'] ?? '', 'type' => 'email', 'required' => true],
            'txt_address' => ['value' => $_POST['txt_address'] ?? '', 'type' => 'string', 'required' => true],
            'txt_nic_number' => ['value' => $_POST['txt_nic_number'] ?? '', 'type' => 'nic', 'required' => true],
            'txt_password' => ['value' => $_POST['txt_password'] ?? '', 'type' => 'password', 'required' => true],
            'txt_confirm_password' => ['value' => $_POST['txt_confirm_password'] ?? '', 'type' => 'string', 'required' => true],
            'select_station' => ['value' => $_POST['select_station'] ?? '', 'type' => 'string', 'required' => true],
            'select_gender' => ['value' => $_POST['select_gender'] ?? '', 'type' => 'string', 'required' => true],
            'date_date_of_birth' => ['value' => $_POST['date_date_of_birth'] ?? '', 'type' => 'string', 'required' => true],
        ];
        
        // Add type-specific fields
        if ($type === 'lawyer') {
            $fields['txt_enrolment_number'] = ['value' => $_POST['txt_enrolment_number'] ?? '', 'type' => 'string', 'required' => true];
        } elseif ($type === 'police') {
            $fields['int_badge_number'] = ['value' => $_POST['int_badge_number'] ?? '', 'type' => 'string', 'required' => true];
        }
        
        // Validate each field
        foreach ($fields as $field => $config) {
            $validation = validateInput($config['value'], $field, $config['type']);
            
            if ($validation['status']) {
                $formData[$field] = $validation['value'];
            } else {
                $errors[$field] = $validation['message'];
            }
        }
        
        // Check if passwords match
        if (!isset($errors['txt_password']) && !isset($errors['txt_confirm_password'])) {
            if ($formData['txt_password'] !== $formData['txt_confirm_password']) {
                $errors['txt_confirm_password'] = "Passwords do not match";
            }
        }
        
        // Check for duplicate email
        if (!isset($errors['txt_email']) && checkDuplicate($conn, 'registration', 'email', $formData['txt_email'])) {
            $errors['txt_email'] = "Email already exists";
        }
        
        // Check for duplicate NIC
        if (!isset($errors['txt_nic_number']) && checkDuplicate($conn, 'registration', 'nic_number', $formData['txt_nic_number'])) {
            $errors['txt_nic_number'] = "NIC number already exists";
        }
        
        // Check for duplicate mobile
        if (!isset($errors['int_mobile']) && checkDuplicate($conn, 'registration', 'mobile', $formData['int_mobile'])) {
            $errors['int_mobile'] = "Mobile number already exists";
        }
        
        // Process file upload if no validation errors
        if (empty($errors)) {
            $upload_result = secureImageUpload('img_profile_photo');
            
            if (!$upload_result['success']) {
                $errors['img_profile_photo'] = $upload_result['error'];
            } else {
                $formData['txt_image_path'] = $upload_result['path'];
                
                // Set joined date to current date
                $formData['date_joined_date'] = date('Y-m-d');
                
                // Hash password
                $hashedPassword = password_hash($formData['txt_password'], PASSWORD_DEFAULT);
                
                // Set status to pending
                $status = "Pending";
                
                // Begin transaction
                $conn->begin_transaction();
                
                try {
                    // Prepare registration insert statement
                    $sqlInsert = "INSERT INTO registration (
                        reg_id, first_name, last_name, mobile, email, address, 
                        nic_number, enrolment_number, badge_number, station, 
                        joined_date, date_of_birth, status, role_id, 
                        password, image_path, gender
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($sqlInsert);
                    
                    // Set empty values for type-specific fields
                    $enrolment_number = $type === 'lawyer' ? $formData['txt_enrolment_number'] : '';
                    $badge_number = $type === 'police' ? $formData['int_badge_number'] : '';
                    
                    $stmt->bind_param(
                        "sssssssssssssssss",
                        $formData['txt_reg_id'],
                        $formData['txt_first_name'],
                        $formData['txt_last_name'],
                        $formData['int_mobile'],
                        $formData['txt_email'],
                        $formData['txt_address'],
                        $formData['txt_nic_number'],
                        $enrolment_number,
                        $badge_number,
                        $formData['select_station'],
                        $formData['date_joined_date'],
                        $formData['date_date_of_birth'],
                        $status,
                        $role_id,
                        $hashedPassword,
                        $formData['txt_image_path'],
                        $formData['select_gender']
                    );
                    
                    $stmt->execute();
                    
                    // Prepare login insert statement
                    $sqlLoginInsert = "INSERT INTO `login` (
                        `username`, `password`, `otp`, `status`, `role_id`
                    ) VALUES (?, ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($sqlLoginInsert);
                    
                    // Generate random OTP
                    $otp = sprintf("%04d", mt_rand(1000, 9999));
                    
                    $stmt->bind_param(
                        "sssss",
                        $formData['txt_email'],
                        $hashedPassword,
                        $otp,
                        $status,
                        $role_id
                    );
                    
                    $stmt->execute();
                    
                    // Commit transaction
                    $conn->commit();
                    
                    // Set success flag
                    $success = true;
                    
                    // Reset form data
                    $formData = [];
                    
                    // Generate new registration ID for next user
                    $next_reg_id = generateNextRegistrationID($conn);
                    
                    // Regenerate CSRF token
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    $errors['database'] = "Database error: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Kilinochchi Courts Management System - Registration</title>
    <meta name="description" content="Kilinochchi Courts Management System for efficient case management, scheduling, and document handling.">
    <meta name="keywords" content="Kilinochchi Courts, Case Management, Court System, Legal System">
    
    <!-- Favicon -->
    <link href="../assets/img/favicon.png" rel="icon">
    <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"  crossorigin="anonymous"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

</head>

<body class="bg-gray-50 font-sans">
    <!-- Header -->
    <header class="bg-primary-600 text-white py-4 shadow-md">
        <div class="container mx-auto px-4">
            <h1 class="text-2xl md:text-3xl font-bold text-center">Registration</h1>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <?php if ($success): ?>
        <!-- Success Message -->
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline"> Your registration request has been submitted successfully. Please wait for admin approval.</span>
            <a href="index.php" class="mt-3 inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition-colors">
                Return to Home
            </a>
        </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Left Column - Image -->
            <div class="md:w-1/3">
                <div class="mt-8 text-center">
                    <img src="assets/img/auth/court.jpg" alt="Sri Lanka Courts" class="w-full rounded-lg shadow-lg">
                </div>
            </div>
            
            <!-- Right Column - Form -->
            <div class="md:w-2/3">
                <?php if (!empty($errors['csrf'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($errors['csrf']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($errors['database'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Database Error!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($errors['database']); ?></span>
                </div>
                <?php endif; ?>
                
                <form name="registration_form" action="register.php?type=<?php echo htmlspecialchars($type); ?>" method="POST" id="registrationForm" enctype="multipart/form-data" class="bg-white shadow-card rounded-lg p-6">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    
                    <!-- Hidden Fields -->
                    <input type="hidden" id="txt_reg_id" name="txt_reg_id" value="<?php echo htmlspecialchars($next_reg_id); ?>">
                    <input type="hidden" id="txt_role_id" name="txt_role_id" value="<?php echo htmlspecialchars($role_id); ?>">
                    
                    <!-- Personal Information Section -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Personal Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- First Name -->
                            <div class="form-group">
                                <label for="txt_first_name" class="form-label">First Name (Given Name) <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-input <?php echo isset($errors['txt_first_name']) ? 'border-red-500' : ''; ?>" 
                                    id="txt_first_name" 
                                    name="txt_first_name" 
                                    value="<?php echo htmlspecialchars($formData['txt_first_name'] ?? ''); ?>"
                                    required
                                >
                                <?php if (isset($errors['txt_first_name'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['txt_first_name']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Last Name -->
                            <div class="form-group">
                                <label for="txt_last_name" class="form-label">Last Name (Surname/Family Name) <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-input <?php echo isset($errors['txt_last_name']) ? 'border-red-500' : ''; ?>" 
                                    id="txt_last_name" 
                                    name="txt_last_name" 
                                    value="<?php echo htmlspecialchars($formData['txt_last_name'] ?? ''); ?>"
                                    required
                                >
                                <?php if (isset($errors['txt_last_name'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['txt_last_name']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Gender -->
                            <div class="form-group">
                                <label for="select_gender" class="form-label">Gender <span class="text-red-500">*</span></label>
                                <select 
                                    class="form-input <?php echo isset($errors['select_gender']) ? 'border-red-500' : ''; ?>" 
                                    id="select_gender" 
                                    name="select_gender" 
                                    required
                                >
                                    <option value="" disabled <?php echo empty($formData['select_gender']) ? 'selected' : ''; ?>>Select Gender</option>
                                    <option value="Male" <?php echo (isset($formData['select_gender']) && $formData['select_gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo (isset($formData['select_gender']) && $formData['select_gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo (isset($formData['select_gender']) && $formData['select_gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                                <?php if (isset($errors['select_gender'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['select_gender']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Date of Birth -->
                            <div class="form-group">
                                <label for="date_date_of_birth" class="form-label">Date of Birth <span class="text-red-500">*</span></label>
                                <input 
                                    type="date" 
                                    class="form-input <?php echo isset($errors['date_date_of_birth']) ? 'border-red-500' : ''; ?>" 
                                    id="date_date_of_birth" 
                                    name="date_date_of_birth" 
                                    value="<?php echo htmlspecialchars($formData['date_date_of_birth'] ?? ''); ?>"
                                    required
                                >
                                <?php if (isset($errors['date_date_of_birth'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['date_date_of_birth']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- NIC Number -->
                            <div class="form-group">
                                <label for="txt_nic_number" class="form-label">NIC Number <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-input <?php echo isset($errors['txt_nic_number']) ? 'border-red-500' : ''; ?>" 
                                    id="txt_nic_number" 
                                    name="txt_nic_number" 
                                    value="<?php echo htmlspecialchars($formData['txt_nic_number'] ?? ''); ?>"
                                    required
                                >
                                <?php if (isset($errors['txt_nic_number'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['txt_nic_number']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Address -->
                            <div class="form-group md:col-span-2">
                                <label for="txt_address" class="form-label">Address <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-input <?php echo isset($errors['txt_address']) ? 'border-red-500' : ''; ?>" 
                                    id="txt_address" 
                                    name="txt_address" 
                                    value="<?php echo htmlspecialchars($formData['txt_address'] ?? ''); ?>"
                                    required
                                >
                                <?php if (isset($errors['txt_address'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['txt_address']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information Section -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Contact Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Mobile Number -->
                            <div class="form-group">
                                <label for="int_mobile" class="form-label">Mobile Number <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-input <?php echo isset($errors['int_mobile']) ? 'border-red-500' : ''; ?>" 
                                    id="int_mobile" 
                                    name="int_mobile" 
                                    value="<?php echo htmlspecialchars($formData['int_mobile'] ?? ''); ?>"
                                    required
                                >
                                <?php if (isset($errors['int_mobile'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['int_mobile']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Email -->
                            <div class="form-group">
                                <label for="txt_email" class="form-label">Email / Username <span class="text-red-500">*</span></label>
                                <input 
                                    type="email" 
                                    class="form-input <?php echo isset($errors['txt_email']) ? 'border-red-500' : ''; ?>" 
                                    id="txt_email" 
                                    name="txt_email" 
                                    value="<?php echo htmlspecialchars($formData['txt_email'] ?? ''); ?>"
                                    required
                                >
                                <?php if (isset($errors['txt_email'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['txt_email']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Professional Information Section -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Professional Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Station -->
                            <div class="form-group">
                                <label for="select_station" class="form-label">Station <span class="text-red-500">*</span></label>
                                <?php if ($type === 'police'): ?>
                                <select 
                                    class="form-input <?php echo isset($errors['select_station']) ? 'border-red-500' : ''; ?>" 
                                    id="select_station" 
                                    name="select_station" 
                                    required
                                >
                                    <option value="" disabled <?php echo empty($formData['select_station']) ? 'selected' : ''; ?>>Select Police Station</option>
                                    <option value="Kilinochchi HQ" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'Kilinochchi HQ') ? 'selected' : ''; ?>>Kilinochchi HQ</option>
                                    <option value="Palai" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'Palai') ? 'selected' : ''; ?>>Palai</option>
                                    <option value="Poonakari" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'Poonakari') ? 'selected' : ''; ?>>Poonakari</option>
                                    <option value="Tharmapuram" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'Tharmapuram') ? 'selected' : ''; ?>>Tharmapuram</option>
                                    <option value="Jeyapuram" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'Jeyapuram') ? 'selected' : ''; ?>>Jeyapuram</option>
                                    <option value="Akkarayan" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'Akkarayan') ? 'selected' : ''; ?>>Akkarayan</option>
                                    <option value="Maruthankeni" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'Maruthankeni') ? 'selected' : ''; ?>>Maruthankeni</option>
                                    <option value="Ramanathapuram" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'Ramanathapuram') ? 'selected' : ''; ?>>Ramanathapuram</option>
                                    <option value="D.C.D.B" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'D.C.D.B') ? 'selected' : ''; ?>>D.C.D.B</option>
                                    <option value="C.I.D" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'C.I.D') ? 'selected' : ''; ?>>C.I.D</option>
                                </select>
                                <?php elseif ($type === 'lawyer'): ?>
                                <select 
                                    class="form-input <?php echo isset($errors['select_station']) ? 'border-red-500' : ''; ?>" 
                                    id="select_station" 
                                    name="select_station" 
                                    required
                                >
                                    <option value="" disabled <?php echo empty($formData['select_station']) ? 'selected' : ''; ?>>Job Type</option>
                                    <option value="Legal Aid Commission" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'Legal Aid Commission') ? 'selected' : ''; ?>>Legal Aid Commission</option>
                                    <option value="Private" <?php echo (isset($formData['select_station']) && $formData['select_station'] === 'Private') ? 'selected' : ''; ?>>Private</option>
                                </select>
                                <?php endif; ?>
                                <?php if (isset($errors['select_station'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['select_station']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Type-specific field -->
                            <div class="form-group">
                                <?php if ($type === 'police'): ?>
                                <label for="int_badge_number" class="form-label">Police Badge Number <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-input <?php echo isset($errors['int_badge_number']) ? 'border-red-500' : ''; ?>" 
                                    id="int_badge_number" 
                                    name="int_badge_number" 
                                    value="<?php echo htmlspecialchars($formData['int_badge_number'] ?? ''); ?>"
                                    required
                                >
                                <?php if (isset($errors['int_badge_number'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['int_badge_number']); ?></p>
                                <?php endif; ?>
                                <?php elseif ($type === 'lawyer'): ?>
                                <label for="txt_enrolment_number" class="form-label">Lawyer Enrolment/Supreme Court Reg. Number <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    class="form-input <?php echo isset($errors['txt_enrolment_number']) ? 'border-red-500' : ''; ?>" 
                                    id="txt_enrolment_number" 
                                    name="txt_enrolment_number" 
                                    value="<?php echo htmlspecialchars($formData['txt_enrolment_number'] ?? ''); ?>"
                                    required
                                >
                                <?php if (isset($errors['txt_enrolment_number'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['txt_enrolment_number']); ?></p>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Profile Photo -->
                            <div class="form-group md:col-span-2">
                                <label for="img_profile_photo" class="form-label">Upload Profile Photo <span class="text-red-500">*</span></label>
                                <input 
                                    type="file" 
                                    class="form-input <?php echo isset($errors['img_profile_photo']) ? 'border-red-500' : ''; ?>" 
                                    id="img_profile_photo" 
                                    name="img_profile_photo" 
                                    accept="image/*" 
                                    required
                                >
                                <p class="text-xs text-gray-500 mt-1">Accepted formats: JPEG, PNG, GIF. Maximum size: 2MB.</p>
                                <?php if (isset($errors['img_profile_photo'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['img_profile_photo']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Information Section -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Account Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Password -->
                            <div class="form-group">
                                <label for="txt_password" class="form-label">Password <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input 
                                        type="password" 
                                        class="form-input pr-10 <?php echo isset($errors['txt_password']) ? 'border-red-500' : ''; ?>" 
                                        id="txt_password" 
                                        name="txt_password" 
                                        required
                                    >
                                    <button 
                                        type="button" 
                                        id="togglePassword" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800"
                                    >
                                        <i class="bi bi-eye-slash" id="password-icon"></i>
                                    </button>
                                </div>
                                <div id="password-strength" class="mt-1 text-xs"></div>
                                <p class="text-xs text-gray-500 mt-1">8+ characters with uppercase, lowercase, number, and special character.</p>
                                <?php if (isset($errors['txt_password'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['txt_password']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Confirm Password -->
                            <div class="form-group">
                                <label for="txt_confirm_password" class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input 
                                        type="password" 
                                        class="form-input pr-10 <?php echo isset($errors['txt_confirm_password']) ? 'border-red-500' : ''; ?>" 
                                        id="txt_confirm_password" 
                                        name="txt_confirm_password" 
                                        required
                                    >
                                </div>
                                <?php if (isset($errors['txt_confirm_password'])): ?>
                                <p class="error-text"><?php echo htmlspecialchars($errors['txt_confirm_password']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="form-group mb-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input 
                                    type="checkbox" 
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                    id="acceptTerms" 
                                    name="accept_terms" 
                                    required
                                >
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="acceptTerms" class="font-medium text-gray-700">
                                    I accept the <a href="#" id="termsLink" class="text-blue-600 hover:underline">Terms & Conditions</a>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="submit" name="btn_add" id="btn_add" class="btn-primary">Submit Registration</button>
                        <button type="button" id="btn_clear" class="btn-secondary">Clear Form</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; <?php echo date('Y'); ?> Kilinochchi Courts Management System. All rights reserved.</p>
            <p class="text-sm mt-2">Developed by Srirajeswaran Raguraj, Court Interpreter, Kilinochchi Courts</p>
        </div>
    </footer>

    <!-- Terms Modal -->
    <div id="termsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">Unified Courts Management System of Kilinochchi</h3>
                    <button id="closeTermsModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold">1. Acceptance of Terms</h2>
                    <p>By checking the "I accept the Terms and Conditions" checkbox and accessing or using the System, you represent that you have read, understood, and agree to be legally bound by these Terms, and all applicable laws and regulations. This constitutes a legally binding agreement between you and the operators of the Unified Courts Management System for Kilinochchi.</p>
                    
                    <!-- Additional terms content here -->
                    <h2 class="text-lg font-semibold">2. User Eligibility and Accounts</h2>
                    <p><strong>2.1 Eligibility:</strong> The System is intended for authorized users only, including Court staffs, Hon. Judges, Lawyers, and other designated personnel as determined by the Kilinochchi Courts administration. Unauthorized access is strictly prohibited.</p>
                    
                    <!-- More terms content -->
                </div>
                
                <div class="mt-6 flex justify-end border-t pt-3">
                    <button id="agreeTerms" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        I Agree
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Error Modal -->
    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 id="passwordModalTitle" class="text-xl font-semibold text-gray-900">Password Error</h3>
                    <button id="closePasswordModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div id="passwordModalBody" class="text-gray-700">
                    <!-- Content will be updated by JS -->
                </div>
                
                <div class="mt-6 flex justify-end border-t pt-3">
                    <button id="passwordModalClose" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Password visibility toggle
            const passwordInput = document.getElementById("txt_password");
            const confirmInput = document.getElementById("txt_confirm_password");
            const toggleButton = document.getElementById("togglePassword");
            const passwordIcon = document.getElementById("password-icon");
            
            toggleButton.addEventListener("click", function() {
                const type = passwordInput.type === "password" ? "text" : "password";
                passwordInput.type = type;
                confirmInput.type = type;
                passwordIcon.classList.toggle("bi-eye");
                passwordIcon.classList.toggle("bi-eye-slash");
            });
            
            // Password strength meter
            const strengthDisplay = document.getElementById("password-strength");
            
            passwordInput.addEventListener("input", function() {
                const password = passwordInput.value;
                const strength = getPasswordStrength(password);
                updateStrengthDisplay(strength);
            });
            
            function getPasswordStrength(password) {
                let score = 0;
                if (password.length >= 8) score++;
                if (/[A-Z]/.test(password)) score++;
                if (/[a-z]/.test(password)) score++;
                if (/\d/.test(password)) score++;
                if (/[\W_]/.test(password)) score++;
                
                if (score <= 2) return "Weak";
                if (score === 3 || score === 4) return "Medium";
                if (score === 5) return "Strong";
            }
            
            function updateStrengthDisplay(strength) {
                const colors = {
                    Weak: "red",
                    Medium: "orange",
                    Strong: "green"
                };
                
                strengthDisplay.textContent = `Password Strength: ${strength}`;
                strengthDisplay.style.color = colors[strength] || "black";
            }
            
            // Form validation
            const form = document.getElementById("registrationForm");
            const passwordModal = document.getElementById("passwordModal");
            const passwordModalTitle = document.getElementById("passwordModalTitle");
            const passwordModalBody = document.getElementById("passwordModalBody");
            const passwordModalClose = document.getElementById("passwordModalClose");
            const closePasswordModal = document.getElementById("closePasswordModal");
            
            form.addEventListener("submit", function(e) {
                // Client-side validation can be added here if needed
                // Server-side validation will handle most cases
            });
            
            // Password modal close buttons
            passwordModalClose.addEventListener("click", function() {
                passwordModal.classList.add("hidden");
            });
            
            closePasswordModal.addEventListener("click", function() {
                passwordModal.classList.add("hidden");
            });
            
            // Terms and conditions modal
            const termsLink = document.getElementById("termsLink");
            const termsModal = document.getElementById("termsModal");
            const closeTermsModal = document.getElementById("closeTermsModal");
            const agreeTerms = document.getElementById("agreeTerms");
            const acceptTerms = document.getElementById("acceptTerms");
            
            termsLink.addEventListener("click", function(e) {
                e.preventDefault();
                termsModal.classList.remove("hidden");
            });
            
            closeTermsModal.addEventListener("click", function() {
                termsModal.classList.add("hidden");
            });
            
            agreeTerms.addEventListener("click", function() {
                acceptTerms.checked = true;
                termsModal.classList.add("hidden");
            });
            
            // Clear form button
            const clearButton = document.getElementById("btn_clear");
            
            clearButton.addEventListener("click", function() {
                const inputs = document.querySelectorAll("input:not([type='hidden']):not([type='checkbox']), select, textarea");
                inputs.forEach(input => {
                    input.value = "";
                    input.classList.remove("border-red-500");
                });
                
                const errorTexts = document.querySelectorAll(".error-text");
                errorTexts.forEach(error => {
                    error.textContent = "";
                });
                
                acceptTerms.checked = false;
                strengthDisplay.textContent = "";
            });
            
            // NIC number validation and auto-fill
            const nicInput = document.getElementById("txt_nic_number");
            const dobInput = document.getElementById("date_date_of_birth");
            const genderInput = document.getElementById("select_gender");
            
            nicInput.addEventListener("input", function() {
                const nic = nicInput.value.trim();
                let year = "";
                let dayOfYear = "";
                let gender = "";
                
                if (nic.length === 10 && /^[0-9]{9}[VvXx]$/.test(nic)) {
                    year = "19" + nic.substring(0, 2);
                    dayOfYear = parseInt(nic.substring(2, 5));
                } else if (nic.length === 12 && /^[0-9]{12}$/.test(nic)) {
                    year = nic.substring(0, 4);
                    dayOfYear = parseInt(nic.substring(4, 7));
                } else {
                    dobInput.value = "";
                    genderInput.value = "";
                    return;
                }
                
                if (dayOfYear > 500) {
                    gender = "Female";
                    dayOfYear -= 500;
                } else {
                    gender = "Male";
                }
                
                const date = new Date(year, 0, dayOfYear - 1);
                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, "0");
                const dd = String(date.getDate()).padStart(2, "0");
                
                dobInput.value = `${yyyy}-${mm}-${dd}`;
                genderInput.value = gender;
            });
        });
    </script>
</body>
</html>
