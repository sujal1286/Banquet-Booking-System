<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/mail.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $base = preg_replace('#/(pages|auth|client|vendor|admin|api)$#', '', $base) ?: '';
    return $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('The form expired. Please go back and try again.');
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(?string $role = null): void
{
    $user = current_user();
    if (!$user || ($role !== null && $user['role'] !== $role)) {
        flash('error', 'Please sign in to continue.');
        redirect(url('auth/login.php'));
    }
}

function money(float|int|string $amount): string
{
    return '₹' . number_format((float) $amount, 0);
}

function image_url(?string $path): string
{
    $path = trim((string) $path);
    if ($path === '' || preg_match('#^https?://#i', $path)) {
        return $path;
    }

    return url(ltrim($path, '/'));
}

function send_email(string $recipient, string $subject, string $message): bool
{
    return send_smtp_email($recipient, $subject, $message, email_template($subject, $message));
}

function email_template(string $heading, string $message): string
{
    $safeHeading = e($heading);
    $safeMessage = nl2br(e($message));

    return '<!doctype html><html><body style="margin:0;background:#f7f5f0;color:#1b1c1a;font-family:Arial,sans-serif">'
        . '<div style="padding:32px 16px">'
        . '<div style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #e1ddd2">'
        . '<div style="padding:24px 30px;background:#0a231c;color:#ffffff">'
        . '<div style="font-size:12px;letter-spacing:2px;color:#fed65b;text-transform:uppercase">GrandVistas</div>'
        . '<div style="margin-top:8px;font-family:Georgia,serif;font-size:26px">Celebrations arranged with care</div>'
        . '</div>'
        . '<div style="padding:30px">'
        . '<div style="font-size:12px;letter-spacing:1.5px;color:#b38d20;text-transform:uppercase;font-weight:bold">' . $safeHeading . '</div>'
        . '<div style="margin-top:20px;font-size:16px;line-height:1.8;color:#3d4541">' . $safeMessage . '</div>'
        . '<div style="margin-top:28px;padding-top:18px;border-top:1px solid #e1ddd2;color:#66706b;font-size:13px;line-height:1.6">For any assistance, please log in to your GrandVistas account or contact us.</div>'
        . '</div>'
        . '<div style="padding:18px 30px;background:#f1eee7;color:#66706b;font-size:12px">GrandVistas · Elegant banquet halls for memorable celebrations</div>'
        . '</div></div></body></html>';
}

function notify_booking_status(int $bookingId, string $status): bool
{
    $stmt = db()->prepare(
        'SELECT b.reference_id,b.event_title,b.event_date,v.name venue_name,u.name client_name,u.email client_email
         FROM bookings b
         JOIN venues v ON v.id=b.venue_id
         JOIN users u ON u.id=b.client_id
         WHERE b.id=:id'
    );
    $stmt->execute(['id' => $bookingId]);
    $booking = $stmt->fetch();
    if (!$booking) {
        return false;
    }

    $subject = $booking['venue_name'] . ' booking ' . ucfirst($status) . ' - GrandVistas';
    $message = "Booking status update\n\n"
        . "Dear {$booking['client_name']},\n\n"
        . "Your booking at {$booking['venue_name']} on {$booking['event_date']} "
        . "is now marked as " . ucfirst($status) . ".\n\n"
        . "Function: {$booking['event_title']}\n"
        . "Booking reference: {$booking['reference_id']}\n\n"
        . "Regards,\nGrandVistas Celebration Team";

    return send_email($booking['client_email'], $subject, $message);
}

function notify_new_booking(int $bookingId): bool
{
    $stmt = db()->prepare(
        'SELECT b.reference_id,b.event_title,b.event_date,b.guest_count,
                v.name venue_name,u.name client_name,u.email client_email,
                p.name vendor_name,p.email vendor_email
         FROM bookings b
         JOIN venues v ON v.id=b.venue_id
         JOIN users u ON u.id=b.client_id
         LEFT JOIN users p ON p.id=v.vendor_id
         WHERE b.id=:id'
    );
    $stmt->execute(['id' => $bookingId]);
    $booking = $stmt->fetch();
    if (!$booking || empty($booking['vendor_email'])) {
        return false;
    }

    $subject = 'New booking for ' . $booking['venue_name'] . ' - GrandVistas';
    $message = "New booking request\n\n"
        . "Dear {$booking['vendor_name']},\n\n"
        . "A new booking request has been received for your hall: {$booking['venue_name']}.\n\n"
        . "Client: {$booking['client_name']}\n"
        . "Client email: {$booking['client_email']}\n"
        . "Function: {$booking['event_title']}\n"
        . "Date: {$booking['event_date']}\n"
        . "Guests: {$booking['guest_count']}\n"
        . "Booking reference: {$booking['reference_id']}\n\n"
        . "Please log in to the hall partner portal to review the request.\n\n"
        . "Regards,\nGrandVistas Celebration Team";

    return send_email($booking['vendor_email'], $subject, $message);
}
