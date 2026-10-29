<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$stmt = db()->prepare('SELECT * FROM password_resets WHERE token_hash=:hash AND used_at IS NULL AND expires_at>NOW() ORDER BY id DESC LIMIT 1');
$stmt->execute(['hash' => hash('sha256', $token)]);
$reset = $stmt->fetch();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = (string) ($_POST['password'] ?? '');
    if (!$reset || strlen($password) < 8)
        $error = 'This reset link is invalid or the password is too short.';
    else {
        $up = db()->prepare('UPDATE users SET password=:password WHERE id=:id');
        $up->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $reset['user_id']]);
        $done = db()->prepare('UPDATE password_resets SET used_at=NOW() WHERE id=:id');
        $done->execute(['id' => $reset['id']]);
        flash('success', 'Password updated. Please sign in.');
        redirect(url('auth/login.php'));
    }
}
$pageTitle = 'Choose new password';
require __DIR__ . '/../includes/header.php';
?>
<main class="auth-wrap">
    <div class="form-card auth-card">
        <div class="eyebrow">New credentials</div>
        <h1>Choose a new password</h1><?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden"
                name="token" value="<?= e($token) ?>">
            <div class="field"><label>New password</label><input name="password" type="password" minlength="8" required>
            </div><button class="button primary" style="margin-top:1rem">Update password</button>
        </form>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>