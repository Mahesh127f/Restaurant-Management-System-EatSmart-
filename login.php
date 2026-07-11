<?php
$pageTitle = 'Login';
require_once 'includes/header.php';
$error = '';
if(isLoggedIn()) { header('Location: index.php'); exit; }
if($_SERVER['REQUEST_METHOD']==='POST') {
  $email = sanitize($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $db = getDB();
  $stmt = $db->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  if($user && password_verify($pass, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $redirect = $_GET['redirect'] ?? ($user['role']==='admin' ? 'admin/index.php' : 'index.php');
    header('Location: ' . $redirect); exit;
  } else {
    $error = 'Invalid email or password. Try admin@eatsmart.com / password';
  }
}
?>
<section class="section" style="padding-top:40px">
  <div class="container" style="max-width:440px">
    <div class="card">
      <div class="card-body" style="padding:36px">
        <div style="text-align:center;margin-bottom:28px">
          <div style="font-size:2.5rem;margin-bottom:8px">🍽️</div>
          <h2 style="margin-bottom:4px">Welcome back</h2>
          <p style="font-size:0.88rem">Sign in to your EatSmart account</p>
        </div>
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="you@email.com" required value="<?php echo sanitize($_POST['email']??''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">Sign In</button>
        </form>
        <div class="divider"></div>
        <div style="text-align:center;font-size:0.88rem">
          Don't have an account? <a href="register.php">Create one →</a>
        </div>
        <div style="margin-top:16px;padding:12px;background:var(--surface2);border-radius:var(--radius-sm);font-size:0.8rem;color:var(--text3)">
          <strong>Demo Accounts:</strong><br>
          Admin: admin@eatsmart.com / password<br>
          Customer: rahul@gmail.com / password
        </div>
      </div>
    </div>
  </div>
</section>
<?php require_once 'includes/footer.php'; ?>
