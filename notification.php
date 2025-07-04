<?php
session_start();
include_once('lib/db.php');

$receiver_id = $_SESSION['LOGIN_USERNAME'] ?? null;

// If it's a click-to-read request
if (isset($_GET['read']) && isset($_GET['id']) && isset($_GET['type'])) {
    $notifId = $_GET['id'];
    $type = $_GET['type'];

    // Mark the notification as read
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE notification_id = ? AND receiver_id = ?");
    $stmt->bind_param("ss", $notifId, $receiver_id);
    $stmt->execute();

    // Redirect to the relevant page
    if ($type === 'lawyer' || $type === 'police') {
        header("Location: index.php?pg=approve.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

// Notification dropdown fetch
if (!$receiver_id) {
    echo '<li class="p-3 text-muted text-center">Not logged in.</li>';
    exit;
}

// Fetch unread notifications
$stmt = $conn->prepare("SELECT notification_id, message, type, created_at FROM notifications WHERE receiver_id = ? AND status = 'unread' ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("s", $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifId = urlencode($row['notification_id']);
        $notifType = urlencode($row['type']);
        $link = "notification.php?read=1&id=$notifId&type=$notifType";

        echo '<li class="px-3 py-2 border-bottom" style="min-width: 300px;">';
        echo '<a href="' . htmlspecialchars($link) . '" class="dropdown-item d-block text-wrap" style="font-size: 14px; line-height: 1.3;">';
        echo '<i class="bi bi-bell-fill text-warning me-2"></i> ';
        echo htmlspecialchars($row['message']);
        echo '<div class="text-muted small mt-1">' . htmlspecialchars($row['created_at']) . '</div>';
        echo '</a>';
        echo '</li>';
    }
} else {
    echo '<li class="text-center p-3 text-muted">No new notifications</li>';
}
?>
