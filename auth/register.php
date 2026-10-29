<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8)
        $error = 'Enter a name, valid email, and password of at least 8 characters.';
    else
        try {
            $role = ($_POST['role'] ?? 'client') === 'vendor' ? 'vendor' : 'client';
            $stmt = db()->prepare('INSERT INTO users (name,email,password,phone,role) VALUES (:name,:email,:password,:phone,:role)');
            $stmt->execute(['name' => $name, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT), 'phone' => trim((string) ($_POST['phone'] ?? '')), 'role' => $role]);
            flash('success', $role === 'vendor' ? 'Your hall partner account is ready. Please log in.' : 'Your account is ready. Please log in.');
            redirect(url('auth/login.php'));
        } catch (PDOException $exception) {
            $error = $exception->getCode() === '23000' ? 'That email is already registered.' : 'Unable to create the account right now.';
        }
}
$pageTitle = 'Create your account';
require __DIR__ . '/../includes/header.php';
?>
<main class="auth-wrap">
    <div class="form-card auth-card">
        <div class="eyebrow">Begin your celebration journey</div>
        <h1>Create your account</h1><?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="field"><label for="name">Full name</label><input id="name" name="name" required></div>
            <div class="field" style="margin-top:1rem"><label for="email">Email address</label><input id="email"
                    name="email" type="email" required></div>
            <div class="field" style="margin-top:1rem"><label for="phone">Mobile number (optional)</label><input
                    id="phone" name="phone" type="tel"></div>
            <div class="field" style="margin-top:1rem"><label for="role">I am joining as</label><select id="role"
                    name="role">
                    <option value="client">Celebration host</option>
                    <option value="vendor">Banquet hall partner</option>
                </select></div>
            <div class="field" style="margin-top:1rem"><label for="password">Password</label><input id="password"
                    name="password" type="password" minlength="8" required></div><button class="button primary"
                style="width:100%;margin-top:1.5rem" type="submit">Create account</button>
        </form>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>