<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
$stmt = db()->prepare('SELECT b.*,v.name venue_name,p.total_amount,p.deposit_amount,p.deposit_paid,p.payment_status FROM bookings b JOIN venues v ON v.id=b.venue_id JOIN payments p ON p.booking_id=b.id WHERE b.id=:id AND b.client_id=:client');
$stmt->execute(['id' => $id, 'client' => current_user()['id']]);
$booking = $stmt->fetch();
if (!$booking) {
    http_response_code(404);
    exit('Booking not found.');
}
$pageTitle = 'Confirm your booking';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="form-card auth-card">
        <div class="eyebrow">Secure payment · Demonstration mode</div>
        <h1>Confirm your booking</h1>
        <p class="muted"><?= e($booking['venue_name']) ?> · <?= e($booking['reference_id']) ?></p>
        <div class="price-row"><span>Total</span><strong><?= money($booking['total_amount']) ?></strong></div>
        <div class="price-row"><span>Deposit due</span><strong><?= money($booking['deposit_amount']) ?></strong></div>
        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden"
                name="id" value="<?= e((string) $id) ?>">
            <div class="field" style="margin-top:1rem"><label>Payment method</label><select name="method">
                    <option>Card (demonstration)</option>
                    <option>Bank transfer (demonstration)</option>
                    <option>UPI (demonstration)</option>
                </select></div><button class="button primary" style="width:100%;margin-top:1.5rem">Pay deposit and
                confirm booking</button>
        </form>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>