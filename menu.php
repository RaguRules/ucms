<header id="header" class="header sticky-top">
	
			<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
	<div class="topbar d-flex align-items-center">
		<div class="container d-flex justify-content-center justify-content-md-between">
			<div class="contact-info d-flex align-items-center">
				<i class="bi bi-envelope d-flex align-items-center"><a href="mailto:dcmckilinochchi2015@gmail.com">dcmckilinochchi2015@gmail.com</a></i>
				<i class="bi bi-phone d-flex align-items-center ms-4"><span>021 228 5303</span></i>
			</div>
			<div class="social-links d-none d-md-flex align-items-center">
			</div>
		</div>
	</div>
	<div class="branding d-flex align-items-center">
		<div class="container position-relative d-flex align-items-center justify-content-between">
			<a href="index.php" class="logo d-flex align-items-center me-auto">
                <img src="assets/img/sri-lanka-emblem.png" alt="Sri Lankan Emblem" class="emblem-logo">
                <h1 class="sitename">Courts Complex Kilinochchi</h1>
            </a>
			<nav id="navmenu" class="navmenu navmenu me-4">
				<ul>
					<!-- <li><a href="index.php" class="active">Home</a></li> -->
					<li class="dropdown">
						<a href="#"><span>Case Management</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
						<ul>
							<li><a href="index.php?pg=cases.php&option=view">Cases</a></li>
							<li><a href="index.php?pg=appeals.php&option=view">Appeals</a></li>
							<li><a href="index.php?pg=motions.php&option=view">Motions</a></li>
							<li><a href="index.php?pg=judgements.php&option=view">Judgements</a></li>
							<li><a href="index.php?pg=warrants.php&option=view">Warrants</a></li>
							<li><a href="index.php?pg=parties.php&option=view">Parties</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a href="#"><span>Court Operations</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
						<ul>
							<li><a href="index.php?pg=dailycaseactivities.php">Daily Case Activities</a></li>
							<li><a href="index.php?pg=orders.php">Orders</a></li>
							<li><a href="index.php?pg=notifications.php">Notifications</a></li>
							<li><a href="index.php?pg=fines.php">Fines</a></li>
							<li><a href="index.php?pg=notes.php">Notes</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a href="#"><span>Administration</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
						<ul>
							<li><a href="index.php?pg=courts.php">Courts</a></li>
							<li><a href="index.php?pg=staff.php&option=view">Staff</a></li>
							<li><a href="index.php?pg=lawyer.php&option=view">Lawyer</a></li>
							<li><a href="index.php?pg=police.php&option=view">Police</a></li>
							<li><a href="index.php?pg=roles.php">Roles</a></li>
						</ul>
					</li>
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

		<!-- <a class="cta-btn d-none d-sm-block" href="#appointment">#TO BE USED</a> -->
		<!-- <a class="cta-btn d-none d-sm-block" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a>
		<a class="cta-btn d-none d-sm-block" href="login.php">Login</a> -->
		<div class="d-flex align-items-center gap-3">
    <?php if (isset($_SESSION["LOGIN_USERNAME"])){ ?>
        <?php
            $username = htmlspecialchars($_SESSION["LOGIN_USERNAME"]);
            $firstLetter = strtoupper($username[0]);
        ?>
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
    <?php }else{ ?>
        <a href="login.php" class="btn btn-outline-primary px-4 shadow-sm rounded-pill">Login</a>
        <a href="#" class="btn btn-primary px-4 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a>
    <?php } ?>
</div>

		</div>

	</div>
</header>

<!-- <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
    Register
  </button> -->

  <!-- Modal -->

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
            <div class="register-option" onclick="window.location.href='register.php?type=lawyer';">
              <i class="fas fa-gavel"></i>
              <h5>Register as Lawyer</h5>
			  <p class="mt-3 text-muted small">
          If you are working with Kilinochchi Courts and have not registered with us yet, you can enroll here. After verification, your profile will be activated and you will be notified accordingly.
        </p>
		<a href="register.php?type=lawyer" class="btn btn-outline-primary mt-3">
          Proceed as Lawyer
        </a>
            </div>
          </div>
          <div class="col-md-6">
            <div class="register-option" onclick="window.location.href='register.php?type=police';">
              <i class="fas fa-user-shield"></i>
              <h5>Register as Police</h5>
			  <p class="mt-3 text-muted small">
          Police personnel working under Kilinochchi Court jurisdiction can register here. Once we verify your information, you will be granted access and notified through your provided contact details.
        </p>
		<a href="register.php?type=police" class="btn btn-outline-primary mt-3">
		Proceed as Police
        </a>
            </div>
          </div>
        </div>
      </div>
      <!-- <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div> -->
    </div>
  </div>
</div>
