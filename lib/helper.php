<?php	
	// Helper function to get temporary registration ID in Register table
	function generateNextRegistrationID($conn) {
		// 1. Find the highest existing staff_id
		$sql = "SELECT MAX(reg_id) AS max_id FROM registration";
		$result = mysqli_query($conn, $sql);
	
		if ($result && mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$max_id = $row['max_id'];
	
			if ($max_id === null) {
				// No staff IDs exist yet, start with S0001
				return "R0001";
			} else {
				// Extract the numeric part, increment, and format
				$numeric_part = (int)substr($max_id, 1); // Remove "S", convert to integer
				$next_numeric_part = $numeric_part + 1;
				// Pad with leading zeros to 4 digits, then prepend "S"
				return "R" . str_pad($next_numeric_part, 4, "0", STR_PAD_LEFT);
			}
		} else {
			// Handle errors (e.g., table doesn't exist)
			return "R0001";
		}
	}



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


	function generateNextRoleID($conn) {
		// 1. Find the highest existing staff_id
		$sql = "SELECT MAX(role_id) AS max_id FROM roles";
		$result = mysqli_query($conn, $sql);
	
		if ($result && mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$max_id = $row['max_id'];
	
			if ($max_id === null) {
				// No staff IDs exist yet, start with P0001
				return "R01";
			} else {
				// Extract the numeric part, increment, and format
				$numeric_part = (int)substr($max_id, 1); // Remove "S", convert to integer
				$next_numeric_part = $numeric_part + 1;
				// Pad with leading zeros to 4 digits, then prepend "S"
				return "R" . str_pad($next_numeric_part, 2, "0", STR_PAD_LEFT);
			}
		} else {
			// Handle errors (e.g., table doesn't exist)
			return "R01"; // Or throw an exception, log an error, etc.
		}
	}


	function generateNextStaffID($conn) {
		// 1. Find the highest existing staff_id
		$sql = "SELECT MAX(staff_id) AS max_id FROM staff";
		$result = mysqli_query($conn, $sql);
	
		if ($result && mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_assoc($result);
			$max_id = $row['max_id'];
	
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
	

	// --- Helper Function to Get Staff Data ---
	function getStaffDataFromDatabase($staff_id, $conn) {
		// $stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ? AND is_active = 1");
		$stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
		$stmt->bind_param("s", $staff_id);
		$stmt->execute();
		$result = $stmt->get_result();
	
		if ($result && $result->num_rows > 0) {
			return $result->fetch_assoc();
		} else {
			return null;
		}
	}	


		// --- Helper Function to Get Lawyer Data ---
		function getLawyerDataFromDatabase($lawyer_id, $conn) {
			// $stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ? AND is_active = 1");
			$stmt = $conn->prepare("SELECT * FROM lawyer WHERE lawyer_id = ?");
			$stmt->bind_param("s", $lawyer_id);
			$stmt->execute();
			$result = $stmt->get_result();
		
			if ($result && $result->num_rows > 0) {
				return $result->fetch_assoc();
			} else {
				return null;
			}
		}	
	
	function getCourtName($court_id) {
		switch ($court_id) {
			case 'C01':
				return "Magistrate's Court";
			case 'C02':
				return "District Court";
			case 'C03':
				return "High Court";
			default:
				return "Unknown";
		}
	}
	
	function getRoleName($role_id) {
		switch ($role_id) {
			case 'R01':
				return "Administrator";
			case 'R02':
				return "Hon. Judge";
			case 'R03':
				return "The Registrar";
			case 'R04':
				return "Interpreter";
			case 'R05':
				return "Common Staff";
			case 'R06':
				return "Lawyer";
			case 'R07':
				return "Police";
			default:
				return "Unknown";
		}
	}

	//   Helper function to validate date
	function validateDate($date, $format = 'Y-m-d') {
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) === $date;
	}

?>