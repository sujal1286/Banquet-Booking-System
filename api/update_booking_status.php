<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/role_check.php';
require_role('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('POST required.');
}
verify_csrf();
$id = (int) ($_POST['booking_id'] ?? 0);
$status = (string) ($_POST['status'] ?? '');
if (!in_array($status, ['approved', 'confirmed', 'completed', 'cancelled'], true)) {
    http_response_code(422);
    exit('Invalid status.');
}
$old = db()->prepare('SELECT status FROM bookings WHERE id=:id');
$old->execute(['id' => $id]);
$oldStatus = $old->fetchColumn();
$stmt = db()->prepare('UPDATE bookings SET status=:status WHERE id=:id');
$stmt->execute(['status' => $status, 'id' => $id]);
$emailSent = $oldStatus !== false && $oldStatus !== $status ? notify_booking_status($id, $status) : true;
flash('success', 'Booking status updated.' . ($emailSent ? ' The client has been notified.' : ' The client can see the update in their dashboard.'));
redirect(url('admin/dashboard.php'));
