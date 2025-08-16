<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Contact';
$success = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Invalid CSRF token.';
    } elseif (!$name || !$email || !$message) {
        $error = 'All fields are required.';
    } else {
        $stmt = db()->prepare('INSERT INTO messages (name, email, message, created_at, is_replied) VALUES (:n,:e,:m,NOW(),0)');
        $stmt->execute([':n' => $name, ':e' => $email, ':m' => $message]);
        send_mail(MAIL_TO_ADMIN, 'New Contact Message', "From: $name <$email>\n\n$message");
        $success = 'Thank you for contacting us!';
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="row">
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-body">
        <h3>Contact</h3>
        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input class="form-control" type="text" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea class="form-control" name="message" rows="5" required></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Send</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>