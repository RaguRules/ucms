<?php
if (!isset($_SESSION)) {
    session_start();
}

include_once('../lib/db.php');
include_once('../lib/security.php');
include_once('../lib/helper.php');

header('Content-Type: application/json');

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

if (isset($_POST['case_input'])) {
    $inputRaw = $_POST['case_input'];

    // 🔍 AI-like input normalization
    $normalized = strtolower(trim($inputRaw));
    $normalized = preg_replace('/[.,\s]+/', '/', $normalized); // Replace space, dot, comma with slash
    $normalized = preg_replace('/[^a-zA-Z0-9\/\-]/', '', $normalized); // Remove other special characters

    // Try direct match
    $stmt = $conn->prepare("SELECT * FROM cases WHERE LOWER(REPLACE(REPLACE(REPLACE(case_id, '.', '/'), ',', '/'), ' ', '/')) = ? OR LOWER(REPLACE(REPLACE(REPLACE(case_name, '.', '/'), ',', '/'), ' ', '/')) = ?");
    $stmt->bind_param("ss", $normalized, $normalized);
    $stmt->execute();
    $caseResult = $stmt->get_result();

    if ($case = $caseResult->fetch_assoc()) {
        // Check if warrant issued
        $isWarrant = $case['is_warrant'];

        // Get latest daily activity
        $stmt2 = $conn->prepare("SELECT activity_date, summary, next_date FROM dailycaseactivities WHERE case_name = ? ORDER BY activity_date DESC LIMIT 1");
        $stmt2->bind_param("s", $case['case_id']);
        $stmt2->execute();
        $activityResult = $stmt2->get_result();
        $latestActivity = $activityResult->fetch_assoc();

        echo json_encode([
            'success' => true,
            'case_name' => $case['case_name'],
            'is_warrant' => $isWarrant,
            'activity' => $latestActivity
        ]);
    } else {
        // Try fuzzy search (AI-like logic) if no exact match found
        $likePattern = "%" . $conn->real_escape_string(str_replace('/', '', $normalized)) . "%";
        $stmt3 = $conn->prepare("SELECT * FROM cases WHERE case_id LIKE ? OR case_name LIKE ? LIMIT 1");
        $stmt3->bind_param("ss", $likePattern, $likePattern);
        $stmt3->execute();
        $fuzzyResult = $stmt3->get_result();

        if ($fuzzyMatch = $fuzzyResult->fetch_assoc()) {
            $stmt4 = $conn->prepare("SELECT activity_date, summary, next_date FROM dailycaseactivities WHERE case_name = ? ORDER BY activity_date DESC LIMIT 1");
            $stmt4->bind_param("s", $fuzzyMatch['case_id']);
            $stmt4->execute();
            $activityResult = $stmt4->get_result();
            $latestActivity = $activityResult->fetch_assoc();

            echo json_encode([
                'success' => true,
                'case_name' => $fuzzyMatch['case_name'],
                'is_warrant' => $fuzzyMatch['is_warrant'],
                'activity' => $latestActivity,
                'note' => 'Fuzzy match used'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No matching case found.']);
        }
    }
}
?>