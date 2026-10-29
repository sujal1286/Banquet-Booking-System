<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
$venue = (int) ($_GET['venue_id'] ?? 0);
$guests = max(0, (int) ($_GET['guests'] ?? 0));
$stmt = db()->prepare('SELECT base_rate FROM venues WHERE id=:id AND status="active"');
$stmt->execute(['id' => $venue]);
$rate = (float) $stmt->fetchColumn();
$subtotal = $rate;
$tax = round($subtotal * .18, 2);
echo json_encode(['subtotal' => $subtotal, 'tax' => $tax, 'total' => $subtotal + $tax, 'guests' => $guests]);
