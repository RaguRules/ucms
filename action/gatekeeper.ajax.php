<?php
if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION["LOGIN_USERTYPE"])) {
    $system_usertype = $_SESSION["LOGIN_USERTYPE"];
    $system_username = $_SESSION["LOGIN_USERNAME"];
    
} else {
    $system_usertype = "GUEST";
    $system_username = "GUEST";
}

include_once('../lib/db.php');
include_once('../lib/security.php');
include_once('../lib/helper.php');

$helper = new Helper($conn);
$security = new Security();

$staff_id = $helper->getId($_SESSION["LOGIN_USERNAME"], $_SESSION["LOGIN_USERTYPE"]);

// Ensure it's an AJAX POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Check CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF token mismatch.']);
    exit;
}

// Validate inputs
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$reg_id = isset($_POST['reg_id']) ? trim($_POST['reg_id']) : '';

if (empty($action) || empty($reg_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input parameters.']);
    exit;
}

if (!preg_match('/^R[0-9]{8}$/', $reg_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid registration ID format.']);
    exit;
}

try {
    // Start a transaction
    mysqli_begin_transaction($conn);

    if ($action === 'approve') {
        // 1. Move user data from registration table to respective lawyer or police table
        $stmtFetch = $conn->prepare("SELECT * FROM registration WHERE reg_id = ?");
        $stmtFetch->bind_param("s", $reg_id);
        $stmtFetch->execute();
        $result = $stmtFetch->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('No registration found for this ID.');
        }

        $data = $result->fetch_assoc();
        $role_id = $data['role_id'];

        if ($role_id === 'R06') { // Lawyer
            $next_lawyer_id = $helper->generateNextLawyerID();

            $stmtInsert = $conn->prepare("INSERT INTO lawyer 
                (lawyer_id, first_name, last_name, mobile, email, address, nic_number, date_of_birth, enrolment_number, joined_date, is_active, role_id, station, image_path, gender, added_by, staff_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), '1', ?, ?, ?, ?, ?, ?)");
            
            $stmtInsert->bind_param(
                "sssisssssssssss",
                $next_lawyer_id,
                $data['first_name'],
                $data['last_name'],
                $data['mobile'],
                $data['email'],
                $data['address'],
                $data['nic_number'],
                $data['date_of_birth'],
                $data['enrolment_number'],
                $data['role_id'],
                $data['station'],
                $data['image_path'],
                $data['gender'],
                $system_usertype,
                $staff_id
            );
            $stmtInsert->execute();

        } elseif ($role_id === 'R07') { // Police
            $next_police_id = $helper->generateNextPoliceID($conn);

            $stmtInsert = $conn->prepare("INSERT INTO police 
                (police_id, first_name, last_name, mobile, email, address, nic_number, date_of_birth, badge_number, joined_date, is_active, role_id, station, image_path, gender, added_by, staff_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), '1', ?, ?, ?, ?, ?, ?)");
                
            $stmtInsert->bind_param(
                "sssisssssssssss",
                $next_police_id,
                $data['first_name'],
                $data['last_name'],
                $data['mobile'],
                $data['email'],
                $data['address'],
                $data['nic_number'],
                $data['date_of_birth'],
                $data['badge_number'],
                $data['role_id'],
                $data['station'],
                $data['image_path'],
                $data['gender'],
                $system_usertype,
                $staff_id
                
            );
            $stmtInsert->execute();
        } else {
            throw new Exception('Invalid user role.');
        }

        // 2. Update login status to active
        $stmtUpdateLogin = $conn->prepare("UPDATE login SET status='active' WHERE username=?");
        $stmtUpdateLogin->bind_param("s", $data['email']);
        $stmtUpdateLogin->execute();

        // 3. Delete from registration table
        $stmtDelete = $conn->prepare("DELETE FROM registration WHERE reg_id = ?");
        $stmtDelete->bind_param("s", $reg_id);
        $stmtDelete->execute();

        mysqli_commit($conn);

        echo json_encode(['success' => true, 'message' => 'User approved successfully.']);
        exit;
    } 
    elseif ($action === 'deny') {
        // 1. Fetch email first
        $stmtFetch = $conn->prepare("SELECT email FROM registration WHERE reg_id = ?");
        $stmtFetch->bind_param("s", $reg_id);
        $stmtFetch->execute();
        $result = $stmtFetch->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('No registration found for this ID.');
        }

        $data = $result->fetch_assoc();

        // 2. Set login status to deleted
        $stmtUpdateLogin = $conn->prepare("UPDATE login SET status='deleted' WHERE username=?");
        $stmtUpdateLogin->bind_param("s", $data['email']);
        $stmtUpdateLogin->execute();

        // 3. Delete from registration table
        $stmtDelete = $conn->prepare("DELETE FROM registration WHERE reg_id = ?");
        $stmtDelete->bind_param("s", $reg_id);
        $stmtDelete->execute();

        mysqli_commit($conn);

        echo json_encode(['success' => true, 'message' => 'User denied and removed successfully.']);
        exit;
    } 
    else {
        throw new Exception('Invalid action.');
    }

} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    mysqli_rollback($conn);

    // Get detailed error information
    $errorMessage = $e->getMessage();  // Exception message
    $errorCode = $e->getCode();  // Exception code (if available)
    $errorFile = $e->getFile();  // File where the exception was thrown
    $errorLine = $e->getLine();  // Line number where the exception was thrown

    // Log the error with detailed information
    error_log("Gatekeeper error: Message: $errorMessage | Code: $errorCode | File: $errorFile | Line: $errorLine");

    // Optionally, log the last executed query if needed
    if (isset($stmtInsert)) {
        error_log("Last executed query: " . $stmtInsert->sqlstate);
    }

    // Send HTTP 500 status code to the client
    http_response_code(500);

    // Send JSON response with a generic message for the client
   // Send detailed JSON response with error information
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error_details' => [
            'message' => $errorMessage,
            'code' => $errorCode,
            'file' => $errorFile,
            'line' => $errorLine
        ]
    ]);
    exit;
}

?>
