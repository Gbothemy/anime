<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';

if (is_logged_in()) { redirect(base_url('/')); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) { $_SESSION['flash'] = ['type'=>'danger','msg'=>'Invalid CSRF.']; redirect(base_url('register')); }
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!$username || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6 || $password !== $confirm) {
        $_SESSION['flash'] = ['type'=>'warning','msg'=>'Please provide valid details and matching passwords (min 6 chars).'];
        redirect(base_url('register'));
    }
    $exists = db_query('SELECT COUNT(*) FROM users WHERE username=:u OR email=:e', [':u'=>$username, ':e'=>$email])->fetchColumn();
    if ($exists) { $_SESSION['flash'] = ['type'=>'warning','msg'=>'Username or email already taken.']; redirect(base_url('register')); }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    db_query('INSERT INTO users (username,email,password_hash,is_admin) VALUES (:u,:e,:p,0)', [':u'=>$username, ':e'=>$email, ':p'=>$hash]);
    $_SESSION['flash'] = ['type'=>'success','msg'=>'Registration successful. You may now login.'];
    redirect(base_url('login'));
}
?>
<div class="row justify-content-center">
  <div class="col-12 col-md-6 col-lg-5">
    <div class="cm-card p-3">
      <h1 class="h5 mb-3">Create Account</h1>
      <form method="post">
        <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input class="form-control" name="username" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" required>
        </div>
        <div class="row g-2">
          <div class="col-12 col-md-6">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="confirm" required>
          </div>
        </div>
        <div class="d-grid mt-3">
          <button class="btn btn-gradient" type="submit">Register</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>