<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid CSRF token.'];
        redirect(base_url('contact'));
    }
    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $message) {
        db_query('INSERT INTO messages (name, email, subject, message) VALUES (:n,:e,:s,:m)', [
            ':n'=>$name, ':e'=>$email, ':s'=>$subject, ':m'=>$message
        ]);
        // Optional email via SMTP or mail() fallback
        @mail($email, '['.SITE_NAME.'] Message received', "Thanks for contacting us! We'll get back soon.");
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Message sent.'];
        redirect(base_url('contact'));
    } else {
        $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Please fill all required fields correctly.'];
        redirect(base_url('contact'));
    }
}
?>
<div class="row g-3">
  <div class="col-12 col-lg-8">
    <div class="cm-card p-3">
      <h1 class="h5 mb-3">Contact Us</h1>
      <form method="post">
        <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="col-12">
            <label class="form-label">Subject</label>
            <input class="form-control" name="subject" required>
          </div>
          <div class="col-12">
            <label class="form-label">Message</label>
            <textarea class="form-control" rows="5" name="message" required></textarea>
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-gradient">Send</button>
        </div>
      </form>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="cm-card p-3 h-100">
      <h2 class="h6">Support</h2>
      <p class="text-muted small">We usually respond within 1-2 business days.</p>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>