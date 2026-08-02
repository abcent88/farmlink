<?php

require_once 'config/database.php';
require_once 'includes/auth.php';

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT
        id,
        fullname,
        email,
        phone,
        lga,
        town
    FROM users
    WHERE id=?
    LIMIT 1
");

$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $lga      = trim($_POST['lga']);
    $town     = trim($_POST['town']);

    $update = $pdo->prepare("
        UPDATE users
        SET
            fullname=?,
            email=?,
            phone=?,
            lga=?,
            town=?
        WHERE id=?
    ");

    $update->execute([
        $fullname,
        $email,
        $phone,
        $lga,
        $town,
        $userId
    ]);

    $_SESSION['success'] = "Profile updated successfully.";

    header("Location: edit_profile.php");

    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">

<div class="card shadow-sm">

<div class="card-header bg-success text-white">

<h3 class="mb-0">

Edit Profile

</h3>

</div>

<div class="card-body">
    <?php if (!empty($_SESSION['success'])): ?>

    <div class="alert alert-success alert-dismissible fade show">

        <?= htmlspecialchars($_SESSION['success']) ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

    <?php unset($_SESSION['success']); ?>

<?php endif; ?>

<form method="POST">

    <div class="row">

        <div class="col-md-6 mb-3">

            <label class="form-label">

                Full Name

            </label>

            <input
                type="text"
                name="fullname"
                class="form-control"
                required
                value="<?= htmlspecialchars($user['fullname']) ?>">

        </div>

        <div class="col-md-6 mb-3">

            <label class="form-label">

                Email

            </label>

            <input
                type="email"
                name="email"
                class="form-control"
                required
                value="<?= htmlspecialchars($user['email']) ?>">

        </div>

        <div class="col-md-6 mb-3">

            <label class="form-label">

                Phone

            </label>

            <input
                type="text"
                name="phone"
                class="form-control"
                value="<?= htmlspecialchars($user['phone']) ?>">

        </div>

        <div class="col-md-6 mb-3">

            <label class="form-label">

                LGA

            </label>

            <input
                type="text"
                name="lga"
                class="form-control"
                value="<?= htmlspecialchars($user['lga']) ?>">

        </div>

        <div class="col-md-6 mb-3">

            <label class="form-label">

                Town

            </label>

            <input
                type="text"
                name="town"
                class="form-control"
                value="<?= htmlspecialchars($user['town']) ?>">

        </div>

    </div>

    <hr>

    <button
        type="submit"
        class="btn btn-success">

        <i class="bi bi-check-circle"></i>

        Save Changes

    </button>

    <a
        href="javascript:history.back()"
        class="btn btn-secondary">

        Cancel

    </a>

</form>
</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>