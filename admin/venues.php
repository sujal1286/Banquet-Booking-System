<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/role_check.php';
require_role('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('UPDATE venues SET status=:status WHERE id=:id');
    $stmt->execute(['status' => in_array($_POST['status'], ['active', 'maintenance', 'inactive'], true) ? $_POST['status'] : 'active', 'id' => (int) $_POST['venue_id']]);
    flash('success', 'Banquet hall status updated.');
    redirect(url('admin/venues.php'));
}
$venues = db()->query('SELECT v.*,u.name vendor_name FROM venues v LEFT JOIN users u ON u.id=v.vendor_id ORDER BY v.created_at DESC')->fetchAll();
$pageTitle = 'Banquet hall management';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="eyebrow">GrandVistas administration</div>
    <h1>Banquet hall status</h1>
    <div class="form-card" style="overflow:auto">
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <th align="left">Venue</th>
                <th align="left">Partner</th>
                <th align="left">City</th>
                <th align="left">Status</th>
                <th></th>
            </tr><?php foreach ($venues as $v): ?>
                <tr>
                    <td><?= e($v['name']) ?></td>
                    <td><?= e($v['vendor_name'] ?? 'GrandVistas') ?></td>
                    <td><?= e($v['city']) ?></td>
                    <td>
                        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input
                                type="hidden" name="venue_id" value="<?= e((string) $v['id']) ?>"><select name="status">
                                <option <?= $v['status'] === 'active' ? 'selected' : '' ?>>active</option>
                                <option <?= $v['status'] === 'maintenance' ? 'selected' : '' ?>>maintenance</option>
                                <option <?= $v['status'] === 'inactive' ? 'selected' : '' ?>>inactive</option>
                            </select><button class="button">Save</button></form>
                    </td>
                </tr><?php endforeach; ?>
        </table>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>