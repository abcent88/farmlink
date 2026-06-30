<?php

require_once '../config/database.php';

$id = (int)$_GET['id'];

$newPassword = password_hash(
    'Password123',
    PASSWORD_DEFAULT
);

$stmt = $pdo->prepare("
    UPDATE users
    SET password=?
    WHERE id=?
");

$stmt->execute([
    $newPassword,
    $id
]);

header("Location: users.php");
exit();