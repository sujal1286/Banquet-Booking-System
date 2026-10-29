<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$stmt = db()->prepare('SELECT b.*,v.name venue_name,v.city,p.total_amount,p.payment_status FROM bookings b JOIN venues v ON v.id=b.venue_id LEFT JOIN payments p ON p.booking_id=b.id WHERE b.client_id=:client ORDER BY b.event_date DESC');
$stmt->execute(['client' => current_user()['id']]);
$bookings = $stmt->fetchAll();
$pageTitle = 'My celebrations';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="section-head">
        <div>
            <div class="eyebrow">My celebrations</div>
            <h1>Welcome back, <?= e(current_user()['name']) ?>.</h1>
            <p class="muted">Your function details and booking requests, all in one place.</p>
        </div>
        <div><a class="button primary" href="<?= e(url('pages/venues.php')) ?>">Book a hall</a></div>
    </div>
    <h2>Your bookings</h2><?php if (!$bookings): ?>
        <div class="empty">
            <p>You have no bookings yet.</p><a class="button outline" href="<?= e(url('pages/venues.php')) ?>">Browse
                banquet halls</a>
        </div><?php else: ?>
        <div class="grid"><?php foreach ($bookings as $booking): ?>
                <article class="card">
                    <div class="card-body"><span class="tag"><?= e($booking['status']) ?></span>
                        <h3><?= e($booking['event_title']) ?></h3>
                        <p class="muted"><?= e($booking['venue_name']) ?> ·
                            <?= date('d M Y', strtotime($booking['event_date'])) ?></p>
                        <div class="card-meta"><span><?= e((string) $booking['guest_count']) ?>
                                guests</span><strong><?= money($booking['total_amount'] ?? 0) ?></strong></div><a
                            class="button outline" style="margin-top:1rem"
                            href="<?= e(url('client/booking_detail.php?id=' . $booking['id'])) ?>">View reservation</a>
                    </div>
                </article><?php endforeach; ?>
        </div><?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>