<?php

if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
}

$stmt = $pdo->query("
    SELECT lga
    FROM lgas
    ORDER BY lga ASC
");

$lgas = $stmt->fetchAll(PDO::FETCH_ASSOC);