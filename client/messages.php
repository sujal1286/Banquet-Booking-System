<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$admins = db()->query("SELECT id,name FROM users WHERE role IN ('admin','vendor') ORDER BY role LIMIT 1")->fetch();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $message = trim($_POST['message'] ?? '');
    if ($message === '' || !$admins)
        $error = 'Please enter a message.';
    else {
        $stmt = db()->prepare('INSERT INTO messages (sender_id,receiver_id,message) VALUES (:sender,:receiver,:message)');
        $stmt->execute(['sender' => current_user()['id'], 'receiver' => $admins['id'], 'message' => $message]);
        flash('success', 'Message sent to your concierge.');
        redirect(url('client/messages.php'));
    }
}
$stmt = db()->prepare('SELECT m.*,u.name sender_name FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.sender_id=:user OR m.receiver_id=:user ORDER BY m.created_at');
$stmt->execute(['user' => current_user()['id']]);
$messages = $stmt->fetchAll();
$pageTitle = 'Messages with our team';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="section-head">
        <div>
            <div class="eyebrow">Personal assistance</div>
            <h1>Your conversation</h1>
        </div><a class="button outline" href="<?= e(url('pages/contact.php')) ?>">Send an enquiry</a>
    </div>
    <div class="form-card"><?php foreach ($messages as $m): ?>
            <p><strong><?= e($m['sender_name']) ?></strong> <span
                    class="muted"><?= date('d M Y H:i', strtotime($m['created_at'])) ?></span><br><?= nl2br(e($m['message'])) ?>
            </p><?php endforeach;
    if (!$messages): ?>
            <p class="muted">Start a conversation with your concierge.</p><?php endif; ?>
        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="field"><label>Message</label><textarea name="message" rows="3" required></textarea></div><button
                class="button primary">Send message</button>
        </form>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>