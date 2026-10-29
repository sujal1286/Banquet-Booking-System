<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/role_check.php';
require_role('vendor');
$ids = db()->prepare('SELECT id FROM venues WHERE vendor_id=:id');
$ids->execute(['id' => current_user()['id']]);
$venueIds = array_column($ids->fetchAll(), 'id');
$bookings = [];
if ($venueIds) {
    $list = implode(',', array_map('intval', $venueIds));
    $bookings = db()->query("SELECT b.*,v.name venue_name,u.name client_name FROM bookings b JOIN venues v ON v.id=b.venue_id JOIN users u ON u.id=b.client_id WHERE b.venue_id IN ($list) ORDER BY b.event_date")->fetchAll();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) $_POST['booking_id'];
    $status = in_array($_POST['status'], ['approved', 'confirmed', 'cancelled'], true) ? $_POST['status'] : 'pending';
    $old = db()->prepare('SELECT b.status FROM bookings b JOIN venues v ON v.id=b.venue_id WHERE b.id=:id AND v.vendor_id=:vendor');
    $old->execute(['id' => $id, 'vendor' => current_user()['id']]);
    $oldStatus = $old->fetchColumn();
    $up = db()->prepare("UPDATE bookings b JOIN venues v ON v.id=b.venue_id SET b.status=:status WHERE b.id=:id AND v.vendor_id=:vendor");
    $up->execute(['status' => $status, 'id' => $id, 'vendor' => current_user()['id']]);
    $emailSent = $oldStatus !== false && $oldStatus !== $status ? notify_booking_status($id, $status) : true;
    if ($emailSent) {
        flash('success', 'Booking status updated. The client has been notified.');
    } else {
        flash('error', 'Booking status updated, but the client email could not be sent. Check the project .env SMTP settings.');
    }
    redirect(url('vendor/bookings.php'));
}
$pageTitle = 'Vendor bookings';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="section-head">
        <div>
            <div class="eyebrow">Hall partner portal</div>
            <h1>Booking list</h1>
            <p class="muted">Review each function and update its status from the action panel.</p>
        </div><a class="button outline" href="<?= e(url('vendor/dashboard.php')) ?>">Back to partner portal</a>
    </div><?php if (!$bookings): ?>
        <div class="empty">
            <p>No booking requests yet.</p>
        </div><?php else: ?>
        <div class="booking-list"><?php foreach ($bookings as $b): ?>
                <article class="booking-card">
                    <div class="booking-card-head">
                        <div>
                            <div class="eyebrow"><?= e($b['venue_name']) ?></div>
                            <h3><?= e($b['client_name']) ?></h3>
                            <p class="muted"><?= e($b['event_type']) ?> · <?= date('d M Y', strtotime($b['event_date'])) ?> ·
                                <?= e((string) $b['guest_count']) ?> guests</p>
                        </div><span class="tag status-<?= e($b['status']) ?>"><?= e(ucfirst($b['status'])) ?></span>
                    </div>
                    <form class="status-editor" method="post"><input type="hidden" name="csrf"
                            value="<?= e(csrf_token()) ?>"><input type="hidden" name="booking_id"
                            value="<?= e((string) $b['id']) ?>"><label for="status-<?= e((string) $b['id']) ?>">Update booking
                            status</label>
                        <div><select id="status-<?= e((string) $b['id']) ?>" name="status">
                                <option value="pending" <?= $b['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $b['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="confirmed" <?= $b['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="cancelled" <?= $b['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select><button class="button primary" type="submit">Save status</button></div>
                    </form>
                </article><?php endforeach; ?>
        </div><?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>