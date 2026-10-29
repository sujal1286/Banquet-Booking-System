<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/role_check.php';
require_role('vendor');
$venues = db()->prepare('SELECT * FROM venues WHERE vendor_id=:id');
$venues->execute(['id' => current_user()['id']]);
$venues = $venues->fetchAll();
$venueId = (int) ($_GET['venue_id'] ?? ($venues[0]['id'] ?? 0));
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $venueId = (int) $_POST['venue_id'];
    $date = $_POST['blocked_date'] ?? '';
    if ($date < date('Y-m-d'))
        $error = 'Choose a future date.';
    else {
        $stmt = db()->prepare('INSERT INTO availability (venue_id,blocked_date,block_type) VALUES (:venue,:date,:type) ON DUPLICATE KEY UPDATE block_type=VALUES(block_type)');
        $stmt->execute(['venue' => $venueId, 'date' => $date, 'type' => $_POST['block_type'] ?? 'blackout']);
        flash('success', 'Availability updated.');
        redirect(url('vendor/availability.php?venue_id=' . $venueId));
    }
}
$stmt = db()->prepare('SELECT * FROM availability WHERE venue_id=:venue AND blocked_date>=CURDATE() ORDER BY blocked_date');
$stmt->execute(['venue' => $venueId]);
$blocks = $stmt->fetchAll();
$pageTitle = 'Availability';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="section-head">
        <div>
            <div class="eyebrow">Hall partner portal</div>
            <h1>Available dates</h1>
        </div><a class="button outline" href="<?= e(url('vendor/dashboard.php')) ?>">Back to partner portal</a>
    </div><?php if ($error): ?>
        <div class="notice error"><?= e($error) ?></div><?php endif; ?>
    <div class="two-col">
        <form class="form-card" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="field"><label>Venue</label><select name="venue_id"><?php foreach ($venues as $venue): ?>
                        <option value="<?= e((string) $venue['id']) ?>" <?= $venueId === $venue['id'] ? 'selected' : '' ?>>
                            <?= e($venue['name']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="field" style="margin-top:1rem"><label>Blocked date</label><input name="blocked_date" type="date"
                    min="<?= date('Y-m-d') ?>" required></div>
            <div class="field" style="margin-top:1rem"><label>Reason</label><select name="block_type">
                    <option value="blackout">Private blackout</option>
                    <option value="maintenance">Maintenance</option>
                </select></div><button class="button primary" style="margin-top:1.5rem">Block date</button>
        </form>
        <div class="form-card">
            <h3>Upcoming blocks</h3><?php foreach ($blocks as $block): ?>
                <p><strong><?= date('d M Y', strtotime($block['blocked_date'])) ?></strong><br><span
                        class="muted"><?= e($block['block_type']) ?></span></p><?php endforeach;
            if (!$blocks): ?>
                <p class="muted">No blocked dates.</p><?php endif; ?>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>