<!-- this security.php is a reflection of Modularity concept – Build security as independent modules. One of the Core Principle of Security Design -->
<?php
	function secure_image_upload($file_input_name = 'image', $upload_dir = 'uploads/', $allowed_ext = ['jpg', 'jpeg', 'png', 'heic', 'heif'], $max_file_size = 6 * 1024 * 1024)
	{
	    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_OK) {
	        return ['success' => false, 'error' => 'No valid image uploaded.'];
	    }
	
	    $file = $_FILES[$file_input_name];
	    $file_tmp = $file['tmp_name'];
	    $file_size = $file['size'];
	    $original_name = $file['name'];
	
	    // Extension check
	    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
	    if (!in_array($ext, $allowed_ext)) {
	        return ['success' => false, 'error' => 'Invalid file extension.'];
	    }
	
	    // MIME check
	    $finfo = finfo_open(FILEINFO_MIME_TYPE);
	    $mime_type = finfo_file($finfo, $file_tmp);
	    finfo_close($finfo);
	
	    $valid_mimes = ['image/jpeg', 'image/png', 'image/heic', 'image/heif', 'application/octet-stream'];
	    if (!in_array($mime_type, $valid_mimes)) {
	        return ['success' => false, 'error' => "Invalid MIME type: $mime_type"];
	    }
	
	    // Size check
	    if ($file_size > $max_file_size) {
	        return ['success' => false, 'error' => 'File too large. Max 6 MB allowed.'];
	    }
	
	    // Remove metadata (JPEG only)
	    if ($ext === 'jpg' || $ext === 'jpeg') {
	        $img = imagecreatefromjpeg($file_tmp);
	        if ($img) {
	            imagejpeg($img, $file_tmp, 100);
	            imagedestroy($img);
	        }
	    }
	
	    // Secure name
	    $new_filename = uniqid('img_', true) . '.' . $ext;
	    $upload_path = rtrim($upload_dir, '/') . '/' . $new_filename;
	
	    // Create folder if not exists
	    if (!is_dir($upload_dir)) {
	        mkdir($upload_dir, 0755, true);
	    }
	
	    // Move file
	    if (move_uploaded_file($file_tmp, $upload_path)) {
	        return ['success' => true, 'filename' => $new_filename, 'path' => $upload_path];
	    } else {
	        return ['success' => false, 'error' => 'Failed to upload file.'];
	    }
	}

	
	// Sanitize inputs
	function sanitize_input($data) {
		$data = trim($data); // Remove whitespace from the beginning and end of the string
		$data = stripslashes($data); // Remove backslashes from the string
		$data = htmlspecialchars($data); // Convert special characters to HTML entities
		return $data; // Return the sanitized string
	}


	// Write error logs
	function logError($message) {
		$rootPath = dirname(__DIR__); // Go one directory up from /lib/
		$logDir = $rootPath . '/logs';
		$logFile = $logDir . '/error_log.txt';
		
		if (!is_dir($logDir)) {
			mkdir($logDir, 0755, true); // Create the directory if not exists
		}
	
		$current = "[" . date("Y-m-d H:i:s") . "] " . $message . "\n";
		file_put_contents($logFile, $current, FILE_APPEND);
	}
	
	// Add to blacklist
	function addToBlacklist($email, $nic, $phone) {
		$blacklistFile = 'blacklist.dat';
		
		// Format the new entry
		$newEntry = "$email,$nic,$phone\n";
		
		// Append the new entry to the blacklist file
		file_put_contents($blacklistFile, $newEntry, FILE_APPEND);
	}
	// Add to blacklist with hashed values
	// function addToBlacklist($email, $nic, $phone) {
	// 	$blacklistFile = 'blacklist.dat';
		
	// 	// Hash sensitive data using SHA-256 (SHA-1 is weak, use SHA-256 or bcrypt)
	// 	$hashedEmail = hash('sha256', $email);
	// 	$hashedNic = hash('sha256', $nic);
	// 	$hashedPhone = hash('sha256', $phone);
		
	// 	// Format the new entry with hashed values
	// 	$newEntry = "$hashedEmail,$hashedNic,$hashedPhone\n";
		
	// 	// Append the new entry to the blacklist file
	// 	file_put_contents($blacklistFile, $newEntry, FILE_APPEND);
	// }
	
	// Check if the entry is Blacklisted.
	function isBlocked($email, $nic, $phone) {
		$blacklistFile = 'blacklist.dat'; // Path to the blocklist file
		
		// Check if the blocklist file exists
		if (!file_exists($blacklistFile)) {
			return false; // If the file doesn't exist, no blocked entries
		}
	
		// Hash the input data
		$hashedEmail = hash('sha256', $email);
		$hashedNic = hash('sha256', $nic);
		$hashedPhone = hash('sha256', $phone);
	
		// Read the blocklist file line by line
		$lines = file($blacklistFile, FILE_IGNORE_NEW_LINES);
		foreach ($lines as $line) {
			// Split the line into the hashed email, NIC, and phone
			list($blockedEmail, $blockedNic, $blockedPhone) = explode(',', $line);
			
			// Compare the hashed values
			if ($blockedEmail === $hashedEmail || $blockedNic === $hashedNic || $blockedPhone === $hashedPhone) {
				return true; // Match found, the entry is blocked
			}
		}
		return false; // No match found, the entry is not blocked
	}

	
	// Check for duplicates across staff, lawyer, police tables
	function check_duplicate($conn, $column, $value, $redirect_url = '', $alert_message = '', $ignore_staff_id = null) {
		$tables = ['staff', 'lawyer', 'police'];
	
		foreach ($tables as $table) {
			$query = "SELECT $column FROM $table WHERE $column = ?";
			if ($table == 'staff' && $ignore_staff_id !== null) {
				$query .= " AND staff_id != ?";
			}
			$query .= " LIMIT 1";
	
			$stmt = $conn->prepare($query);
			if (!$stmt) {
				continue;
			}
	
			if ($table == 'staff' && $ignore_staff_id !== null) {
				$stmt->bind_param("ss", $value, $ignore_staff_id);
			} else {
				$stmt->bind_param("s", $value);
			}
	
			$stmt->execute();
			$result = $stmt->get_result();
	
			if ($result && $result->num_rows >= 1) {
				if (!empty($alert_message)) {
					echo "<script>alert(" . json_encode($alert_message) . ");</script>";
				}
				if (!empty($redirect_url)) {
					echo "<script>location.href='$redirect_url';</script>";
				}
				exit;
			}
		}
	}	
?>