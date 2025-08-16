<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_id'])) {
    if (!verify_csrf($_POST['csrf'] ?? '')) { $_SESSION['flash']=['type'=>'danger','msg'=>'Invalid CSRF']; redirect(base_url('admin/messages.php')); }
    $id = (int)$_POST['reply_id'];
    $reply = trim($_POST['reply_text'] ?? '');
    if ($reply) {
        db_query('UPDATE messages SET status=\'replied\', reply_text=:r, replied_at=NOW() WHERE id=:id', [':r'=>$reply, ':id'=>$id]);
        $msg = db_query('SELECT email, subject FROM messages WHERE id=:id', [':id'=>$id])->fetch();
        if ($msg) { @mail($msg['email'], '['.SITE_NAME.'] Re: '.$msg['subject'], $reply); }
        $_SESSION['flash']=['type'=>'success','msg'=>'Reply sent'];
    }
    redirect(base_url('admin/messages.php'));
}

$rows = db_query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();
?>
<div class="cm-card p-3">
  <h1 class="h5 mb-3">Messages</h1>
  <div class="list-group list-group-flush">
    <?php foreach ($rows as $m): ?>
      <div class="list-group-item">
        <div class="d-flex justify-content-between">
          <div>
            <div class="fw-semibold"><?php echo e($m['subject']); ?></div>
            <div class="text-muted small mb-2"><?php echo e($m['name']); ?> • <?php echo e($m['email']); ?> • <?php echo e($m['created_at']); ?></div>
            <div class="mb-2"><?php echo nl2br(e($m['message'])); ?></div>
            <?php if ($m['status'] === 'replied'): ?>
              <div class="border rounded p-2 bg-dark-subtle small">Admin Reply: <?php echo nl2br(e($m['reply_text'])); ?></div>
            <?php endif; ?>
          </div>
          <div class="text-end">
            <span class="badge bg-<?php echo $m['status']==='replied'?'success':'secondary'; ?>"><?php echo e($m['status']); ?></span>
          </div>
        </div>
        <?php if ($m['status'] !== 'replied'): ?>
        <form class="mt-2" method="post">
          <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
          <input type="hidden" name="reply_id" value="<?php echo (int)$m['id']; ?>">
          <div class="row g-2">
            <div class="col-12">
              <textarea class="form-control" rows="2" name="reply_text" placeholder="Write a reply..."></textarea>
            </div>
            <div class="col-12 text-end">
              <button class="btn btn-sm btn-gradient">Send Reply</button>
            </div>
          </div>
        </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="list-group-item">No messages.</div><?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>