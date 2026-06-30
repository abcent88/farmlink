<?php

ini_set('session.gc_maxlifetime', 1800);

session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'httponly' => true
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}