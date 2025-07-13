<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    include_once('lib/db.php');
    include_once('lib/helper.php');
}

$receiver_id = $_SESSION['LOGIN_USERNAME'] ?? null;
if (!$receiver_id) {
    echo '<p class="text-center p-5 text-muted">Not logged in.</p>';
    exit;
}

// Fetch the unread notifications
$notifCount = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM notifications WHERE receiver_id = ? AND status = 'unread'");
$stmt->bind_param("s", $receiver_id);
$stmt->execute();
$stmt->bind_result($notifCount);
$stmt->fetch();
$stmt->close();

// Set limit for notifications per page
$limit = 15;  // Show 5 notifications per load (you can adjust this)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// General Info Notifications - Mark notification as read when clicked
if (isset($_GET['read']) && isset($_GET['id'])) {
    $notifId = $_GET['id'];
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE notification_id = ?");
    $stmt->bind_param("s", $notifId);
    $stmt->execute();
    // echo "<script>location.href='index.php?pg=notification.php';</script>";
}

// Redirection Type Notifications - Mark notification as read when clicked
if (isset($_GET['read']) && isset($_GET['id']) && isset($_GET['approval'])) {
    $notifId = $_GET['id'];
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE notification_id = ?");
    $stmt->bind_param("s", $notifId);
    $stmt->execute();
    echo "<script>location.href='index.php?pg=approve.php';</script>"; // Redirect back to notification page after marking as read
}

// Fetch all typws of notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE receiver_id = ? AND status = 'unread' ORDER BY created_at DESC LIMIT ?, ?");
$stmt->bind_param("sii", $receiver_id, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Mark all notifications as read
if (isset($_POST['markAllRead'])) {
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE receiver_id = ?");
    $stmt->bind_param("s", $receiver_id);
    $stmt->execute();
    echo "<script>window.location.reload();</script>"; // Reload page to update notification status
}
?>
<!DOCTYPE html>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="text-center text-primary">📬 Notifications</h2>
            <p class="text-center">You have <?= $notifCount ?> notifications</p>
            <hr>

            <!-- Button to Mark All Notifications as Read -->
             <?php
             if($notifCount != 0){
                ?>
            <form method="POST" class="text-center mb-4">
                <button type="submit" name="markAllRead" class="btn btn-outline-primary btn-sm">Mark All as Read</button>
            </form>
            <?php
             }else{
                ?>
                <div class="alert alert-info text-center">You have read all the Notifications...</div>
                <?php
             }
            ?>

            <!-- Notification List -->
            <div class="list-group">
                <?php foreach ($notifications as $notification){
                    if ($notification['type'] == 'Self-Registration Approval') {
                        ?>
                        <a href="?pg=notification.php&read=1&id=<?= $notification['notification_id']?>&approval=1" class="list-group-item list-group-item-action <?= $notification['status'] == 'unread' ? 'bg-light' : '' ?> border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-truncate" style="max-width: 80%"><?= htmlspecialchars($notification['message']) ?></strong>
                                <small class="text-muted"><?= date("Y-m-d H:i:s", strtotime($notification['created_at'])) ?></small>
                    </div>
                    </a>
                    <?php 
                    }else{
                    ?>

                    <a href="?pg=notification.php&read=1&id=<?= $notification['notification_id'] ?>" class="list-group-item list-group-item-action <?= $notification['status'] == 'unread' ? 'bg-light' : '' ?> border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-truncate" style="max-width: 80%"><?= htmlspecialchars($notification['message']) ?></strong>
                            <small class="text-muted"><?= date("Y-m-d H:i:s", strtotime($notification['created_at'])) ?></small>
                        </div>
                    </a>
                <?php }
                }
                ?>
            </div>

              <?php
             if($notifCount != 0){
                ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mt-3">
                    <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pg=notification.php&page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?pg=notification.php&page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php
             }
            ?>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-center text-muted mt-5">
    <p>&copy; 2025 Unified Courts Management System</p>
</footer>
</html

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
