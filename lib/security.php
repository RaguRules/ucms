<?php
// OOP-based security module (formerly procedural security.php)

class Security
{
    private $uploadDir;
    private $allowedExtensions;
    private $maxFileSize;

    public function __construct($uploadDir = 'uploads/', $allowedExtensions = ['jpg', 'jpeg', 'png', 'heic', 'heif'], $maxFileSize = 6291456)
    {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->allowedExtensions = $allowedExtensions;
        $this->maxFileSize = $maxFileSize;
    }

    public function uploadImage($fileInput = 'image')
    {
        if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'No valid image uploaded.'];
        }

        $file = $_FILES[$fileInput];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $originalName = $file['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, $this->allowedExtensions)) {
            return ['success' => false, 'error' => 'Invalid file extension.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileTmp);
        finfo_close($finfo);

        $validMimes = ['image/jpeg', 'image/png', 'image/heic', 'image/heif', 'application/octet-stream'];
        if (!in_array($mimeType, $validMimes)) {
            return ['success' => false, 'error' => "Invalid MIME type: $mimeType"];
        }

        if ($fileSize > $this->maxFileSize) {
            return ['success' => false, 'error' => 'File too large. Max 6 MB allowed.'];
        }

        // Remove metadata (JPEG only)
        if (in_array($ext, ['jpg', 'jpeg'])) {
            $img = imagecreatefromjpeg($fileTmp);
            if ($img) {
                imagejpeg($img, $fileTmp, 100);
                imagedestroy($img);
            }
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $newFileName = uniqid('img_', true) . '.' . $ext;
        $uploadPath = $this->uploadDir . $newFileName;

        if (move_uploaded_file($fileTmp, $uploadPath)) {
            return ['success' => true, 'filename' => $newFileName, 'path' => $uploadPath];
        } else {
            return ['success' => false, 'error' => 'Failed to upload file.'];
        }
    }

    public static function sanitize($data)
    {
       return htmlspecialchars(stripslashes(trim($data)));
    }

    public static function logError($message)
    {
        $root = dirname(__DIR__);
        $logDir = $root . '/logs';
        $logFile = $logDir . '/error_log.txt';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $entry = '[' . date('Y-m-d H:i:s') . "] $message\n";
        file_put_contents($logFile, $entry, FILE_APPEND);
    }

    public static function addToBlacklist($email, $nic, $phone)
    {
        $blacklistFile = 'blacklist.dat';
        $newEntry = hash('sha256', $email) . ',' . hash('sha256', $nic) . ',' . hash('sha256', $phone) . "\n";
        file_put_contents($blacklistFile, $newEntry, FILE_APPEND);
    }

    public static function isBlocked($email, $nic, $phone)
    {
        $blacklistFile = 'blacklist.dat';
        if (!file_exists($blacklistFile)) return false;

        $hashEmail = hash('sha256', $email);
        $hashNic = hash('sha256', $nic);
        $hashPhone = hash('sha256', $phone);

        $lines = file($blacklistFile, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            list($blkEmail, $blkNic, $blkPhone) = explode(',', $line);
            if ($blkEmail === $hashEmail || $blkNic === $hashNic || $blkPhone === $hashPhone) {
                return true;
            }
        }
        return false;
    }

    public static function checkDuplicate($conn, $column, $value, $redirect = '', $message = '', $ignoreStaffId = null)
    {
        $tables = ['staff', 'lawyer', 'police'];

        foreach ($tables as $table) {
            $sql = "SELECT $column FROM $table WHERE $column = ?";
            if ($table === 'staff' && $ignoreStaffId !== null) {
                $sql .= " AND staff_id != ?";
            }
            $sql .= " LIMIT 1";

            $stmt = $conn->prepare($sql);
            if (!$stmt) continue;

            if ($table === 'staff' && $ignoreStaffId !== null) {
                $stmt->bind_param('ss', $value, $ignoreStaffId);
            } else {
                $stmt->bind_param('s', $value);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                if (!empty($message)) {
                    echo "<script>alert(" . json_encode($message) . ");</script>";
                }
                if (!empty($redirect)) {
                    echo "<script>location.href='$redirect';</script>";
                }
                exit;
            }
        }
    }
}
?>
