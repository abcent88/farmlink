<?php

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin');
header('X-XSS-Protection: 1; mode=block');

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>FarmLink</title>

<?php
$base = "/projects/farmlink";
?>

<link rel="stylesheet" href="<?= $base ?>/assets/vendor/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= $base ?>/assets/css/theme.css">
<link rel="stylesheet" href="<?= $base ?>/assets/css/navbar.css">

</head>

<body>