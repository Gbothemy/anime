<?php
require_once __DIR__ . '/../../includes/functions.php';
if (is_admin()) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Invalid CSRF token.';
    } else {
        $user = find_user_by_email($email);
        if ($user && $user['role'] === 'admin' && password_verify($password, $user['password_hash'])) {
            login_user($user);
            header('Location: ' . BASE_URL . 'admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
include __DIR__ . '/header.php';
?>
<div class="row justify-content-center">
  <div class="col-12 col-md-5 col-lg-4">
    <div class="card">
      <div class="card-body">
        <h3>Admin Login</h3>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary" type="submit">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>