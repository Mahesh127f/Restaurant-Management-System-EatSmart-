<?php
$pageTitle = 'Register';
require_once 'includes/header.php';
$error = $success = '';
if(isLoggedIn()) { header('Location: index.php'); exit; }
if($_SERVER['REQUEST_METHOD']==='POST') {
  $name = sanitize($_POST['name'] ?? '');
  $email = sanitize($_POST['email'] ?? '');
  $phone = sanitize($_POST['phone'] ?? '');
  $pass = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';
  if(!$name || !$email || !$pass) { $error = 'All fields are required.'; }
  elseif($pass !== $confirm) { $error = 'Passwords do not match.'; }
  elseif(strlen($pass) < 6) { $error = 'Password must be at least 6 characters.'; }
  else {
    $db = getDB();
    $check = $db->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param('s', $email); $check->execute();
    if($check->get_result()->num_rows > 0) {
      $error = 'This email is already registered. <a href="login.php">Login instead</a>';
    } else {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $stmt = $db->prepare("INSERT INTO users (name, email, phone, password) VALUES (?,?,?,?)");
      $stmt->bind_param('ssss', $name, $email, $phone, $hash);
      if($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['name'] = $name; $_SESSION['email'] = $email; $_SESSION['role'] = 'customer';
        header('Location: index.php?welcome=1'); exit;
      } else { $error = 'Registration failed. Please try again.'; }
    }
  }
}
?>
<section class="section" style="padding-top:40px">
  <div class="container" style="max-width:480px">
    <div class="card">
      <div class="card-body" style="padding:36px">
        <div style="text-align:center;margin-bottom:28px">
          <div style="font-size:2.5rem;margin-bottom:8px">✨</div>
          <h2 style="margin-bottom:4px">Create Account</h2>
          <p style="font-size:0.88rem">Join EatSmart for a smarter dining experience</p>
        </div>
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" placeholder="Rahul Sharma" required value="<?php echo sanitize($_POST['name']??''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="you@email.com" required value="<?php echo sanitize($_POST['email']??''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Phone (optional)</label>
            <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210" value="<?php echo sanitize($_POST['phone']??''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">Create Account</button>
        </form>
        <div class="divider"></div>
        <div style="text-align:center;font-size:0.88rem">Already have an account? <a href="login.php">Sign in →</a></div>
      </div>
    </div>
  </div>
</section>
<?php require_once 'includes/footer.php'; ?>
