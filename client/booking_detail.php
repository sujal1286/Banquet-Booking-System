<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$stmt = db()->prepare('SELECT b.*,v.name venue_name,v.city,v.address,v.cover_image,p.* FROM bookings b JOIN venues v ON v.id=b.venue_id LEFT JOIN payments p ON p.booking_id=b.id WHERE b.id=:id AND b.client_id=:client');
$stmt->execute(['id' => $id, 'client' => current_user()['id']]);
$booking = $stmt->fetch();
if (!$booking) {
    http_response_code(404);
    exit('Booking not found.');
}
$pageTitle = 'Booking ' . $booking['reference_id'];
require __DIR__ . '/../includes/header.php';
?>
<main class="container"><a class="muted" href="<?= e(url('client/dashboard.php')) ?>">← Back to my celebrations</a>
    <div class="two-col" style="margin-top:1.5rem">
        <section class="form-card">
            <div class="venue-image"
                style="height:220px;background-image:url('<?= e(image_url($booking['cover_image'])) ?>')"></div><span
                class="tag"><?= e($booking['status']) ?></span>
            <h1><?= e($booking['event_title']) ?></h1>
            <p class="muted"><?= e($booking['reference_id']) ?> · <?= e($booking['event_type']) ?></p>
            <div class="card-meta">
                <span><?= e($booking['venue_name']) ?></span><span><?= date('d M Y', strtotime($booking['event_date'])) ?></span><span><?= e((string) $booking['guest_count']) ?>
                    guests</span></div>
            <h3 style="margin-top:2rem">Booking journey</h3>
            <p><strong>Request received</strong><br><span
                    class="muted"><?= date('d M Y, H:i', strtotime($booking['created_at'])) ?></span></p>
            <p><strong>Next step</strong><br><span class="muted">Our celebration team will review your request and
                    contact you with availability and arrangements.</span></p>
        </section>
        <aside class="price-box">
            <div class="eyebrow">Payment summary</div>
            <div class="price-row"><span>Hall hire</span><strong><?= money($booking['subtotal'] ?? 0) ?></strong></div>
            <div class="price-row"><span>Deposit
                    requested</span><strong><?= money($booking['deposit_amount'] ?? 0) ?></strong></div>
            <p style="color:#d9e3dd">Payment status: <?= e($booking['payment_status'] ?? 'pending') ?></p>
        </aside>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>