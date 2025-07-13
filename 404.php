<!DOCTYPE html>
<html lang="en">
<head>
    <title>Access Denied</title>
</head>
<body>


<div class="modal fade" id="unauthorizedModal" tabindex="-1" aria-labelledby="unauthorizedLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="unauthorizedLabel"><i class="bi bi-shield-lock-fill me-2"></i>Access Denied</h5>
      </div>
      <div class="modal-body text-center">
        <p class="fs-5"><?php echo Security::sanitize("You are not authorized to access this page."); ?></p>
        <span class="text-muted">Contact the Administrator if you believe this is an error.</span>
      </div>
      <div class="modal-footer justify-content-center">
        <a href="index.php" class="btn btn-primary"><i class="bi bi-house-door-fill me-1"></i> Home</a>
        <a href="login.php" class="btn btn-secondary"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
      </div>
    </div>
  </div>
</div>


<script>
window.onload = function () {
    const myModal = new bootstrap.Modal(document.getElementById('unauthorizedModal'));
    myModal.show();
};
</script>

</body>
</html>