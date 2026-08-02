<?php

require_once 'config/database.php';
require_once 'includes/auth.php';

$userId = $_SESSION['user_id'];

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {

        $message = "New passwords do not match.";
        $type = "danger";

    } elseif (strlen($newPassword) < 8) {

        $message = "Password must be at least 8 characters.";
        $type = "danger";

    } else {

        $stmt = $pdo->prepare("
            SELECT password
            FROM users
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$userId]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user['password'])) {

            $message = "Current password is incorrect.";
            $type = "danger";

        } else {

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $update = $pdo->prepare("
                UPDATE users
                SET password = ?
                WHERE id = ?
            ");

            $update->execute([
                $hashedPassword,
                $userId
            ]);

            $message = "Password changed successfully.";
            $type = "success";
        }
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">

<div class="card shadow-sm">

<div class="card-header bg-success text-white">

<h3 class="mb-0">

Change Password

</h3>

</div>

<div class="card-body">
    <?php if (!empty($message)): ?>

    <div class="alert alert-<?= $type ?> alert-dismissible fade show">

        <?= htmlspecialchars($message) ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

<?php endif; ?>

<form method="POST">

    <div class="mb-3">

        <label class="form-label">

            Current Password

        </label>

        <input
            type="password"
            name="current_password"
            class="form-control"
            required>

    </div>

    <div class="mb-3">

        <label class="form-label">

            New Password

        </label>

        <input
            type="password"
            name="new_password"
            class="form-control"
            minlength="8"
            required>

        <small class="text-muted">

            Password must be at least 8 characters.

        </small>

    </div>

    <div class="mb-4">

        <label class="form-label">

            Confirm New Password

        </label>

        <input
            type="password"
            name="confirm_password"
            class="form-control"
            minlength="8"
            required>

    </div>

    <button
        type="submit"
        class="btn btn-success">

        <i class="bi bi-key"></i>

        Change Password

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