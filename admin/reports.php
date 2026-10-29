<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/role_check.php';
require_role('admin');
if (($_GET['format'] ?? '') === 'csv') {
    header('Content-Type:text/csv');
    header('Content-Disposition:attachment; filename=grandvistas-report.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Reference', 'Venue', 'Client', 'Date', 'Status', 'Total']);
    $rows = db()->query('SELECT b.reference_id,v.name venue_name,u.name client_name,b.event_date,b.status,p.total_amount FROM bookings b JOIN venues v ON v.id=b.venue_id JOIN users u ON u.id=b.client_id LEFT JOIN payments p ON p.booking_id=b.id ORDER BY b.event_date DESC')->fetchAll();
    foreach ($rows as $r)
        fputcsv($out, $r);
    exit;
}
$summary = db()->query("SELECT COUNT(*) bookings,COALESCE(SUM(total_amount),0) revenue FROM payments")->fetch();
$monthly = db()->query("SELECT DATE_FORMAT(b.event_date,'%Y-%m') month,COUNT(*) bookings,COALESCE(SUM(p.total_amount),0) revenue FROM bookings b LEFT JOIN payments p ON p.booking_id=b.id GROUP BY month ORDER BY month DESC LIMIT 12")->fetchAll();
$pageTitle = 'Reports';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="section-head">
        <div>
            <div class="eyebrow">GrandVistas administration</div>
            <h1>Booking and revenue reports</h1>
        </div><a class="button primary" href="<?= e(url('admin/reports.php?format=csv')) ?>">Download CSV</a>
    </div>
    <div class="grid">
        <div class="card card-body">
            <div class="eyebrow">Reservations</div>
            <h2><?= e((string) $summary['bookings']) ?></h2>
        </div>
        <div class="card card-body">
            <div class="eyebrow">Gross value</div>
            <h2><?= money($summary['revenue']) ?></h2>
        </div>
    </div>
    <div class="form-card" style="margin-top:2rem">
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <th align="left">Month</th>
                <th align="left">Bookings</th>
                <th align="left">Revenue</th>
            </tr><?php foreach ($monthly as $row): ?>
                <tr>
                    <td><?= e($row['month']) ?></td>
                    <td><?= e((string) $row['bookings']) ?></td>
                    <td><?= money($row['revenue']) ?></td>
                </tr><?php endforeach; ?>
        </table>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>