<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = db()->prepare("SELECT * FROM venues WHERE id = :id AND status = 'active'");
$stmt->execute(['id' => $id ?: 0]);
$venue = $stmt->fetch();
if (!$venue) {
    http_response_code(404);
    exit('Venue not found.');
}
$pageTitle = $venue['name'];
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="two-col">
        <section>
            <div class="venue-image"
                style="height:420px;background-image:url('<?= e(image_url($venue['cover_image'])) ?>')"></div>
            <div class="eyebrow" style="margin-top:2rem"><?= e($venue['type']) ?> · <?= e($venue['city']) ?></div>
            <h1><?= e($venue['name']) ?></h1>
            <p class="muted"><?= e($venue['description']) ?></p>
            <div class="card-meta"><span>Capacity <?= e((string) $venue['max_capacity']) ?></span><span>★
                    <?= e((string) $venue['rating']) ?> (<?= e((string) $venue['review_count']) ?>
                    reviews)</span><span><?= e($venue['square_footage']) ?> sq ft</span></div>
        </section>
        <aside class="price-box">
            <div class="eyebrow">Book this hall</div>
            <h2 style="color:#fff"><?= money($venue['base_rate']) ?></h2>
            <p>Starting hall rate per function. Catering, decor, and other arrangements can be selected during booking.
            </p>
            <form method="get" action="<?= e(url('pages/checkout.php')) ?>"><input type="hidden" name="venue_id"
                    value="<?= e((string) $venue['id']) ?>">
                <div class="field"><label for="event_date">Preferred function date</label><input id="event_date"
                        name="event_date" type="date" min="<?= date('Y-m-d') ?>" required></div><button
                    class="button gold" style="width:100%;margin-top:1rem" type="submit">Start booking →</button>
            </form>
        </aside>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>