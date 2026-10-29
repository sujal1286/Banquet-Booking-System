<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/role_check.php';
require_role('admin');
$stats = ['users' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(), 'venues' => (int) db()->query("SELECT COUNT(*) FROM venues WHERE status='active'")->fetchColumn(), 'bookings' => (int) db()->query('SELECT COUNT(*) FROM bookings')->fetchColumn(), 'revenue' => (float) db()->query("SELECT COALESCE(SUM(total_amount),0) FROM payments WHERE payment_status IN ('partial','paid')")->fetchColumn()];
$bookings = db()->query('SELECT b.*,v.name venue_name,u.name client_name FROM bookings b JOIN venues v ON v.id=b.venue_id JOIN users u ON u.id=b.client_id ORDER BY b.created_at DESC LIMIT 20')->fetchAll();
$pageTitle = 'Admin management';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="eyebrow">GrandVistas administration</div>
    <h1>Manage the platform</h1>
    <p><a class="button outline" href="<?= e(url('admin/users.php')) ?>">Users</a> <a class="button outline"
            href="<?= e(url('admin/venues.php')) ?>">Venues</a> <a class="button outline"
            href="<?= e(url('admin/reports.php')) ?>">Reports</a></p>
    <div class="grid">
        <div class="card card-body">
            <div class="eyebrow">Clients & partners</div>
            <h2><?= $stats['users'] ?></h2>
        </div>
        <div class="card card-body">
            <div class="eyebrow">Active venues</div>
            <h2><?= $stats['venues'] ?></h2>
        </div>
        <div class="card card-body">
            <div class="eyebrow">All bookings</div>
            <h2><?= $stats['bookings'] ?></h2>
        </div>
    </div>
    <h2 style="margin-top:3rem">Latest booking activity</h2>
    <div class="form-card" style="overflow:auto">
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <th align="left">Reference</th>
                <th align="left">Client</th>
                <th align="left">Venue</th>
                <th align="left">Date</th>
                <th align="left">Status</th>
                <th></th>
            </tr><?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= e($booking['reference_id']) ?></td>
                    <td><?= e($booking['client_name']) ?></td>
                    <td><?= e($booking['venue_name']) ?></td>
                    <td><?= e($booking['event_date']) ?></td>
                    <td><span class="tag"><?= e($booking['status']) ?></span></td>
                    <td><?php if ($booking['status'] === 'pending'): ?>
                            <form method="post" action="<?= e(url('api/update_booking_status.php')) ?>"><input type="hidden"
                                    name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="booking_id"
                                    value="<?= e((string) $booking['id']) ?>"><input type="hidden" name="status"
                                    value="approved"><button class="button">Approve</button></form><?php endif; ?>
                    </td>
                </tr><?php endforeach; ?>
        </table>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>