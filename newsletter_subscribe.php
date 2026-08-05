<?php

require_once 'config/database.php';
require_once 'includes/csrf.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

verify_csrf();

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $_SESSION['newsletter_message'] = [
        'type' => 'danger',
        'text' => 'Please enter a valid email address.'
    ];

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}

$stmt = $pdo->prepare("
SELECT id
FROM newsletter_subscribers
WHERE email=?
LIMIT 1
");

$stmt->execute([$email]);

if ($stmt->fetch()) {

    $_SESSION['newsletter_message'] = [
        'type' => 'warning',
        'text' => 'This email is already subscribed.'
    ];

} else {

    $stmt = $pdo->prepare("
    INSERT INTO newsletter_subscribers(email)
    VALUES(?)
    ");

    $stmt->execute([$email]);

    $_SESSION['newsletter_message'] = [
        'type' => 'success',
        'text' => 'Thank you for subscribing!'
    ];
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;