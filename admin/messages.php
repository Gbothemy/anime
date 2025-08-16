<?php
require_once __DIR__ . '/includes/admin_auth.php';
$action = $_GET['action'] ?? 'list';

if ($action === 'reply' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('Invalid CSRF');
    $id = (int)$_GET['id'];
    $msg = db()->prepare('SELECT * FROM messages WHERE id=:id');
    $msg->execute([':id'=>$id]);
    $row = $msg->fetch();
    if ($row) {
        $reply = trim($_POST['reply']);
        send_mail($row['email'], 'Re: Your message to ' . SITE_NAME, $reply);
        db()->prepare('UPDATE messages SET is_replied=1 WHERE id=:id')->execute([':id'=>$id]);
    }
    header('Location: messages.php'); exit;
}

include __DIR__ . '/includes/header.php';
$rows = db()->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();
?>
<h3>Messages</h3>
<div class="table-responsive">
<table class="table table-dark table-striped align-middle">
  <thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Date</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $m): ?>
    <tr>
      <td><?php echo htmlspecialchars($m['name']); ?></td>
      <td><?php echo htmlspecialchars($m['email']); ?></td>
      <td style="max-width: 420px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;"><?php echo htmlspecialchars($m['message']); ?></td>
      <td><?php echo htmlspecialchars($m['created_at']); ?></td>
      <td><?php echo $m['is_replied'] ? 'Replied' : 'Pending'; ?></td>
      <td class="text-end">
        <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#r<?php echo (int)$m['id']; ?>">Reply</button>
      </td>
    </tr>
    <tr class="collapse" id="r<?php echo (int)$m['id']; ?>">
      <td colspan="6">
        <form method="post" action="?action=reply&id=<?php echo (int)$m['id']; ?>">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <div class="input-group">
            <textarea class="form-control" name="reply" rows="3" placeholder="Write a reply..."></textarea>
            <button class="btn btn-success" type="submit">Send</button>
          </div>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>