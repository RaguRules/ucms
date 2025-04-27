<?php
if (!isset($_SESSION)) {
    session_start();
}

include_once('../lib/db.php');
include_once('../lib/security.php');
include_once('../lib/helper.php');

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

if (!preg_match('/^R[0-9]{4}$/', $reg_id)) {
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
            $next_lawyer_id = generateNextLawyerID($conn);

            $stmtInsert = $conn->prepare("INSERT INTO lawyer 
                (lawyer_id, first_name, last_name, mobile, email, address, nic_number, date_of_birth, enrolment_number, joined_date, is_active, role_id, station, image_path, gender) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), '1', ?, ?, ?, ?)");
			
            $stmtInsert->bind_param(
                "sssisssdsssss",
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
                $data['gender']
            );
            $stmtInsert->execute();

        } elseif ($role_id === 'R07') { // Police
            $next_police_id = generateNextPoliceID($conn);

            $stmtInsert = $conn->prepare("INSERT INTO police 
                (police_id, first_name, last_name, mobile, email, address, nic_number, date_of_birth, badge_number, joined_date, is_active, role_id, station, image_path, gender) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), '1', ?, ?, ?, ?)");
				$stmtInsert->bind_param(
					"sssisssdsssss",
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
					$data['gender']
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
    mysqli_rollback($conn);
    logError('Gatekeeper error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred.']);
    exit;
}
?>
