<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$venueId = filter_input(INPUT_GET, 'venue_id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'venue_id', FILTER_VALIDATE_INT);
$stmt = db()->prepare("SELECT v.*,u.email vendor_email FROM venues v LEFT JOIN users u ON u.id=v.vendor_id WHERE v.id = :id AND v.status = 'active'");
$stmt->execute(['id' => $venueId ?: 0]);
$venue = $stmt->fetch();
if (!$venue) {
    http_response_code(404);
    exit('Venue not found.');
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $date = (string) ($_POST['event_date'] ?? '');
    $guests = (int) ($_POST['guest_count'] ?? 0);
    if ($date < date('Y-m-d') || $guests < 1 || $guests > (int) $venue['max_capacity'])
        $error = 'Please choose a future date and a guest count within the venue capacity.';
    else {
        $busy = db()->prepare("SELECT COUNT(*) FROM bookings WHERE venue_id=:venue AND event_date=:date AND status IN ('pending','approved','confirmed')");
        $busy->execute(['venue' => $venue['id'], 'date' => $date]);
        if ((int) $busy->fetchColumn() > 0)
            $error = 'That date is already reserved. Please choose another date.';
        else {
            $reference = 'GV-' . strtoupper(bin2hex(random_bytes(3)));
            $insert = db()->prepare("INSERT INTO bookings (reference_id,client_id,venue_id,event_title,event_type,event_date,start_time,end_time,guest_count,time_slot,status) VALUES (:ref,:client,:venue,:title,:type,:date,:start,:end,:guests,'evening','pending')");
            $insert->execute(['ref' => $reference, 'client' => current_user()['id'], 'venue' => $venue['id'], 'title' => trim((string) $_POST['event_title']), 'type' => trim((string) $_POST['event_type']), 'date' => $date, 'start' => '17:00:00', 'end' => '23:00:00', 'guests' => $guests]);
            $bookingId = (int) db()->lastInsertId();
            $subtotal = (float) $venue['base_rate'];
            $addons = ['Signature catering' => [2500, 'catering'], 'Floral styling' => [35000, 'decor'], 'AV suite' => [18000, 'service']];
            foreach ($addons as $name => $addon) {
                if (!empty($_POST['addons'][$name])) {
                    $quantity = max(1, (int) $_POST['addons'][$name]);
                    $total = $addon[0] * $quantity;
                    $add = db()->prepare('INSERT INTO booking_addons (booking_id,addon_name,addon_type,price_per_unit,quantity,total_price) VALUES (:booking,:name,:type,:price,:quantity,:total)');
                    $add->execute(['booking' => $bookingId, 'name' => $name, 'type' => $addon[1], 'price' => $addon[0], 'quantity' => $quantity, 'total' => $total]);
                    $subtotal += $total;
                }
            }
            $tax = round($subtotal * .18, 2);
            $payment = db()->prepare('INSERT INTO payments (booking_id,subtotal,tax_rate,tax_amount,total_amount,deposit_amount,deposit_paid,payment_status) VALUES (:booking,:subtotal,18,:tax,:total,:deposit,0,"pending")');
            $payment->execute(['booking' => $bookingId, 'subtotal' => $subtotal, 'tax' => $tax, 'total' => $subtotal + $tax, 'deposit' => $subtotal * .3]);
            $emailSent = notify_new_booking($bookingId);
            flash('success', "Booking request {$reference} sent. We will be in touch shortly." . ($emailSent ? '' : ' The hall partner will be notified through the portal.'));
            redirect(url('client/dashboard.php'));
        }
    }
}
$pageTitle = 'Plan your function';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="two-col">
        <section class="form-card">
            <div class="eyebrow">Steps 1–2 · Plan your function</div>
            <h1>Plan your function</h1>
            <p class="muted"><?= e($venue['name']) ?> · <?= money($venue['base_rate']) ?> starting hall rate</p>
            <?php if ($error): ?>
                <div class="notice error"><?= e($error) ?></div><?php endif; ?>
            <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden"
                    name="venue_id" value="<?= e((string) $venue['id']) ?>">
                <div class="form-grid">
                    <div class="field full"><label for="event_title">Function name</label><input id="event_title"
                            name="event_title" placeholder="e.g. Aarav and Meera's wedding reception" required></div>
                    <div class="field"><label for="event_type">Function type</label><select id="event_type"
                            name="event_type">
                            <option>Wedding</option>
                            <option>Corporate gathering</option>
                            <option>Family celebration</option>
                            <option>Reception</option>
                        </select></div>
                    <div class="field"><label for="event_date">Function date</label><input id="event_date"
                            name="event_date" type="date" min="<?= date('Y-m-d') ?>"
                            value="<?= e($_GET['event_date'] ?? '') ?>" required></div>
                    <div class="field full"><label for="guest_count">Expected guests (maximum
                            <?= e((string) $venue['max_capacity']) ?>)</label><input id="guest_count" name="guest_count"
                            type="number" min="1" max="<?= e((string) $venue['max_capacity']) ?>" required></div>
                    <div class="field full"><label>Additional arrangements</label><label class="addon-option"><input
                                type="checkbox" name="addons[Signature catering]" value="1"><span>Traditional and
                                contemporary catering — <?= money(2500) ?></span></label><label
                            class="addon-option"><input type="checkbox" name="addons[Floral styling]"
                                value="1"><span>Floral and mandap decor — <?= money(35000) ?></span></label><label
                            class="addon-option"><input type="checkbox" name="addons[AV suite]" value="1"><span>Sound
                                and presentation setup — <?= money(18000) ?></span></label></div>
                </div><button class="button primary" style="margin-top:1.5rem" type="submit">Send booking request
                    →</button>
            </form>
        </section>
        <aside class="price-box">
            <div class="eyebrow">Booking summary</div>
            <h2 style="color:#fff"><?= e($venue['name']) ?></h2>
            <div class="price-row"><span>Hall hire</span><strong><?= money($venue['base_rate']) ?></strong></div>
            <div class="price-row"><span>Security
                    deposit</span><strong><?= money($venue['security_deposit']) ?></strong></div>
            <p class="muted" style="color:#d9e3dd">No payment is taken until the hall partner reviews and approves your
                booking request.</p>
        </aside>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>