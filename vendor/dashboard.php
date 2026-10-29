<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/role_check.php';
require_role('vendor');
$stmt = db()->prepare('SELECT * FROM venues WHERE vendor_id=:vendor ORDER BY created_at DESC');
$stmt->execute(['vendor' => current_user()['id']]);
$venues = $stmt->fetchAll();
$bookings = [];
if ($venues) {
    $ids = implode(',', array_map('intval', array_column($venues, 'id')));
    $bookings = db()->query("SELECT b.*,v.name venue_name,u.name client_name FROM bookings b JOIN venues v ON v.id=b.venue_id JOIN users u ON u.id=b.client_id WHERE b.venue_id IN ($ids) ORDER BY b.event_date DESC")->fetchAll();
}
$pageTitle = 'Hall partner portal';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="section-head">
        <div>
            <div class="eyebrow">Hall partner portal</div>
            <h1>Manage your banquet halls with ease.</h1>
            <p class="muted">Keep track of bookings, available dates, and guest requirements.</p>
        </div><a class="button primary" href="<?= e(url('vendor/register_venue.php')) ?>">Add a banquet hall</a>
    </div>
    <div class="grid">
        <div class="card card-body">
            <div class="eyebrow">Your halls</div>
            <h2><?= count($venues) ?></h2>
            <p class="muted">Active listings</p>
        </div>
        <div class="card card-body">
            <div class="eyebrow">Bookings</div>
            <h2><?= count($bookings) ?></h2>
            <p class="muted">Across your halls</p>
        </div>
        <div class="card card-body">
            <div class="eyebrow">Upcoming functions</div>
            <h2><?= count(array_filter($bookings, fn($b) => $b['event_date'] >= date('Y-m-d'))) ?></h2>
            <p class="muted">Functions scheduled</p>
        </div>
    </div>
    <div class="section-head" style="margin-top:3rem">
        <div>
            <div class="eyebrow">Manage each request</div>
            <h2 style="margin-bottom:0">Booking list</h2>
        </div><a class="button outline" href="<?= e(url('vendor/bookings.php')) ?>">Open full list</a>
    </div>
    <div class="form-card"><?php if (!$bookings): ?>
            <p class="muted">No booking requests yet.</p><?php else: ?>
            <div class="booking-list"><?php foreach ($bookings as $booking): ?>
                    <div class="booking-row">
                        <div><strong><?= e($booking['venue_name']) ?></strong>
                            <p class="muted"><?= e($booking['client_name']) ?> ·
                                <?= date('d M Y', strtotime($booking['event_date'])) ?></p>
                        </div><span
                            class="tag status-<?= e($booking['status']) ?>"><?= e(ucfirst($booking['status'])) ?></span><a
                            class="button outline" href="<?= e(url('vendor/bookings.php')) ?>">Change status</a>
                    </div><?php endforeach; ?>
            </div><?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>