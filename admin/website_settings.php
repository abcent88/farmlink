<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("
        UPDATE website_settings
        SET
            site_name=?,
            contact_email=?,
            contact_phone=?,
            address=?,
            about_text=?,
            facebook=?,
            twitter=?,
            instagram=?,
            youtube=?,
            linkedin=?,
            footer_text=?
        WHERE id=1
    ");

    $stmt->execute([

        trim($_POST['site_name']),
        trim($_POST['contact_email']),
        trim($_POST['contact_phone']),
        trim($_POST['address']),
        trim($_POST['about_text']),
        trim($_POST['facebook']),
        trim($_POST['twitter']),
        trim($_POST['instagram']),
        trim($_POST['youtube']),
        trim($_POST['linkedin']),
        trim($_POST['footer_text'])

    ]);

    $message = "Website settings updated successfully.";
}

$stmt = $pdo->query("
SELECT *
FROM website_settings
LIMIT 1
");

$settings = $stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';

?>
<div class="container py-4">

<h2 class="mb-4">
🌐 Website Settings
</h2>

<?php if($message): ?>

<div class="alert alert-success">

<?= htmlspecialchars($message) ?>

</div>

<?php endif; ?>

<div class="card shadow">

<div class="card-body">

<form method="POST">
    <h4 class="mb-3">
General Information
</h4>

<div class="row">

<div class="col-md-6 mb-3">

<label>Website Name</label>

<input
type="text"
name="site_name"
class="form-control"
value="<?= htmlspecialchars($settings['site_name']) ?>">

</div>

<div class="col-md-6 mb-3">

<label>Contact Email</label>

<input
type="email"
name="contact_email"
class="form-control"
value="<?= htmlspecialchars($settings['contact_email']) ?>">

</div>

<div class="col-md-6 mb-3">

<label>Phone Number</label>

<input
type="text"
name="contact_phone"
class="form-control"
value="<?= htmlspecialchars($settings['contact_phone']) ?>">

</div>

<div class="col-md-6 mb-3">

<label>Address</label>

<input
type="text"
name="address"
class="form-control"
value="<?= htmlspecialchars($settings['address']) ?>">

</div>

</div>
<div class="mb-3">

<label>

About FarmLink

</label>

<textarea
name="about_text"
rows="5"
class="form-control"><?= htmlspecialchars($settings['about_text']) ?></textarea>

</div>
<h4 class="mt-4 mb-3">

Social Media

</h4>

<div class="row">

<div class="col-md-6 mb-3">

<label>Facebook</label>

<input
type="url"
name="facebook"
class="form-control"
value="<?= htmlspecialchars($settings['facebook']) ?>">

</div>

<div class="col-md-6 mb-3">

<label>Twitter / X</label>

<input
type="url"
name="twitter"
class="form-control"
value="<?= htmlspecialchars($settings['twitter']) ?>">

</div>

<div class="col-md-6 mb-3">

<label>Instagram</label>

<input
type="url"
name="instagram"
class="form-control"
value="<?= htmlspecialchars($settings['instagram']) ?>">

</div>

<div class="col-md-6 mb-3">

<label>YouTube</label>

<input
type="url"
name="youtube"
class="form-control"
value="<?= htmlspecialchars($settings['youtube']) ?>">

</div>

<div class="col-md-6 mb-3">

<label>LinkedIn</label>

<input
type="url"
name="linkedin"
class="form-control"
value="<?= htmlspecialchars($settings['linkedin']) ?>">

</div>

</div>
<div class="mb-4">

<label>

Footer Text

</label>

<textarea
name="footer_text"
rows="2"
class="form-control"><?= htmlspecialchars($settings['footer_text']) ?></textarea>

</div>
<button
class="btn btn-success btn-lg">

💾 Save Website Settings

</button>

</form>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>