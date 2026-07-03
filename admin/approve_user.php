<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';
require_once '../includes/notify.php';


verify_csrf();

$id = (int)$_POST['id'];

$stmt = $pdo->prepare("
    UPDATE users
    SET status='active'
    WHERE id=?
");
notify(

$pdo,

$userId,

'Account Approved',

'Your FarmLink account has been approved. You can now login.'

);


$stmt->execute([$id]);

logAction(
    $pdo,
    $_SESSION['user_id'],
    'Approved User',
    $id
);

header("Location: users.php");
exit();