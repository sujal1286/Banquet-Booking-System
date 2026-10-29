<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/role_check.php';
require_role('admin');
$users = db()->query('SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC')->fetchAll();
$pageTitle = 'User management';
require __DIR__ . '/../includes/header.php';
?>
<main class="container">
    <div class="eyebrow">GrandVistas administration</div>
    <h1>Registered members and partners</h1>
    <div class="form-card" style="overflow:auto">
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <th align="left">Name</th>
                <th align="left">Email</th>
                <th align="left">Role</th>
                <th align="left">Joined</th>
            </tr><?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="tag"><?= e($u['role']) ?></span></td>
                    <td><?= e($u['created_at']) ?></td>
                </tr><?php endforeach; ?>
        </table>
    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>