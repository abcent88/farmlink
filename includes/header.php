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
<link rel="stylesheet" href="/projects/farmlink/assets/css/preloader.css">

</head>

<body>
    <div id="preloader">

    <div class="preloader-box">

        <img
    src="/projects/farmlink/assets/images/logo/farmlink-logo.png"
    alt="FarmLink"
    class="preloader-logo">

            FarmLink

        </div>

        <div class="preloader-sub">

            Connecting Farmers, Buyers & Truckers

        </div>

        <div class="loader-dots">

            <span></span>
            <span></span>
            <span></span>

        </div>
        <div class="loader-progress">

    <div class="loader-progress-bar"></div>

</div>

    </div>

</div>