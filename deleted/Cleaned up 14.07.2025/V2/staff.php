<?php
/**
 * Staff Management Module
 * 
 * This file handles all staff-related operations including viewing, adding, editing, and deleting staff members.
 * It uses the Database and Security classes for secure database operations and input validation.
 * 
 * @version 2.0
 * @author Courts Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once 'db.php';
require_once 'security.php';

// Initialize variables
$errors = [];
$formData = [];
$successMessage = '';

// Check user type from session
$system_usertype = $_SESSION["LOGIN_USERTYPE"] ?? "GUEST";
$system_username = $_SESSION["LOGIN_USERNAME"] ?? "";

/**
 * Generate the next staff ID
 * 
 * @param PDO $conn Database connection
 * @return string Next staff ID
 */
function generateNextStaffID($conn) {
    $db = Database::getInstance();
    $result = $db->getRow("SELECT MAX(staff_id) AS max_id FROM staff");
    
    if ($result && isset($result['max_id'])) {
        $max_id = $result['max_id'];
        
        if ($max_id === null) {
            // No staff IDs exist yet, start with S0001
            return "S0001";
        } else {
            // Extract the numeric part, increment, and format
            $numeric_part = (int)substr($max_id, 1); // Remove "S", convert to integer
            $next_numeric_part = $numeric_part + 1;
            // Pad with leading zeros to 4 digits, then prepend "S"
            return "S" . str_pad($next_numeric_part, 4, "0", STR_PAD_LEFT);
        }
    } else {
        // Handle errors (e.g., table doesn't exist)
        return "S0001"; // Or throw an exception, log an error, etc.
    }
}

/**
 * Get staff data from database
 * 
 * @param string $staff_id Staff ID
 * @return array|null Staff data or null if not found
 */
function getStaffDataFromDatabase($staff_id) {
    $db = Database::getInstance();
    return $db->getRow("SELECT * FROM staff WHERE staff_id = ?", [$staff_id]);
}

/**
 * Check for duplicate values in database
 * 
 * @param string $table Table name
 * @param string $column Column name
 * @param string $value Value to check
 * @return bool True if duplicate exists, false otherwise
 */
function checkDuplicate($table, $column, $value) {
    $db = Database::getInstance();
    $result = $db->getRow("SELECT $column FROM $table WHERE $column = ? LIMIT 1", [$value]);
    return ($result !== false);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (isset($_POST['csrf_token'])) {
        if (!Security::verifyCSRFToken($_POST['csrf_token'])) {
            $errors['csrf'] = "Invalid form submission";
        }
    } else {
        $errors['csrf'] = "CSRF token missing";
    }
    
    // Process add staff form
    if (isset($_POST["btn_add"]) && empty($errors)) {
        // Validate and sanitize input
        $txt_staff_id = $_POST["txt_staff_id"] ?? '';
        
        // Validate first name
        $firstNameResult = Security::validateInput($_POST["txt_first_name"] ?? '', 'name');
        if (!$firstNameResult['status']) {
            $errors['first_name'] = $firstNameResult['message'];
        } else {
            $formData['txt_first_name'] = $firstNameResult['value'];
        }
        
        // Validate last name
        $lastNameResult = Security::validateInput($_POST["txt_last_name"] ?? '', 'name');
        if (!$lastNameResult['status']) {
            $errors['last_name'] = $lastNameResult['message'];
        } else {
            $formData['txt_last_name'] = $lastNameResult['value'];
        }
        
        // Validate mobile
        $mobileResult = Security::validateInput($_POST["int_mobile"] ?? '', 'mobile');
        if (!$mobileResult['status']) {
            $errors['mobile'] = $mobileResult['message'];
        } else {
            $formData['int_mobile'] = $mobileResult['value'];
        }
        
        // Validate NIC
        $nicResult = Security::validateInput($_POST["txt_nic_number"] ?? '', 'nic');
        if (!$nicResult['status']) {
            $errors['nic'] = $nicResult['message'];
        } else {
            $formData['txt_nic_number'] = $nicResult['value'];
            
            // Check for duplicate NIC
            if (checkDuplicate('staff', 'nic_number', $formData['txt_nic_number'])) {
                $errors['nic'] = "This NIC number is already registered";
            }
        }
        
        // Validate date of birth
        if (empty($_POST["date_date_of_birth"])) {
            $errors['dob'] = "Date of birth is required";
        } else {
            $formData['date_date_of_birth'] = $_POST["date_date_of_birth"];
        }
        
        // Validate email
        $emailResult = Security::validateInput($_POST["txt_email"] ?? '', 'email');
        if (!$emailResult['status']) {
            $errors['email'] = $emailResult['message'];
        } else {
            $formData['txt_email'] = $emailResult['value'];
            
            // Check for duplicate email
            if (checkDuplicate('staff', 'email', $formData['txt_email'])) {
                $errors['email'] = "This email is already registered";
            }
        }
        
        // Validate address
        if (empty($_POST["txt_address"])) {
            $errors['address'] = "Address is required";
        } else {
            $formData['txt_address'] = htmlspecialchars($_POST["txt_address"], ENT_QUOTES, 'UTF-8');
        }
        
        // Validate court name
        if (empty($_POST["select_court_name"])) {
            $errors['court'] = "Court name is required";
        } else {
            $formData['select_court_name'] = $_POST["select_court_name"];
        }
        
        // Validate joined date
        if (empty($_POST["date_joined_date"])) {
            $errors['joined_date'] = "Joined date is required";
        } else {
            $formData['date_joined_date'] = $_POST["date_joined_date"];
        }
        
        // Validate role name
        if (empty($_POST["select_role_name"])) {
            $errors['role'] = "Role name is required";
        } else {
            $formData['select_role_name'] = $_POST["select_role_name"];
        }
        
        // Validate gender
        if (empty($_POST["select_gender"])) {
            $errors['gender'] = "Gender is required";
        } else {
            $formData['select_gender'] = $_POST["select_gender"];
        }
        
        // Validate appointment
        if (empty($_POST["select_appointment"])) {
            $errors['appointment'] = "Appointment type is required";
        } else {
            $formData['select_appointment'] = $_POST["select_appointment"];
        }
        
        // Process profile photo upload
        $uploadResult = Security::secureImageUpload('img_profile_photo');
        if (!$uploadResult['success']) {
            $errors['profile_photo'] = $uploadResult['error'];
        } else {
            $formData['txt_image_path'] = 'uploads/' . $uploadResult['filename'];
        }
        
        // If no errors, insert data into database
        if (empty($errors)) {
            $db = Database::getInstance();
            $formData['txt_staff_id'] = $txt_staff_id;
            $formData['status'] = 'active';
            
            // Begin transaction
            $db->beginTransaction();
            
            try {
                // Insert staff data
                $staffData = [
                    'staff_id' => $formData['txt_staff_id'],
                    'first_name' => $formData['txt_first_name'],
                    'last_name' => $formData['txt_last_name'],
                    'mobile' => $formData['int_mobile'],
                    'nic_number' => $formData['txt_nic_number'],
                    'date_of_birth' => $formData['date_date_of_birth'],
                    'email' => $formData['txt_email'],
                    'address' => $formData['txt_address'],
                    'court_id' => $formData['select_court_name'],
                    'joined_date' => $formData['date_joined_date'],
                    'role_id' => $formData['select_role_name'],
                    'image_path' => $formData['txt_image_path'],
                    'gender' => $formData['select_gender'],
                    'appointment' => $formData['select_appointment'],
                    'is_active' => 1
                ];
                
                $staffInsertResult = $db->insert('staff', $staffData);
                
                if ($staffInsertResult) {
                    // Create login credentials
                    $hashedPassword = Security::hashPassword($formData['txt_nic_number']);
                    
                    $loginData = [
                        'username' => $formData['txt_email'],
                        'password' => $hashedPassword,
                        'otp' => '1329', // This should be generated randomly in a real system
                        'status' => 'active',
                        'role_id' => $formData['select_role_name']
                    ];
                    
                    $loginInsertResult = $db->insert('login', $loginData);
                    
                    if ($loginInsertResult) {
                        // Commit transaction
                        $db->commit();
                        $successMessage = "Staff member added successfully";
                        
                        // Redirect to view page
                        header("Location: index.php?pg=staff.php&option=view");
                        exit;
                    } else {
                        throw new Exception("Failed to create login credentials");
                    }
                } else {
                    throw new Exception("Failed to add staff member");
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                $db->rollback();
                $errors['database'] = "Database error: " . $e->getMessage();
            }
        }
    }
    
    // Process update staff form
    if (isset($_POST["btn_update"]) && empty($errors)) {
        // Similar validation as add staff, but with update logic
        $txt_staff_id = $_POST["txt_staff_id"] ?? '';
        
        // Validate first name
        $firstNameResult = Security::validateInput($_POST["txt_first_name"] ?? '', 'name');
        if (!$firstNameResult['status']) {
            $errors['first_name'] = $firstNameResult['message'];
        } else {
            $formData['txt_first_name'] = $firstNameResult['value'];
        }
        
        // Validate last name
        $lastNameResult = Security::validateInput($_POST["txt_last_name"] ?? '', 'name');
        if (!$lastNameResult['status']) {
            $errors['last_name'] = $lastNameResult['message'];
        } else {
            $formData['txt_last_name'] = $lastNameResult['value'];
        }
        
        // Validate mobile
        $mobileResult = Security::validateInput($_POST["int_mobile"] ?? '', 'mobile');
        if (!$mobileResult['status']) {
            $errors['mobile'] = $mobileResult['message'];
        } else {
            $formData['int_mobile'] = $mobileResult['value'];
        }
        
        // Validate NIC
        $nicResult = Security::validateInput($_POST["txt_nic_number"] ?? '', 'nic');
        if (!$nicResult['status']) {
            $errors['nic'] = $nicResult['message'];
        } else {
            $formData['txt_nic_number'] = $nicResult['value'];
        }
        
        // Validate date of birth
        if (empty($_POST["date_date_of_birth"])) {
            $errors['dob'] = "Date of birth is required";
        } else {
            $formData['date_date_of_birth'] = $_POST["date_date_of_birth"];
        }
        
        // Validate email
        $emailResult = Security::validateInput($_POST["txt_email"] ?? '', 'email');
        if (!$emailResult['status']) {
            $errors['email'] = $emailResult['message'];
        } else {
            $formData['txt_email'] = $emailResult['value'];
        }
        
        // Validate address
        if (empty($_POST["txt_address"])) {
            $errors['address'] = "Address is required";
        } else {
            $formData['txt_address'] = htmlspecialchars($_POST["txt_address"], ENT_QUOTES, 'UTF-8');
        }
        
        // Validate court name
        if (empty($_POST["select_court_name"])) {
            $errors['court'] = "Court name is required";
        } else {
            $formData['select_court_name'] = $_POST["select_court_name"];
        }
        
        // Validate role name
        if (empty($_POST["select_role_name"])) {
            $errors['role'] = "Role name is required";
        } else {
            $formData['select_role_name'] = $_POST["select_role_name"];
        }
        
        // Validate gender
        if (empty($_POST["select_gender"])) {
            $errors['gender'] = "Gender is required";
        } else {
            $formData['select_gender'] = $_POST["select_gender"];
        }
        
        // Validate appointment
        if (empty($_POST["select_appointment"])) {
            $errors['appointment'] = "Appointment type is required";
        } else {
            $formData['select_appointment'] = $_POST["select_appointment"];
        }
        
        // Process profile photo upload
        $uploadResult = Security::secureImageUpload('img_profile_photo');
        if (!$uploadResult['success']) {
            $errors['profile_photo'] = $uploadResult['error'];
        } else {
            $formData['txt_image_path'] = 'uploads/' . $uploadResult['filename'];
        }
        
        // If no errors, update data in database
        if (empty($errors)) {
            $db = Database::getInstance();
            
            // Update staff data
            $staffData = [
                'first_name' => $formData['txt_first_name'],
                'last_name' => $formData['txt_last_name'],
                'mobile' => $formData['int_mobile'],
                'nic_number' => $formData['txt_nic_number'],
                'date_of_birth' => $formData['date_date_of_birth'],
                'email' => $formData['txt_email'],
                'address' => $formData['txt_address'],
                'court_id' => $formData['select_court_name'],
                'role_id' => $formData['select_role_name'],
                'image_path' => $formData['txt_image_path'],
                'gender' => $formData['select_gender'],
                'appointment' => $formData['select_appointment']
            ];
            
            $updateResult = $db->update('staff', $staffData, 'staff_id = ?', [$txt_staff_id]);
            
            if ($updateResult) {
                $successMessage = "Staff member updated successfully";
                
                // Redirect to view page
                header("Location: index.php?pg=staff.php&option=view");
                exit;
            } else {
                $errors['database'] = "Failed to update staff member";
            }
        }
    }
    
    // Process delete staff form
    if (isset($_POST["btn_delete"]) && empty($errors)) {
        $staff_id = $_POST['staff_id'] ?? '';
        
        if (empty($staff_id)) {
            $errors['staff_id'] = "Staff ID is required";
        } else {
            $db = Database::getInstance();
            
            // Begin transaction
            $db->beginTransaction();
            
            try {
                // Get email from staff table
                $staffData = $db->getRow("SELECT email FROM staff WHERE staff_id = ?", [$staff_id]);
                
                if ($staffData && isset($staffData['email'])) {
                    $email = $staffData['email'];
                    
                    // Update login status
                    $loginUpdateResult = $db->update('login', ['status' => 'deleted'], 'username = ?', [$email]);
                    
                    // Update staff status
                    $staffUpdateResult = $db->update('staff', ['is_active' => 0], 'staff_id = ?', [$staff_id]);
                    
                    if ($loginUpdateResult && $staffUpdateResult) {
                        // Commit transaction
                        $db->commit();
                        $successMessage = "Staff member deleted successfully";
                        
                        // Redirect to view page
                        header("Location: index.php?pg=staff.php&option=view");
                        exit;
                    } else {
                        throw new Exception("Failed to delete staff member");
                    }
                } else {
                    throw new Exception("Staff member not found");
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                $db->rollback();
                $errors['database'] = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Generate CSRF token
$csrf_token = Security::generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php
    // Display error messages
    if (!empty($errors)) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4" role="alert">';
        echo '<strong class="font-bold">Error!</strong>';
        echo '<span class="block sm:inline"> Please fix the following issues:</span>';
        echo '<ul class="list-disc ml-5">';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
    
    // Display success message
    if (!empty($successMessage)) {
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4" role="alert">';
        echo '<strong class="font-bold">Success!</strong>';
        echo '<span class="block sm:inline"> ' . $successMessage . '</span>';
        echo '</div>';
    }
    
    // VIEW Section
    if (isset($_GET['option']) && $_GET['option'] == "view") {
        $db = Database::getInstance();
        $staffList = $db->getRows("SELECT staff_id, first_name, last_name, nic_number, mobile, court_id, email FROM staff WHERE is_active = 1");
    ?>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Staff Management</h1>
            <a href="index.php?pg=staff.php&option=add" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors">
                <i class="fas fa-plus mr-2"></i> Add Staff
            </a>
        </div>
        
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIC</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Court Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        $count = 1;
                        if ($staffList) {
                            foreach ($staffList as $row) { 
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $count; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['nic_number']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['mobile']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo getCourtName($row['court_id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="index.php?pg=staff.php&option=edit" class="inline">
                                        <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($row['staff_id']); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" name="btn_edit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-1 px-2 rounded focus:outline-none focus:shadow-outline transition-colors">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </button>
                                    </form>
                                    
                                    <form method="GET" action="index.php" class="inline">
                                        <input type="hidden" name="pg" value="staff.php">
                                        <input type="hidden" name="option" value="full_view">
                                        <input type="hidden" name="id" value="<?php echo urlencode(htmlspecialchars($row['staff_id'])); ?>">
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-1 px-2 rounded focus:outline-none focus:shadow-outline transition-colors">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="#" class="inline delete-form">
                                        <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($row['staff_id']); ?>">
                                        <input type="hidden" name="btn_delete" value="1">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="button" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-1 px-2 rounded focus:outline-none focus:shadow-outline transition-colors" onclick="showDeleteModal(this)">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php 
                                $count++;
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No staff members found</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php
    // FULL VIEW SECTION
    } elseif (isset($_GET['option']) && $_GET['option'] == "full_view" && isset($_GET['id'])) {
        $staff_id = htmlspecialchars($_GET['id']);
        $staffData = getStaffDataFromDatabase($staff_id);
        
        if ($staffData) {
    ?>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4 flex items-center justify-between">
                <h1 class="text-xl font-bold"><i class="fas fa-user-tie mr-2"></i>Staff Profile</h1>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Profile Photo -->
                    <div class="md:col-span-1">
                        <p class="text-gray-600 font-semibold mb-2">Profile Photo</p>
                        <img src="<?php echo htmlspecialchars($staffData['image_path']); ?>" alt="Profile Photo" class="w-full h-auto max-w-xs mx-auto rounded-lg border-4 border-blue-500 shadow-md">
                    </div>
                    
                    <!-- Staff Information -->
                    <div class="md:col-span-2">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 w-1/3">Staff ID</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo ltrim(substr($staffData['staff_id'], 1), '0'); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">First Name</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($staffData['first_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Last Name</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($staffData['last_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Mobile Number</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($staffData['mobile']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">NIC Number</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($staffData['nic_number']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Date of Birth</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($staffData['date_of_birth']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Email</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($staffData['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Gender</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($staffData['gender']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Staff Type</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($staffData['appointment']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Address</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($staffData['address']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Court</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo getCourtName($staffData['court_id']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Joined Date</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($staffData['joined_date']); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Status</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php if ($staffData['is_active']) { ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            <?php } else { ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Role</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo getRoleName($staffData['role_id']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation and Action Buttons -->
                <div class="mt-8 flex flex-wrap justify-between items-center gap-4">
                    <?php
                    // Calculate previous staff ID
                    $number = (int) filter_var($staffData['staff_id'], FILTER_SANITIZE_NUMBER_INT);
                    $prev_number = $number - 1;
                    $previous_staff_id = 'S' . str_pad($prev_number, 4, '0', STR_PAD_LEFT);
                    $prev_exists = getStaffDataFromDatabase($previous_staff_id);
                    
                    // Calculate next staff ID
                    $next_number = $number + 1;
                    $next_staff_id = 'S' . str_pad($next_number, 4, '0', STR_PAD_LEFT);
                    $next_exists = getStaffDataFromDatabase($next_staff_id);
                    ?>
                    
                    <!-- Previous Button -->
                    <?php if ($prev_exists) { ?>
                        <a href="index.php?pg=staff.php&option=full_view&id=<?php echo $previous_staff_id; ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i> Previous
                        </a>
                    <?php } else { ?>
                        <button disabled class="bg-gray-200 text-gray-400 font-bold py-2 px-4 rounded inline-flex items-center cursor-not-allowed">
                            <i class="fas fa-arrow-left mr-2"></i> Previous
                        </button>
                    <?php } ?>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="index.php?pg=staff.php&option=edit" class="inline">
                            <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($staffData['staff_id']); ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <button type="submit" name="btn_edit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center transition-colors">
                                <i class="fas fa-edit mr-2"></i> Edit
                            </button>
                        </form>
                        
                        <form method="POST" action="#" class="inline delete-form">
                            <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($staffData['staff_id']); ?>">
                            <input type="hidden" name="btn_delete" value="1">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <button type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded inline-flex items-center transition-colors" onclick="showDeleteModal(this)">
                                <i class="fas fa-trash-alt mr-2"></i> Delete
                            </button>
                        </form>
                        
                        <a href="index.php?pg=staff.php&option=view" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center transition-colors">
                            <i class="fas fa-times mr-2"></i> Close
                        </a>
                    </div>
                    
                    <!-- Next Button -->
                    <?php if ($next_exists) { ?>
                        <a href="index.php?pg=staff.php&option=full_view&id=<?php echo $next_staff_id; ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center transition-colors">
                            Next <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    <?php } else { ?>
                        <button disabled class="bg-gray-200 text-gray-400 font-bold py-2 px-4 rounded inline-flex items-center cursor-not-allowed">
                            Next <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php
        } else {
            echo '<div class="container mx-auto px-4 py-8">';
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
            echo '<strong class="font-bold">Error!</strong>';
            echo '<span class="block sm:inline"> Staff member not found.</span>';
            echo '</div>';
            echo '<div class="mt-4">';
            echo '<a href="index.php?pg=staff.php&option=view" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center transition-colors">';
            echo '<i class="fas fa-arrow-left mr-2"></i> Back to Staff List';
            echo '</a>';
            echo '</div>';
            echo '</div>';
        }
    }
    
    // ADD SECTION
    elseif (isset($_GET['option']) && $_GET['option'] == "add") {
        $next_staff_id = generateNextStaffID($conn);
    ?>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-blue-600 text-white text-center py-4 rounded-t-lg">
            <h1 class="text-2xl font-bold">Add New Staff</h1>
        </div>
        
        <div class="bg-white shadow-md rounded-b-lg p-6">
            <form action="#" method="POST" id="staffForm" name="staffForm" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="txt_staff_id" value="<?php echo htmlspecialchars($next_staff_id); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Information -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-800 border-b pb-2">Personal Information</h2>
                        
                        <div class="form-group">
                            <label for="txt_first_name" class="form-label">First Name</label>
                            <input type="text" class="form-input" id="txt_first_name" name="txt_first_name" value="<?php echo $formData['txt_first_name'] ?? ''; ?>" required>
                            <?php if (isset($errors['first_name'])) { ?>
                                <p class="error-text"><?php echo $errors['first_name']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="txt_last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-input" id="txt_last_name" name="txt_last_name" value="<?php echo $formData['txt_last_name'] ?? ''; ?>" required>
                            <?php if (isset($errors['last_name'])) { ?>
                                <p class="error-text"><?php echo $errors['last_name']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="txt_nic_number" class="form-label">NIC Number</label>
                            <input type="text" class="form-input" id="txt_nic_number" name="txt_nic_number" value="<?php echo $formData['txt_nic_number'] ?? ''; ?>" required>
                            <?php if (isset($errors['nic'])) { ?>
                                <p class="error-text"><?php echo $errors['nic']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-input" id="date_date_of_birth" name="date_date_of_birth" value="<?php echo $formData['date_date_of_birth'] ?? ''; ?>" required>
                            <?php if (isset($errors['dob'])) { ?>
                                <p class="error-text"><?php echo $errors['dob']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="select_gender" class="form-label">Gender</label>
                            <select class="form-input" id="select_gender" name="select_gender" required>
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male" <?php echo (isset($formData['select_gender']) && $formData['select_gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (isset($formData['select_gender']) && $formData['select_gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo (isset($formData['select_gender']) && $formData['select_gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <?php if (isset($errors['gender'])) { ?>
                                <p class="error-text"><?php echo $errors['gender']; ?></p>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-800 border-b pb-2">Contact Information</h2>
                        
                        <div class="form-group">
                            <label for="int_mobile" class="form-label">Mobile Number</label>
                            <input type="text" class="form-input" id="int_mobile" name="int_mobile" value="<?php echo $formData['int_mobile'] ?? ''; ?>" required>
                            <?php if (isset($errors['mobile'])) { ?>
                                <p class="error-text"><?php echo $errors['mobile']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="txt_email" class="form-label">Email</label>
                            <input type="email" class="form-input" id="txt_email" name="txt_email" value="<?php echo $formData['txt_email'] ?? ''; ?>" required>
                            <?php if (isset($errors['email'])) { ?>
                                <p class="error-text"><?php echo $errors['email']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="txt_address" class="form-label">Address</label>
                            <input type="text" class="form-input" id="txt_address" name="txt_address" value="<?php echo $formData['txt_address'] ?? ''; ?>" required>
                            <?php if (isset($errors['address'])) { ?>
                                <p class="error-text"><?php echo $errors['address']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="img_profile_photo" class="form-label">Profile Photo</label>
                            <input type="file" class="form-input" id="img_profile_photo" name="img_profile_photo" accept="image/*" required>
                            <?php if (isset($errors['profile_photo'])) { ?>
                                <p class="error-text"><?php echo $errors['profile_photo']; ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Professional Information -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-800 border-b pb-2">Professional Information</h2>
                        
                        <div class="form-group">
                            <label for="select_court_name" class="form-label">Court Name</label>
                            <select class="form-input" id="select_court_name" name="select_court_name" required>
                                <option value="" disabled selected>Select Court</option>
                                <option value="C01" <?php echo (isset($formData['select_court_name']) && $formData['select_court_name'] == 'C01') ? 'selected' : ''; ?>>Magistrate's Court</option>
                                <option value="C02" <?php echo (isset($formData['select_court_name']) && $formData['select_court_name'] == 'C02') ? 'selected' : ''; ?>>District Court</option>
                                <option value="C03" <?php echo (isset($formData['select_court_name']) && $formData['select_court_name'] == 'C03') ? 'selected' : ''; ?>>High Court</option>
                            </select>
                            <?php if (isset($errors['court'])) { ?>
                                <p class="error-text"><?php echo $errors['court']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="select_role_name" class="form-label">Role Name</label>
                            <select class="form-input" id="select_role_name" name="select_role_name" required>
                                <option value="" disabled selected>Select Role</option>
                                <option value="R01" <?php echo (isset($formData['select_role_name']) && $formData['select_role_name'] == 'R01') ? 'selected' : ''; ?>>Administrator</option>
                                <option value="R02" <?php echo (isset($formData['select_role_name']) && $formData['select_role_name'] == 'R02') ? 'selected' : ''; ?>>Hon. Judge</option>
                                <option value="R03" <?php echo (isset($formData['select_role_name']) && $formData['select_role_name'] == 'R03') ? 'selected' : ''; ?>>The Registrar</option>
                                <option value="R04" <?php echo (isset($formData['select_role_name']) && $formData['select_role_name'] == 'R04') ? 'selected' : ''; ?>>Interpreter</option>
                                <option value="R05" <?php echo (isset($formData['select_role_name']) && $formData['select_role_name'] == 'R05') ? 'selected' : ''; ?>>Other Staff</option>
                                <option value="R06" <?php echo (isset($formData['select_role_name']) && $formData['select_role_name'] == 'R06') ? 'selected' : ''; ?>>Lawyer</option>
                                <option value="R07" <?php echo (isset($formData['select_role_name']) && $formData['select_role_name'] == 'R07') ? 'selected' : ''; ?>>Police</option>
                            </select>
                            <?php if (isset($errors['role'])) { ?>
                                <p class="error-text"><?php echo $errors['role']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="select_appointment" class="form-label">Officer Classification</label>
                            <select class="form-input" id="select_appointment" name="select_appointment" required>
                                <option value="" disabled selected>Select type of appointment</option>
                                <option value="Judicial Staff (JSC)" <?php echo (isset($formData['select_appointment']) && $formData['select_appointment'] == 'Judicial Staff (JSC)') ? 'selected' : ''; ?>>Judicial Staff (JSC)</option>
                                <option value="Ministry Staff" <?php echo (isset($formData['select_appointment']) && $formData['select_appointment'] == 'Ministry Staff') ? 'selected' : ''; ?>>Ministry Staff</option>
                                <option value="O.E.S/ Peon/ Security" <?php echo (isset($formData['select_appointment']) && $formData['select_appointment'] == 'O.E.S/ Peon/ Security') ? 'selected' : ''; ?>>O.E.S/ Peon/ Security</option>
                            </select>
                            <?php if (isset($errors['appointment'])) { ?>
                                <p class="error-text"><?php echo $errors['appointment']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <input type="hidden" id="date_joined_date" name="date_joined_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <!-- Account Information -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-800 border-b pb-2">Account Information</h2>
                        
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        A user account will be created automatically with the email as username and NIC number as the initial password.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-shield-alt text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        The user should be instructed to change their password after first login for security purposes.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-between mt-8">
                    <button type="button" id="btn_clear" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline transition-colors">
                        <i class="fas fa-eraser mr-2"></i> Clear Form
                    </button>
                    
                    <button type="submit" id="btn_add" name="btn_add" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline transition-colors">
                        <i class="fas fa-save mr-2"></i> Save Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php
    // EDIT SECTION
    } elseif (isset($_GET['option']) && $_GET['option'] == "edit" && isset($_POST['staff_id'])) {
        $staff_id = htmlspecialchars($_POST['staff_id']);
        $staffData = getStaffDataFromDatabase($staff_id);
        
        if ($staffData) {
    ?>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-blue-600 text-white text-center py-4 rounded-t-lg">
            <h1 class="text-2xl font-bold">Edit Staff</h1>
        </div>
        
        <div class="bg-white shadow-md rounded-b-lg p-6">
            <form action="#" method="POST" id="staffForm" name="staffForm" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="txt_staff_id" value="<?php echo $staff_id; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Information -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-800 border-b pb-2">Personal Information</h2>
                        
                        <div class="form-group">
                            <label for="txt_first_name" class="form-label">First Name</label>
                            <input type="text" class="form-input" id="txt_first_name" name="txt_first_name" value="<?php echo htmlspecialchars($staffData['first_name']); ?>" required>
                            <?php if (isset($errors['first_name'])) { ?>
                                <p class="error-text"><?php echo $errors['first_name']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="txt_last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-input" id="txt_last_name" name="txt_last_name" value="<?php echo htmlspecialchars($staffData['last_name']); ?>" required>
                            <?php if (isset($errors['last_name'])) { ?>
                                <p class="error-text"><?php echo $errors['last_name']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="txt_nic_number" class="form-label">NIC Number</label>
                            <input type="text" class="form-input" id="txt_nic_number" name="txt_nic_number" value="<?php echo htmlspecialchars($staffData['nic_number']); ?>" required>
                            <?php if (isset($errors['nic'])) { ?>
                                <p class="error-text"><?php echo $errors['nic']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-input" id="date_date_of_birth" name="date_date_of_birth" value="<?php echo htmlspecialchars($staffData['date_of_birth']); ?>" required>
                            <?php if (isset($errors['dob'])) { ?>
                                <p class="error-text"><?php echo $errors['dob']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="select_gender" class="form-label">Gender</label>
                            <select class="form-input" id="select_gender" name="select_gender" required>
                                <option value="" disabled>Select Gender</option>
                                <option value="Male" <?php echo ($staffData['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($staffData['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($staffData['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <?php if (isset($errors['gender'])) { ?>
                                <p class="error-text"><?php echo $errors['gender']; ?></p>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-800 border-b pb-2">Contact Information</h2>
                        
                        <div class="form-group">
                            <label for="int_mobile" class="form-label">Mobile Number</label>
                            <input type="text" class="form-input" id="int_mobile" name="int_mobile" value="<?php echo htmlspecialchars($staffData['mobile']); ?>" required>
                            <?php if (isset($errors['mobile'])) { ?>
                                <p class="error-text"><?php echo $errors['mobile']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="txt_email" class="form-label">Email</label>
                            <input type="email" class="form-input" id="txt_email" name="txt_email" value="<?php echo htmlspecialchars($staffData['email']); ?>" required>
                            <?php if (isset($errors['email'])) { ?>
                                <p class="error-text"><?php echo $errors['email']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="txt_address" class="form-label">Address</label>
                            <input type="text" class="form-input" id="txt_address" name="txt_address" value="<?php echo htmlspecialchars($staffData['address']); ?>" required>
                            <?php if (isset($errors['address'])) { ?>
                                <p class="error-text"><?php echo $errors['address']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="img_profile_photo" class="form-label">Profile Photo</label>
                            <div class="mb-2">
                                <img src="<?php echo htmlspecialchars($staffData['image_path']); ?>" alt="Current Profile Photo" class="w-32 h-32 object-cover rounded-md border border-gray-300">
                            </div>
                            <input type="file" class="form-input" id="img_profile_photo" name="img_profile_photo" accept="image/*" required>
                            <?php if (isset($errors['profile_photo'])) { ?>
                                <p class="error-text"><?php echo $errors['profile_photo']; ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Professional Information -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-800 border-b pb-2">Professional Information</h2>
                        
                        <div class="form-group">
                            <label for="select_court_name" class="form-label">Court Name</label>
                            <select class="form-input" id="select_court_name" name="select_court_name" required>
                                <option value="" disabled>Select Court</option>
                                <option value="C01" <?php echo ($staffData['court_id'] == 'C01') ? 'selected' : ''; ?>>Magistrate's Court</option>
                                <option value="C02" <?php echo ($staffData['court_id'] == 'C02') ? 'selected' : ''; ?>>District Court</option>
                                <option value="C03" <?php echo ($staffData['court_id'] == 'C03') ? 'selected' : ''; ?>>High Court</option>
                            </select>
                            <?php if (isset($errors['court'])) { ?>
                                <p class="error-text"><?php echo $errors['court']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="select_role_name" class="form-label">Role Name</label>
                            <select class="form-input" id="select_role_name" name="select_role_name" required>
                                <option value="" disabled>Select Role</option>
                                <option value="R01" <?php echo ($staffData['role_id'] == 'R01') ? 'selected' : ''; ?>>Administrator</option>
                                <option value="R02" <?php echo ($staffData['role_id'] == 'R02') ? 'selected' : ''; ?>>Hon. Judge</option>
                                <option value="R03" <?php echo ($staffData['role_id'] == 'R03') ? 'selected' : ''; ?>>The Registrar</option>
                                <option value="R04" <?php echo ($staffData['role_id'] == 'R04') ? 'selected' : ''; ?>>Interpreter</option>
                                <option value="R05" <?php echo ($staffData['role_id'] == 'R05') ? 'selected' : ''; ?>>Other Staff</option>
                                <option value="R06" <?php echo ($staffData['role_id'] == 'R06') ? 'selected' : ''; ?>>Lawyer</option>
                                <option value="R07" <?php echo ($staffData['role_id'] == 'R07') ? 'selected' : ''; ?>>Police</option>
                            </select>
                            <?php if (isset($errors['role'])) { ?>
                                <p class="error-text"><?php echo $errors['role']; ?></p>
                            <?php } ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="select_appointment" class="form-label">Officer Classification</label>
                            <select class="form-input" id="select_appointment" name="select_appointment" required>
                                <option value="" disabled>Select type of appointment</option>
                                <option value="Judicial Staff (JSC)" <?php echo ($staffData['appointment'] == 'Judicial Staff (JSC)') ? 'selected' : ''; ?>>Judicial Staff (JSC)</option>
                                <option value="Ministry Staff" <?php echo ($staffData['appointment'] == 'Ministry Staff') ? 'selected' : ''; ?>>Ministry Staff</option>
                                <option value="O.E.S/ Peon/ Security" <?php echo ($staffData['appointment'] == 'O.E.S/ Peon/ Security') ? 'selected' : ''; ?>>O.E.S/ Peon/ Security</option>
                            </select>
                            <?php if (isset($errors['appointment'])) { ?>
                                <p class="error-text"><?php echo $errors['appointment']; ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-between mt-8">
                    <a href="index.php?pg=staff.php&option=view" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Cancel
                    </a>
                    
                    <button type="submit" id="btn_update" name="btn_update" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline transition-colors">
                        <i class="fas fa-save mr-2"></i> Update Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php
        } else {
            echo '<div class="container mx-auto px-4 py-8">';
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
            echo '<strong class="font-bold">Error!</strong>';
            echo '<span class="block sm:inline"> Staff member not found.</span>';
            echo '</div>';
            echo '<div class="mt-4">';
            echo '<a href="index.php?pg=staff.php&option=view" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center transition-colors">';
            echo '<i class="fas fa-arrow-left mr-2"></i> Back to Staff List';
            echo '</a>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        // Redirect to view page if no valid option is provided
        header("Location: index.php?pg=staff.php&option=view");
        exit;
    }
    ?>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm mx-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-6">
                <p class="text-gray-700">Are you sure you want to delete this staff member? This action cannot be undone.</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors" onclick="closeDeleteModal()">
                    Cancel
                </button>
                <button type="button" id="confirmDeleteBtn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors">
                    Delete
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Clear form button
        document.addEventListener('DOMContentLoaded', function() {
            const clearBtn = document.getElementById('btn_clear');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    document.getElementById('staffForm').reset();
                });
            }
            
            // NIC number auto-fill for date of birth and gender
            const nicInput = document.getElementById('txt_nic_number');
            const dobInput = document.getElementById('date_date_of_birth');
            const genderInput = document.getElementById('select_gender');
            
            if (nicInput && dobInput && genderInput) {
                nicInput.addEventListener('input', function() {
                    const nic = nicInput.value.trim();
                    let year = '';
                    let dayOfYear = '';
                    let gender = '';
                    
                    if (nic.length === 10 && /^[0-9]{9}[VvXx]$/.test(nic)) {
                        year = '19' + nic.substring(0, 2);
                        dayOfYear = parseInt(nic.substring(2, 5));
                    } else if (nic.length === 12 && /^[0-9]{12}$/.test(nic)) {
                        year = nic.substring(0, 4);
                        dayOfYear = parseInt(nic.substring(4, 7));
                    } else {
                        return;
                    }
                    
                    if (dayOfYear > 500) {
                        gender = 'Female';
                        dayOfYear -= 500;
                    } else {
                        gender = 'Male';
                    }
                    
                    const date = new Date(year, 0, dayOfYear);
                    const yyyy = date.getFullYear();
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const dd = String(date.getDate()).padStart(2, '0');
                    
                    dobInput.value = `${yyyy}-${mm}-${dd}`;
                    
                    // Set gender dropdown value
                    for (let i = 0; i < genderInput.options.length; i++) {
                        if (genderInput.options[i].value === gender) {
                            genderInput.selectedIndex = i;
                            break;
                        }
                    }
                });
            }
        });
        
        // Delete confirmation modal
        let currentDeleteForm = null;
        
        function showDeleteModal(button) {
            currentDeleteForm = button.closest('form');
            document.getElementById('deleteModal').classList.remove('hidden');
            
            // Set up the confirm button
            document.getElementById('confirmDeleteBtn').onclick = function() {
                if (currentDeleteForm) {
                    currentDeleteForm.submit();
                }
                closeDeleteModal();
            };
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            currentDeleteForm = null;
        }
    </script>
</body>
</html>
