<?php
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/settings.php';
?>
<footer class="footer">

<div class="container text-center">

<h5 class="mb-2">
🌱 <?= htmlspecialchars($settings['site_name']) ?>
</h5>

<div class="mb-2">

<a href="/projects/farmlink/">Home</a>

|

<a href="/projects/farmlink/index.php#products">Marketplace</a>

|

<a href="/projects/farmlink/register.php">Register</a>

|

<a href="/projects/farmlink/login.php">Login</a>

</div>

<div class="mb-2">

<?= htmlspecialchars($settings['contact_email']) ?>

|

<?= htmlspecialchars($settings['contact_phone']) ?>

</div>

<div class="small">

© <?= date('Y') ?>

<?= htmlspecialchars($settings['site_name']) ?>

•

<?= htmlspecialchars($settings['footer_text']) ?>

</div>

</div>

</footer>

<script src="<?= $base ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>/assets/js/theme.js"></script>
<script src="/projects/farmlink/assets/js/preloader.js"></script>
<script src="/projects/farmlink/assets/js/counter.js"></script>

</body>
</html>