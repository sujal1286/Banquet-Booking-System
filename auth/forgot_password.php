<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('SELECT id FROM users WHERE email=:email');
    $stmt->execute(['email' => strtolower(trim($_POST['email'] ?? ''))]);
    $user = $stmt->fetch();
    if ($user) {
        $token = bin2hex(random_bytes(24));
        $save = db()->prepare('INSERT INTO password_resets (user_id,token_hash,expires_at) VALUES (:user,:hash,DATE_ADD(NOW(),INTERVAL 1 HOUR))');
        $save->execute(['user' => $user['id'], 'hash' => hash('sha256', $token)]);
        $message = 'Reset link generated for this demo: ' . url('auth/reset_password.php?token=' . $token);
    } else {
        $message = 'If that email exists, a reset link has been generated.';
    }
}
$pageTitle = 'Reset password';
require __DIR__ . '/../includes/header.php';
?>
<main class="auth-wrap">
    <div class="form-card auth-card">
        <div class="eyebrow">Account access</div>
        <h1>Reset your password</h1><?php if ($message): ?>
            <div class="notice success"><?= e($message) ?></div><?php endif; ?>
        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="field"><label>Email address</label><input name="email" type="email" required></div><button
                class="button primary" style="margin-top:1rem">Generate reset link</button>
        </form>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>