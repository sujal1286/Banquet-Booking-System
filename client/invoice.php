<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$stmt = db()->prepare('SELECT b.*,v.name venue_name,v.address,v.city,u.name client_name,u.email client_email,p.* FROM bookings b JOIN venues v ON v.id=b.venue_id JOIN users u ON u.id=b.client_id LEFT JOIN payments p ON p.booking_id=b.id WHERE b.id=:id AND b.client_id=:client');
$stmt->execute(['id' => $id, 'client' => current_user()['id']]);
$b = $stmt->fetch();
if (!$b) {
    http_response_code(404);
    exit('Invoice not found.');
}
$addons = db()->prepare('SELECT * FROM booking_addons WHERE booking_id=:id');
$addons->execute(['id' => $id]);
$addons = $addons->fetchAll();
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Booking statement <?= e($b['reference_id']) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>

<body>
    <main class="container">
        <div class="section-head">
            <div>
                <div class="eyebrow">GrandVistas booking statement</div>
                <h1><?= e($b['reference_id']) ?></h1>
            </div><button class="button primary" onclick="window.print()">Print / save PDF</button>
        </div>
        <div class="form-card">
            <p><strong><?= e($b['client_name']) ?></strong><br><?= e($b['client_email']) ?></p>
            <p><?= e($b['venue_name']) ?> · <?= e($b['city']) ?><br>Event date: <?= e($b['event_date']) ?></p>
            <hr>
            <div class="price-row"><span>Venue
                    hire</span><strong><?= money($b['subtotal'] - (array_sum(array_column($addons, 'total_price')))) ?></strong>
            </div><?php foreach ($addons as $a): ?>
                <div class="price-row">
                    <span><?= e($a['addon_name']) ?></span><strong><?= money($a['total_price']) ?></strong></div>
            <?php endforeach; ?>
            <div class="price-row"><span>Tax</span><strong><?= money($b['tax_amount'] ?? 0) ?></strong></div>
            <h2 style="text-align:right"><?= money($b['total_amount'] ?? 0) ?></h2>
        </div>
    </main>
</body>

</html>