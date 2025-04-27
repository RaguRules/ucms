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
	?>