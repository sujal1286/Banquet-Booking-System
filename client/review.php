<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
$stmt = db()->prepare("SELECT b.*,v.name venue_name FROM bookings b JOIN venues v ON v.id=b.venue_id WHERE b.id=:id AND b.client_id=:client AND b.status='completed'");
$stmt->execute(['id' => $id, 'client' => current_user()['id']]);
$booking = $stmt->fetch();
if (!$booking) {
    http_response_code(404);
    exit('Completed booking not found.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('INSERT INTO reviews (booking_id,client_id,venue_id,rating,comment) VALUES (:booking,:client,:venue,:rating,:comment)');
    $stmt->execute(['booking' => $id, 'client' => current_user()['id'], 'venue' => $booking['venue_id'], 'rating' => max(1, min(5, (int) $_POST['rating'])), 'comment' => trim($_POST['comment'])]);
    $avg = db()->prepare('UPDATE venues v SET rating=(SELECT AVG(rating) FROM reviews WHERE venue_id=v.id),review_count=(SELECT COUNT(*) FROM reviews WHERE venue_id=v.id) WHERE v.id=:id');
    $avg->execute(['id' => $booking['venue_id']]);
    flash('success', 'Thank you for sharing your experience.');
    redirect(url('client/dashboard.php'));
}
$pageTitle = 'Review venue';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="form-card auth-card">
        <div class="eyebrow">Your celebration experience</div>
        <h1>Share your experience of <?= e($booking['venue_name']) ?></h1>
        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden"
                name="id" value="<?= e((string) $id) ?>">
            <div class="field"><label>Rating</label><select name="rating">
                    <option value="5">5 — Exceptional</option>
                    <option value="4">4 — Beautiful</option>
                    <option value="3">3 — Good</option>
                    <option value="2">2 — Needs attention</option>
                    <option value="1">1 — Disappointing</option>
                </select></div>
            <div class="field"><label>Your feedback</label><textarea name="comment" rows="5" required></textarea></div>
            <button class="button primary">Share feedback</button>
        </form>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>