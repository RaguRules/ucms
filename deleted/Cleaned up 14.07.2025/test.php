<?php

// Simulate CSRF token for demo
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Simulate database fetch for edit (replace with real DB fetch)
function getStaffData($staffId) {
    // Example fake data for editing
    return [
        'staff_id' => $staffId,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'mobile' => '0712345678',
        'nic_number' => '123456789V',
        'date_of_birth' => '1990-05-10',
        'email' => 'john.doe@example.com',
        'address' => '123 Main St',
        'court_id' => 'C02',
        'role_id' => 'R03',
        'gender' => 'Male',
        'appointment' => 'Ministry Staff',
        'joined_date' => '2020-01-01',
        'image_path' => 'uploads/john.jpg',
        'is_active' => true,
    ];
}

// Handle GET parameter for option and staff_id
$option = $_GET['option'] ?? 'add';
$staffId = $_GET['id'] ?? null;

// For add, generate new staff id (simulate)
$next_staff_id = 'S' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

if ($option === 'edit' && $staffId !== null) {
    $data = getStaffData($staffId);
} else {
    $data = null;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Staff Form - <?php echo htmlspecialchars(ucfirst($option)); ?></title>

  <!-- Materialize CSS CDN -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

  <style>
    body { padding: 2rem; background-color: #f5f5f5; }
    .container { background: white; padding: 2rem; border-radius: 8px; max-width: 700px; }
  </style>
</head>
<body>

<div class="container">
  <h4 class="center-align"><?php echo $option === 'edit' ? 'Edit Staff' : 'Add New Staff'; ?></h4>

  <form method="POST" action="your_submit_script.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
    <input type="hidden" name="txt_staff_id" value="<?php echo htmlspecialchars($data['staff_id'] ?? $next_staff_id); ?>" />

    <div class="row">
      <div class="input-field col s12 m6">
        <input id="txt_first_name" name="txt_first_name" type="text" required
          value="<?php echo htmlspecialchars($data['first_name'] ?? ''); ?>" />
        <label for="txt_first_name" class="<?php echo $data ? 'active' : ''; ?>">First Name</label>
      </div>

      <div class="input-field col s12 m6">
        <input id="txt_last_name" name="txt_last_name" type="text" required
          value="<?php echo htmlspecialchars($data['last_name'] ?? ''); ?>" />
        <label for="txt_last_name" class="<?php echo $data ? 'active' : ''; ?>">Last Name</label>
      </div>
    </div>

    <div class="row">
      <div class="input-field col s12 m6">
        <input id="int_mobile" name="int_mobile" type="text" required
          value="<?php echo htmlspecialchars($data['mobile'] ?? ''); ?>" />
        <label for="int_mobile" class="<?php echo $data ? 'active' : ''; ?>">Mobile Number</label>
      </div>

      <div class="input-field col s12 m6">
        <input id="txt_nic_number" name="txt_nic_number" type="text" required
          value="<?php echo htmlspecialchars($data['nic_number'] ?? ''); ?>" />
        <label for="txt_nic_number" class="<?php echo $data ? 'active' : ''; ?>">NIC Number</label>
      </div>
    </div>

    <div class="row">
      <div class="input-field col s12 m6">
        <input id="txt_email" name="txt_email" type="email" required
          value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" />
        <label for="txt_email" class="<?php echo $data ? 'active' : ''; ?>">Email</label>
      </div>

      <div class="input-field col s12 m6">
        <input id="txt_address" name="txt_address" type="text" required
          value="<?php echo htmlspecialchars($data['address'] ?? ''); ?>" />
        <label for="txt_address" class="<?php echo $data ? 'active' : ''; ?>">Address</label>
      </div>
    </div>

    <div class="row">
      <div class="input-field col s12 m6">
        <select id="select_appointment" name="select_appointment" required>
          <option value="" disabled <?php echo empty($data['appointment']) ? 'selected' : ''; ?>>Choose Officer Classification</option>
          <?php
            $appointments = ['Judicial Staff (JSC)', 'Ministry Staff', 'O.E.S/ Peon/ Security'];
            foreach ($appointments as $appt) {
              $selected = ($data['appointment'] ?? '') === $appt ? 'selected' : '';
              echo "<option value=\"$appt\" $selected>$appt</option>";
            }
          ?>
        </select>
        <label>Officer Classification</label>
      </div>

      <div class="file-field input-field col s12 m6">
        <div class="btn">
          <span>Profile Photo</span>
          <input type="file" id="img_profile_photo" name="img_profile_photo" accept="image/*" />
        </div>
        <div class="file-path-wrapper">
          <input class="file-path validate" type="text" placeholder="Upload profile photo" />
        </div>
      </div>
    </div>

    <div class="row">
      <div class="input-field col s12 m6">
        <input id="date_date_of_birth" name="date_date_of_birth" type="text" class="datepicker" required
          value="<?php echo htmlspecialchars($data['date_of_birth'] ?? ''); ?>" />
        <label for="date_date_of_birth" class="<?php echo $data ? 'active' : ''; ?>">Date of Birth</label>
      </div>

      <div class="input-field col s12 m6">
        <select id="select_gender" name="select_gender" required>
          <option value="" disabled <?php echo empty($data['gender']) ? 'selected' : ''; ?>>Choose Gender</option>
          <?php
            $genders = ['Male', 'Female', 'Other'];
            foreach ($genders as $gender) {
              $selected = ($data['gender'] ?? '') === $gender ? 'selected' : '';
              echo "<option value=\"$gender\" $selected>$gender</option>";
            }
          ?>
        </select>
        <label>Gender</label>
      </div>
    </div>

    <div class="row">
      <div class="input-field col s12 m6">
        <select id="select_court_name" name="select_court_name" required>
          <option value="" disabled <?php echo empty($data['court_id']) ? 'selected' : ''; ?>>Choose Court Name</option>
          <?php
            $courts = [
              'C01' => "Magistrate's Court",
              'C02' => "District Court",
              'C03' => "High Court"
            ];
            foreach ($courts as $code => $court) {
              $selected = ($data['court_id'] ?? '') === $code ? 'selected' : '';
              echo "<option value=\"$code\" $selected>$court</option>";
            }
          ?>
        </select>
        <label>Court Name</label>
      </div>

      <div class="input-field col s12 m6">
        <select id="select_role_name" name="select_role_name" required>
          <option value="" disabled <?php echo empty($data['role_id']) ? 'selected' : ''; ?>>Choose Role</option>
          <?php
            $roles = [
              'R01' => 'Administrator',
              'R02' => 'Hon. Judge',
              'R03' => 'The Registrar',
              'R04' => 'Interpreter',
              'R05' => 'Other Staff',
            ];
            foreach ($roles as $code => $role) {
              $selected = ($data['role_id'] ?? '') === $code ? 'selected' : '';
              echo "<option value=\"$code\" $selected>$role</option>";
            }
          ?>
        </select>
        <label>Role Name</label>
      </div>
    </div>

    <p class="grey-text text-darken-1">* Password will be generated and sent to registered email/mobile</p>

    <input type="hidden" id="date_joined_date" name="date_joined_date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($data['joined_date'] ?? date('Y-m-d')); ?>" required />

    <div class="row center-align">
      <button class="btn waves-effect waves-light blue" type="submit" name="<?php echo $option === 'edit' ? 'btn_update' : 'btn_add'; ?>">
        <?php echo $option === 'edit' ? 'Update' : 'Submit'; ?>
        <i class="material-icons right">send</i>
      </button>
      <button class="btn grey lighten-1 black-text" type="reset">Clear Inputs</button>
    </div>
  </form>
</div>

<!-- Materialize JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var elemsSelect = document.querySelectorAll('select');
    M.FormSelect.init(elemsSelect);

    var elemsDatepicker = document.querySelectorAll('.datepicker');
    M.Datepicker.init(elemsDatepicker, {
      format: 'yyyy-mm-dd',
      maxDate: new Date(),
      autoClose: true,
    });
  });
</script>

</body>
</html>
