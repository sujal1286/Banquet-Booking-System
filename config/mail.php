<?php
declare(strict_types=1);

function mail_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $values = [];
    $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (is_readable($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', ltrim($line, "\xEF\xBB\xBF"), 2);
            $values[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        }
    }

    $config = [
        'host' => $values['MAIL_HOST'] ?? getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => (int) ($values['MAIL_PORT'] ?? getenv('MAIL_PORT') ?: 587),
        'username' => $values['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME') ?: '',
        'password' => preg_replace('/\s+/', '', $values['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD') ?: ''),
        'from_address' => $values['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS') ?: '',
        'from_name' => $values['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?: 'GrandVistas',
    ];

    return $config;
}

function send_smtp_email(string $recipient, string $subject, string $message, ?string $htmlMessage = null): bool
{
    $config = mail_config();
    if ($config['username'] === '' || $config['password'] === '' || $config['from_address'] === '') {
        $GLOBALS['grandvistas_mail_error'] = 'SMTP credentials are missing from the project .env file.';
        error_log('GrandVistas SMTP is not configured. Add MAIL_USERNAME, MAIL_PASSWORD, and MAIL_FROM_ADDRESS to .env.');
        return false;
    }

    $socket = @stream_socket_client(
        'tcp://' . $config['host'] . ':' . $config['port'],
        $errorNumber,
        $errorMessage,
        15,
        STREAM_CLIENT_CONNECT
    );
    if (!$socket) {
        $GLOBALS['grandvistas_mail_error'] = 'Could not connect to Gmail SMTP: ' . $errorMessage;
        error_log("GrandVistas SMTP connection failed ({$errorNumber}): {$errorMessage}");
        return false;
    }

    stream_set_timeout($socket, 15);
    try {
        smtp_expect($socket, 220);
        smtp_command($socket, 'EHLO localhost', 250);
        smtp_command($socket, 'STARTTLS', 220);
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('TLS negotiation failed.');
        }
        smtp_command($socket, 'EHLO localhost', 250);
        smtp_command($socket, 'AUTH LOGIN', 334);
        smtp_command($socket, base64_encode($config['username']), 334);
        smtp_command($socket, base64_encode($config['password']), 235);
        smtp_command($socket, 'MAIL FROM:<' . $config['from_address'] . '>', 250);
        smtp_command($socket, 'RCPT TO:<' . $recipient . '>', 250);
        smtp_command($socket, 'DATA', 354);

        $headers = [
            'From: ' . $config['from_name'] . ' <' . $config['from_address'] . '>',
            'To: <' . $recipient . '>',
            'Subject: ' . (function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($subject, 'UTF-8') : $subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="grandvistas-boundary"',
        ];
        $plainBody = preg_replace('/^\./m', '..', str_replace(["\r\n", "\r"], "\n", $message));
        $htmlBody = $htmlMessage !== null ? $htmlMessage : nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        $emailBody = "--grandvistas-boundary\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . str_replace("\n", "\r\n", $plainBody) . "\r\n"
            . "--grandvistas-boundary\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . str_replace("\n", "\r\n", $htmlBody) . "\r\n"
            . "--grandvistas-boundary--\r\n";
        fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $emailBody . ".\r\n");
        smtp_expect($socket, 250);
        smtp_command($socket, 'QUIT', 221);
    } catch (Throwable $exception) {
        $GLOBALS['grandvistas_mail_error'] = $exception->getMessage();
        error_log('GrandVistas SMTP error: ' . $exception->getMessage());
        fclose($socket);
        return false;
    }

    function mail_last_error(): string
    {
        return (string) ($GLOBALS['grandvistas_mail_error'] ?? 'Email could not be sent.');
    }

    fclose($socket);
    return true;
}

function smtp_command($socket, string $command, int $expectedCode): void
{
    fwrite($socket, $command . "\r\n");
    smtp_expect($socket, $expectedCode);
}

function smtp_expect($socket, int $expectedCode): void
{
    $response = '';
    while (($line = fgets($socket)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    if ((int) substr($response, 0, 3) !== $expectedCode) {
        throw new RuntimeException('SMTP expected ' . $expectedCode . ', received: ' . trim($response));
    }
}
