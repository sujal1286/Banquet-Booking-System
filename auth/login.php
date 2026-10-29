<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if (current_user())
    redirect(url('client/dashboard.php'));
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare("SELECT id,name,email,password,role FROM users WHERE email = :email ORDER BY (role = 'client') DESC, id DESC LIMIT 1");
    $stmt->execute(['email' => strtolower(trim((string) ($_POST['email'] ?? '')))]);
    $user = $stmt->fetch();
    if ($user && password_verify((string) ($_POST['password'] ?? ''), $user['password'])) {
        unset($user['password']);
        $_SESSION['user'] = $user;
        session_regenerate_id(true);
        redirect(url($user['role'] === 'admin' ? 'admin/dashboard.php' : ($user['role'] === 'vendor' ? 'vendor/dashboard.php' : 'client/dashboard.php')));
    }
    $error = 'The email or password does not match our records.';
}
$pageTitle = 'Log in';
require __DIR__ . '/../includes/header.php';
?>
<main class="auth-wrap">
    <div class="form-card auth-card">
        <div class="eyebrow">Welcome back</div>
        <h1>Log in to GrandVistas</h1><?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email"
                    required autocomplete="email"></div>
            <div class="field" style="margin-top:1rem"><label for="password">Password</label><input id="password"
                    name="password" type="password" required autocomplete="current-password"></div><button
                class="button primary" style="width:100%;margin-top:1.5rem" type="submit">Log in</button>
        </form>
        <p class="muted"><a href="<?= e(url('auth/forgot_password.php')) ?>"><u>Forgot password?</u></a></p>
        <p class="muted">New to GrandVistas? <a href="<?= e(url('auth/register.php')) ?>"><u>Create your account</u></a>
        </p>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>