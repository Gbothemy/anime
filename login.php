<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';

if (is_logged_in()) { redirect(base_url('/')); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) { $_SESSION['flash'] = ['type'=>'danger','msg'=>'Invalid CSRF.']; redirect(base_url('login')); }
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = db_query('SELECT * FROM users WHERE username=:u OR email=:u LIMIT 1', [':u'=>$username])->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = ['id'=>$user['id'], 'username'=>$user['username'], 'email'=>$user['email'], 'is_admin'=>(int)$user['is_admin']];
        redirect(base_url('/'));
    }
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Invalid credentials'];
    redirect(base_url('login'));
}
?>
<div class="row justify-content-center">
  <div class="col-12 col-md-6 col-lg-4">
    <div class="cm-card p-3">
      <h1 class="h5 mb-3">Login</h1>
      <form method="post">
        <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
        <div class="mb-3">
          <label class="form-label">Username or Email</label>
          <input class="form-control" name="username" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        <div class="d-grid">
          <button class="btn btn-gradient" type="submit">Sign In</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>