<?php
require_once 'db.php'; 
header('Content-Type: application/json');

if (isset($_POST['check']) && isset($_POST['value'])) {
    $check = $_POST['check'];
    $value = trim($_POST['value']);

    // Supported checks and columns
    $valid_checks = [
        'email' => 'email',
        'mobile' => 'mobile',
        'nic' => 'nic_number'
    ];

    // Supported tables
    $tables = ['staff', 'lawyer', 'police', 'registration'];

    if (!isset($valid_checks[$check])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid check type']);
        exit;
    }

    $column = $valid_checks[$check];
    $found_in = null;

    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT `$column` FROM `$table` WHERE `$column` = ? LIMIT 1");
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $found_in = $table;
            break;
        }
    }

    if ($found_in === null) {
        echo json_encode(['exists' => false]);
    } elseif ($found_in === 'registration') {
        echo json_encode([
            'exists' => true,
            'pending' => true,
            'message' => 'Previous request with this value is waiting for admin approval. Please wait...'
        ]);
    } else {
        echo json_encode([
            'exists' => true,
            'message' => "Duplicate found in $found_in table."
        ]);
    }

    exit;
}
?>
