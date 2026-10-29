<?php
require_once __DIR__ . '/functions.php';
function require_role(string $role): void
{
    require_login();
    if ((current_user()['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('You do not have permission to access this area.');
    }
}
