<?php
	include "db.php"; // your DB connection
	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);
	
	
	
	function generateNextLawyerID($conn) {
		// 1. Find the highest existing staff_id
		$sql = "SELECT MAX(lawyer_id) AS max_id FROM lawyer";
		$result = mysqli_query($conn, $sql);
	    
	
		if ($result && mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$max_id = $row['max_id'];
	
			if ($max_id === null) {
				// No staff IDs exist yet, start with L0001
				return "L0001";
			} else {
				// Extract the numeric part, increment, and format
				$numeric_part = (int)substr($max_id, 1); // Remove "L", convert to integer
				$next_numeric_part = $numeric_part + 1;
				// Pad with leading zeros to 4 digits, then prepend "L"
				return "L" . str_pad($next_numeric_part, 4, "0", STR_PAD_LEFT);
			}
		} else {
			// Handle errors (e.g., table doesn't exist)
			return "L0001"; // Or throw an exception, log an error, etc.
		}
	}
	
	
	
	
	function generateNextPoliceID($conn) {
		// 1. Find the highest existing staff_id
	$sql = "SELECT MAX(police_id) AS max_id FROM police";
		$result = mysqli_query($conn, $sql);
	
		if ($result && mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$max_id = $row['max_id'];
	
			if ($max_id === null) {
				// No staff IDs exist yet, start with P0001
				return "P0001";
			} else {
				// Extract the numeric part, increment, and format
				$numeric_part = (int)substr($max_id, 1); // Remove "S", convert to integer
				$next_numeric_part = $numeric_part + 1;
				// Pad with leading zeros to 4 digits, then prepend "S"
				return "P" . str_pad($next_numeric_part, 4, "0", STR_PAD_LEFT);
			}
		} else {
			// Handle errors (e.g., table doesn't exist)
			return "P0001"; // Or throw an exception, log an error, etc.
		}
	}
	
	
	
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	    $reg_id = $_POST['reg_id'];
	    $action = $_POST['action'];
	
	    if ($action === 'approve') {
	        $result = mysqli_query($conn, "SELECT * FROM registration WHERE reg_id = '$reg_id'");
	        $data = mysqli_fetch_assoc($result);
	
	        if (!$data) {
	            echo json_encode(['success' => false, 'message' => 'Registration data not found.']);
	            exit;
	        }
	
	        $insert = false; // default
	
	        if ($data['role_id'] === 'R06') { 
	            
	            $next_lawyer_id = generateNextLawyerID($conn);
	
	            $insert = mysqli_query($conn, "INSERT INTO lawyer (lawyer_id, first_name, last_name, mobile, email, address, nic_number, date_of_birth, enrolment_number, joined_date, role_id, station, image_path) VALUES (
	            '$next_lawyer_id',
	            '{$data['first_name']}',
	            '{$data['last_name']}',
	            '{$data['mobile']}',
	            '{$data['email']}',
	            '{$data['address']}',
	            '{$data['nic_number']}',
	            '{$data['date_of_birth']}',
	            '{$data['enrolment_number']}',
	            NOW(),
	            '{$data['role_id']}',
	            '{$data['station']}',
	            '{$data['image_path']}'
	        )");
	        } 
	
	        if ($data['role_id'] === 'R07') { 
	
	            $next_police_id = generateNextPoliceID($conn);
	            
	            $insert = mysqli_query($conn, "INSERT INTO police (police_id, first_name, last_name, mobile, email, address, nic_number, date_of_birth, badge_number, joined_date, role_id, station, image_path) VALUES (
	            '$next_police_id',
	            '{$data['first_name']}',
	            '{$data['last_name']}',
	            '{$data['mobile']}',
	            '{$data['email']}',
	            '{$data['address']}',
	            '{$data['nic_number']}',
	            '{$data['date_of_birth']}',
	            '{$data['badge_number']}',
	            NOW(),
	            '{$data['role_id']}',
	            '{$data['station']}',
	            '{$data['image_path']}'
	        )");
	        }
	
	        // Also insert into login
	        // if ($insert) {
	        //     $insert_login = mysqli_query($conn, "INSERT INTO login (username, password, role_id, status) VALUES (
	        //         '{$data['email']}',
	        //         '{$data['password']}',
	        //         '{$data['role_id']}',
	        //         'active'
	        //     )");
	        // }
	        if($insert){
	            $update_login = mysqli_query($conn, "UPDATE login SET status = 'active' WHERE username = '{$data['email']}'");
	
	        }
	
	        // Delete from registration
	        if ($insert && $update_login) {
	            mysqli_query($conn, "DELETE FROM registration WHERE reg_id = '$reg_id'");
	            echo json_encode(['success' => true, 'message' => 'User approved successfully.']);
	        } else {
	            echo json_encode(['success' => false, 'message' => 'Error moving data.']);
	        }
	
	    } elseif ($action === 'deny') {
	        $deleted = mysqli_query($conn, "DELETE FROM registration WHERE reg_id = '$reg_id'");
	        echo json_encode(['success' => $deleted, 'message' => $deleted ? 'Request denied and removed.' : 'Deletion failed.']);
	    } else {
	        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
	    }
	}
	?>