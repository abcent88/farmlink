<?php

$host = "localhost";
$dbname = "farmlink_db";
$username = "farmlink_user";
$password = "Esapret91@";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    die("Connection Failed: " . $e->getMessage());
}
?>