<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Elegant banquet halls for memorable celebrations';
$venues = db()->query("SELECT * FROM venues WHERE status = 'active' ORDER BY rating DESC LIMIT 3")->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<main>
    <section class="hero">
        <div class="hero-inner">
            <div class="eyebrow">Celebrations with Indian warmth</div>
            <h1>Banquet halls for every beautiful occasion</h1>
            <p>Find gracious spaces for weddings, receptions, family functions, festive gatherings, and corporate
                events, with service that feels truly personal.</p><a class="button gold"
                href="<?= e(url('pages/venues.php')) ?>">View banquet halls <span>→</span></a>
        </div>
    </section>
    <section class="container">
        <div class="section-head">
            <div>
                <div class="eyebrow">Our handpicked halls</div>
                <h2>Spaces made for celebration</h2>
            </div><a class="button outline" href="<?= e(url('pages/venues.php')) ?>">See all halls</a>
        </div>
        <div class="grid"><?php foreach ($venues as $venue): ?>
                <article class="card">
                    <div class="venue-image" style="background-image:url('<?= e(image_url($venue['cover_image'])) ?>')">
                    </div>
                    <div class="card-body"><span class="tag"><?= e($venue['type']) ?></span>
                        <h3><?= e($venue['name']) ?></h3>
                        <p class="muted"><?= e($venue['city']) ?> · Up to <?= e((string) $venue['max_capacity']) ?> guests
                        </p>
                        <div class="card-meta"><span><?= money($venue['base_rate']) ?> onwards</span><a
                                href="<?= e(url('pages/venue_detail.php?id=' . $venue['id'])) ?>">View hall →</a></div>
                    </div>
                </article><?php endforeach; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>