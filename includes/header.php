<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'GrandVistas';
$user = current_user();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | GrandVistas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Newsreader:wght@400;500;600&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,300..700,0..1,0&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>

<body>
    <header class="site-header">
        <a class="brand" href="<?= e(url('pages/index.php')) ?>"><span class="brand-mark">G</span> GrandVistas</a>
        <button class="menu-toggle" type="button" aria-label="Toggle navigation" data-menu-toggle>☰</button>
        <nav class="site-nav" data-menu>
            <a href="<?= e(url('pages/venues.php')) ?>">Banquet halls</a>
            <a href="<?= e(url('pages/venues.php?view=availability')) ?>">Available dates</a>
            <?php if ($user): ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="<?= e(url('admin/dashboard.php')) ?>">Admin</a>
                <?php elseif ($user['role'] === 'vendor'): ?>
                    <a href="<?= e(url('vendor/dashboard.php')) ?>">Partner portal</a>
                <?php else: ?>
                    <a href="<?= e(url('client/dashboard.php')) ?>">My celebrations</a>
                <?php endif; ?>
                <a href="<?= e(url('auth/logout.php')) ?>">Log out</a>
            <?php else: ?>
                <a href="<?= e(url('auth/login.php')) ?>">Log in</a>
                <a class="nav-cta" href="<?= e(url('auth/register.php')) ?>">Create account</a>
            <?php endif; ?>
        </nav>
    </header>
    <?php if ($message = flash('success')): ?>
        <div class="notice success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?>
        <div class="notice error"><?= e($message) ?></div><?php endif; ?>