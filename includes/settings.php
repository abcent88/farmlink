<?php

if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
}

$stmt = $pdo->query("
SELECT *
FROM website_settings
LIMIT 1
");

$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    $settings = [
        'site_name'      => 'FarmLink',
        'contact_email'  => '',
        'contact_phone'  => '',
        'address'        => '',
        'about_text'     => '',
        'facebook'       => '',
        'twitter'        => '',
        'instagram'      => '',
        'youtube'        => '',
        'linkedin'       => '',
        'footer_text'    => ''
    ];
}