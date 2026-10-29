<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '')
        $error = 'Please complete all fields.';
    else {
        $stmt = db()->prepare('INSERT INTO enquiries (user_id,name,email,message) VALUES (:user,:name,:email,:message)');
        $stmt->execute(['user' => current_user()['id'] ?? null, 'name' => $name, 'email' => $email, 'message' => $message]);
        flash('success', 'Thank you. A concierge will respond shortly.');
        redirect(url('pages/contact.php'));
    }
}
$pageTitle = 'Contact GrandVistas';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="form-card auth-card">
        <div class="eyebrow">We are here to help</div>
        <h1>Contact GrandVistas</h1><?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="field"><label>Name</label><input name="name" value="<?= e(current_user()['name'] ?? '') ?>"
                    required></div>
            <div class="field"><label>Email</label><input name="email" type="email"
                    value="<?= e(current_user()['email'] ?? '') ?>" required></div>
            <div class="field"><label>How may we assist you?</label><textarea name="message" rows="6"
                    required></textarea></div><button class="button primary">Send your enquiry</button>
        </form>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>