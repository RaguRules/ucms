<header id="header" class="header sticky-top">
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Top Bar -->
    <div class="topbar d-flex align-items-center">
        <div class="container d-flex justify-content-center justify-content-md-between">
            <div class="contact-info d-flex align-items-center">
                <i class="bi bi-envelope d-flex align-items-center">
                    <a href="mailto:dcmckilinochchi2015@gmail.com">dcmckilinochchi2015@gmail.com</a>
                </i>
                <i class="bi bi-phone d-flex align-items-center ms-4"><span>021 228 5303</span></i>
            </div>
        </div>
    </div>

    <!-- Branding & Navigation -->
    <div class="branding d-flex align-items-center">
        <div class="container position-relative d-flex align-items-center justify-content-between">
            <a href="index.php" class="logo d-flex align-items-center me-auto">
                <img src="assets/img/sri-lanka-emblem.png" alt="Sri Lankan Emblem" class="emblem-logo">
                <h1 class="sitename">Courts Complex Kilinochchi</h1>
            </a>

            <!-- Navigation Menu -->
            <nav id="navmenu" class="navmenu navmenu me-4">
                <ul>
                    <!-- CASE MANAGEMENT -->
                    <li class="dropdown">
                        <a href="#"><span>Case Management</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                        <ul>
                            <li><a href="index.php?pg=cases.php&option=view">Cases</a></li>
                            <li><a href="index.php?pg=appeal.php&option=view">Appeals</a></li>
                            <li><a href="index.php?pg=motion.php&option=view">Motions</a></li>
                            <li><a href="index.php?pg=judgement.php&option=view">Judgements</a></li>
                            <li><a href="index.php?pg=warrant.php&option=view">Warrants</a></li>
                            <li><a href="index.php?pg=parties.php&option=view">Parties</a></li>
                        </ul>
                    </li>

                    <!-- COURT OPERATIONS -->
                    <li class="dropdown">
                        <a href="#"><span>Court Operations</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                        <ul>
                            <li><a href="index.php?pg=dailycaseactivities.php">Daily Case Activities</a></li>
                            <li><a href="index.php?pg=order.php">Orders</a></li>
                            <li><a href="index.php?pg=notification.php">Notifications</a></li>
                            <li><a href="index.php?pg=notes.php">Notes</a></li>
                        </ul>
                    </li>

                    <!-- ADMINISTRATION -->
                    <li class="dropdown">
                        <a href="#"><span>Administration</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                        <ul>
                            <li><a href="index.php?pg=courts.php">Courts</a></li>
                            <li><a href="index.php?pg=staff.php&option=view">Staff</a></li>
                            <li><a href="index.php?pg=lawyer.php&option=view">Lawyer</a></li>
                            <li><a href="index.php?pg=police.php&option=view">Police</a></li>
                            <li><a href="index.php?pg=roles.php">Roles</a></li>
							              <li><a href="index.php?pg=approve.php">Pending Approvals</a></li>
                        </ul>
                    </li>

                    <!-- USER/SYSTEM -->
                    <li class="dropdown">
                        <a href="#"><span>User/System</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                        <ul>
                            <li><a href="index.php?pg=about.php">About</a></li>
                            <li><a href="index.php?pg=contact.php">Contact</a></li>
                        </ul>
                    </li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <!-- Right-side: Notification + Profile/Login -->
            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION["LOGIN_USERNAME"])): ?>
                    <?php
                        $username = htmlspecialchars($_SESSION["LOGIN_USERNAME"]);
                        $firstLetter = strtoupper($username[0]);
                    ?>

                <?php
$notifCount = 0;
if (isset($_SESSION['LOGIN_USERNAME'])) {
    $username = $_SESSION['LOGIN_USERNAME'];
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM notifications WHERE receiver_id = ? AND status = 'unread'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($notifCount);
    $stmt->fetch();
    $stmt->close();
}
?>

<!-- Notification Bell UI -->
<li class="nav-item dropdown">
  <a class="nav-link position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="bi bi-bell fs-5"></i>
    <?php if ($notifCount > 0): ?>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $notifCount ?></span>
    <?php endif; ?>
  </a>

  <ul class="dropdown-menu dropdown-menu-end shadow rounded-3" aria-labelledby="notifDropdown" id="notif-list" style="min-width: 320px; max-width: 400px; font-size: 0.95rem;">
    <li class="dropdown-header fw-semibold text-primary px-3 py-2">🔔 Notifications</li>
    <li><hr class="dropdown-divider"></li>
    <!-- JS will replace this -->
    <li class="text-center text-muted p-2">Loading...</li>
  </ul>
</li>



                    <!-- Profile Button -->
                    <div class="dropdown">
                        <button class="btn btn-light shadow-sm rounded-circle d-flex align-items-center justify-content-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width: 45px; height: 45px; font-weight: bold;">
                            <?= $firstLetter ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                            <li class="px-3 py-2"><strong><?= $username ?></strong></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item text-danger" href="index.php?logout=true"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary px-4 shadow-sm rounded-pill">Login</a>
                    <a href="#" class="btn btn-primary px-4 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Registration Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="registerModalLabel">Choose Registration Type</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
          <div class="col-md-6">
            <div class="register-option" onclick="window.location.href='register.php?type=lawyer';" style="cursor:pointer;">
              <i class="fas fa-gavel"></i>
              <h5>Register as Lawyer</h5>
              <p class="mt-3 text-muted small">
                If you are working with Kilinochchi Courts and have not registered with us yet, you can enroll here. After verification, your profile will be activated and you will be notified accordingly.
              </p>
              <a href="register.php?type=lawyer" class="btn btn-outline-primary mt-3">Proceed as Lawyer</a>
            </div>
          </div>
          <div class="col-md-6">
            <div class="register-option" onclick="window.location.href='register.php?type=police';" style="cursor:pointer;">
              <i class="fas fa-user-shield"></i>
              <h5>Register as Police</h5>
              <p class="mt-3 text-muted small">
                Police personnel working under Kilinochchi Court jurisdiction can register here. Once we verify your information, you will be granted access and notified through your provided contact details.
              </p>
              <a href="register.php?type=police" class="btn btn-outline-primary mt-3">Proceed as Police</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Notification Dropdown JS -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const notifDropdown = document.getElementById('notifDropdown');
    const notifList = document.getElementById('notif-list');

    if (notifDropdown) {
        notifDropdown.addEventListener('click', function () {
            fetch('notification.php')  // Fetch notifications from notification.php
                .then(response => response.text())
                .then(html => {
                    notifList.innerHTML = html;  // Replace the "Loading..." message with actual notifications
                })
                .catch(error => {
                    notifList.innerHTML = '<li class="p-3 text-danger">Failed to load notifications</li>';
                    console.error('Error loading notifications:', error);
                });
        });
    }
});

</script>
