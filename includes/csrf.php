<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Generate CSRF Token
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| Return Token
|--------------------------------------------------------------------------
*/

function csrf_token()
{
    return $_SESSION['csrf_token'];
}

/*
|--------------------------------------------------------------------------
| Hidden Form Field
|--------------------------------------------------------------------------
*/

function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') .
        '">';
}

/*
|--------------------------------------------------------------------------
| Verify Token
|--------------------------------------------------------------------------
*/

function verify_csrf()
{
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {

        appError('Invalid CSRF Token');

        appFail(
            'Your session expired or the request is invalid. Please try again.'
        );
    }
}
?>