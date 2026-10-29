<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/role_check.php';
require_role('vendor');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('UPDATE venues SET base_rate=:base,peak_rate=:peak,security_deposit=:deposit WHERE id=:id AND vendor_id=:vendor');
    $stmt->execute(['base' => (float) $_POST['base_rate'], 'peak' => (float) $_POST['peak_rate'], 'deposit' => (float) $_POST['security_deposit'], 'id' => (int) $_POST['venue_id'], 'vendor' => current_user()['id']]);
    flash('success', 'Rates updated.');
}
$stmt = db()->prepare('SELECT * FROM venues WHERE vendor_id=:id');
$stmt->execute(['id' => current_user()['id']]);
$venues = $stmt->fetchAll();
$pageTitle = 'Hall rates';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="eyebrow">Hall partner portal</div>
    <h1>Hall rates and deposits</h1>
    <div class="grid"><?php foreach ($venues as $v): ?>
            <form class="form-card" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input
                    type="hidden" name="venue_id" value="<?= e((string) $v['id']) ?>">
                <h3><?= e($v['name']) ?></h3>
                <div class="field"><label>Base rate</label><input name="base_rate" type="number"
                        value="<?= e((string) $v['base_rate']) ?>"></div>
                <div class="field"><label>Peak rate</label><input name="peak_rate" type="number"
                        value="<?= e((string) $v['peak_rate']) ?>"></div>
                <div class="field"><label>Security deposit</label><input name="security_deposit" type="number"
                        value="<?= e((string) $v['security_deposit']) ?>"></div><button class="button primary"
                    style="margin-top:1rem">Save rates</button>
            </form><?php endforeach; ?>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>