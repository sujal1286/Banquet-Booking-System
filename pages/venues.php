<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Find your banquet hall';
$where = ["status = 'active'"];
$params = [];
if (!empty($_GET['type'])) {
    $where[] = 'type = :type';
    $params['type'] = $_GET['type'];
}
if (!empty($_GET['capacity']) && ctype_digit((string) $_GET['capacity'])) {
    $where[] = 'max_capacity >= :capacity';
    $params['capacity'] = (int) $_GET['capacity'];
}
$query = 'SELECT * FROM venues WHERE ' . implode(' AND ', $where) . ' ORDER BY rating DESC';
$stmt = db()->prepare($query);
$stmt->execute($params);
$venues = $stmt->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="section-head">
        <div>
            <div class="eyebrow">The GrandVistas selection</div>
            <h1>Find the right hall for your occasion</h1>
            <p class="muted">Choose from welcoming banquet halls selected for their location, service, comfort, and
                celebration-ready facilities.</p>
        </div>
    </div>
    <form class="search-panel" method="get">
        <div class="field"><label for="type">Hall type</label><select id="type" name="type">
                <option value="">All hall types</option>
                <?php foreach (['Ballroom', 'Garden', 'Pavilion', 'Heritage', 'Estate'] as $type): ?>
                    <option <?= ($_GET['type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </select></div>
        <div class="field"><label for="capacity">Number of guests</label><input id="capacity" name="capacity"
                type="number" min="1" placeholder="Any capacity" value="<?= e($_GET['capacity'] ?? '') ?>"></div>
        <div class="field"><label>&nbsp;</label><button class="button primary" type="submit">Find halls</button>
        </div>
        <div class="field"><label>&nbsp;</label><a class="button" href="<?= e(url('pages/venues.php')) ?>">Clear
                filters</a></div>
    </form>
    <div class="grid" style="margin-top:2.5rem"><?php if (!$venues): ?>
            <div class="empty full">No venues match those preferences.</div>
        <?php endif; ?><?php foreach ($venues as $venue): ?>
            <article class="card">
                <div class="venue-image" style="background-image:url('<?= e(image_url($venue['cover_image'])) ?>')"></div>
                <div class="card-body"><span class="tag"><?= e($venue['type']) ?></span>
                    <h3><?= e($venue['name']) ?></h3>
                    <p class="muted"><?= e($venue['city']) ?> · <?= e((string) $venue['max_capacity']) ?> guests · ★
                        <?= e((string) $venue['rating']) ?>
                    </p>
                    <div class="card-meta"><strong><?= money($venue['base_rate']) ?> <small>/ function</small></strong><a
                            href="<?= e(url('pages/venue_detail.php?id=' . $venue['id'])) ?>">View hall →</a></div>
                </div>
            </article><?php endforeach; ?>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>