<?php
require 'db.php';
require 'functions.php';
$pageTitle = "Profile";
require 'layout.php';
if (!is_logged_in()) { 
    header('Location: login.php'); 
    exit; 
}

$uid = current_user_id();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $stmt = $mysqli->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
    $stmt->bind_param('ssi', $name, $phone, $uid);

    if ($stmt->execute()) $msg = 'Profile updated successfully';
    else $msg = 'Update failed: ' . $stmt->error;
}

$stmt = $mysqli->prepare("SELECT id,name,email,phone,role FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<style>
.profile-card {
  max-width: 520px;
  margin: 40px auto;
  border-radius: 18px;
  box-shadow: 0 14px 32px rgba(0,0,0,0.08);
  border: none;
}
.profile-title {
  font-size: 1.8rem;
  font-weight: 800;
}
.profile-label {
  font-weight: 600;
  font-size: 0.9rem;
  margin-bottom: 4px;
}
.profile-input {
  height: 48px;
  border-radius: 12px;
}
.save-btn {
  background: #e21b70;
  border: none;
  border-radius: 999px;
  padding: 8px 20px;
}
.save-btn:hover {
  background: #c4175f;
}
.link-muted {
  color: #e21b70;
  text-decoration: none;
  font-weight: 600;
}
.link-muted:hover {
  text-decoration: underline;
}
</style>
<div class="container main-wrapper">

  <div class="card profile-card">
    <div class="card-body p-4">

      <div class="mb-4 text-center">
        <div class="profile-title">Your Profile</div>
        <div class="text-muted">
          Role: <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
        </div>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-info">
          <?php echo htmlspecialchars($msg); ?>
        </div>
      <?php endif; ?>

      <form method="post">

        <div class="mb-3">
          <div class="profile-label">Full Name</div>
          <input
            class="form-control profile-input"
            name="name"
            value="<?php echo htmlspecialchars($user['name']); ?>"
            required
          >
        </div>

        <div class="mb-3">
          <div class="profile-label">Email</div>
          <input
            class="form-control profile-input"
            value="<?php echo htmlspecialchars($user['email']); ?>"
            disabled
          >
        </div>

        <div class="mb-4">
          <div class="profile-label">Phone</div>
          <input
            class="form-control profile-input"
            name="phone"
            value="<?php echo htmlspecialchars($user['phone']); ?>"
          >
        </div>

        <div class="d-grid">
          <button class="btn btn-primary save-btn">
            Save Changes
          </button>
        </div>

      </form>

      <hr class="my-4">

      <div class="text-center">
        <a href="addresses.php" class="link-muted">
          📍 Manage Addresses
        </a>
      </div>

    </div>
  </div>

</div>

