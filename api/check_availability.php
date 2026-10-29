<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
$venue = (int) ($_GET['venue_id'] ?? 0);
$date = (string) ($_GET['date'] ?? '');
$stmt = db()->prepare("SELECT COUNT(*) FROM bookings WHERE venue_id=:venue AND event_date=:date AND status IN ('pending','approved','confirmed')");
$stmt->execute(['venue' => $venue, 'date' => $date]);
echo json_encode(['available' => (int) $stmt->fetchColumn() === 0]);
