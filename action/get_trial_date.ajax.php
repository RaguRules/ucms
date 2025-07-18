<?php

include_once('../lib/db.php');
require_once('../lib/security.php');
require_once('../lib/helper.php');

$helper = new Helper($conn);
$security = new Security();


header('Content-Type: application/json');

$offsetDays = 21;
$maxPerDay = 10;
$stepDays = 7;

for ($i = 0; $i < 8; $i++) {
    $target = date('Y-m-d', strtotime("+".($offsetDays + $i * $stepDays)." days"));

    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM dailycaseactivities WHERE next_status = 'Trial' AND next_date = ?");
    $stmt->bind_param("s", $target);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    
    if (($res['cnt'] ?? 0) < $maxPerDay) {
        echo json_encode(['date' => $target]);
        exit;
    }
}

http_response_code(404);
echo json_encode(['error' => 'No date found']);