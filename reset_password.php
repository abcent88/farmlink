<?php

require_once 'config/database.php';
require_once 'includes/csrf.php';

$message = '';
$success = false;

$token = $_GET['token'] ?? $_POST['token'] ?? '';

if (empty($token)) {
    die('Invalid password reset link.');
}

/*
|--------------------------------------------------------------------------
| Validate Token
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT email, expires_at, used
    FROM password_resets
    WHERE token = ?
    LIMIT 1
");
$stmt->execute([$token]);

$reset = $stmt->fetch();

if (!$reset) {
    die('Invalid password reset link.');
}

if ($reset['used']) {
    die('This password reset link has already been used.');
}

if (strtotime($reset['expires_at']) < time()) {
    die('This password reset link has expired.');
}

/*
|--------------------------------------------------------------------------
| Update Password
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);

    if (strlen($password) < 8) {

        $message = 'Password must be at least 8 characters long.';

    } elseif ($password !== $confirm) {

        $message = 'Passwords do not match.';

    } else {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE users
            SET password = ?
            WHERE email = ?
        ");

        $stmt->execute([
            $hashedPassword,
            $reset['email']
        ]);

        $stmt = $pdo->prepare("
            UPDATE password_resets
            SET used = 1
            WHERE token = ?
        ");

        $stmt->execute([$token]);

        $success = true;
        $message = 'Your password has been changed successfully.';
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow">

                <div class="card-header bg-success text-white">
                    <h3>Reset Password</h3>
                </div>

                <div class="card-body">

                    <?php if ($message): ?>

                        <div class="alert alert-<?=
                            $success ? 'success' : 'danger';
                        ?>">

                            <?= htmlspecialchars($message) ?>

                        </div>

                    <?php endif; ?>

                    <?php if (!$success): ?>

                    <form method="POST">

                        <?= csrfField(); ?>

                        <input
                            type="hidden"
                            name="token"
                            value="<?= htmlspecialchars($token) ?>">

                        <div class="mb-3">

                            <label>New Password</label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                minlength="8"
                                required>

                        </div>

                        <div class="mb-3">

                            <label>Confirm Password</label>

                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control"
                                minlength="8"
                                required>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-success w-100">

                            Update Password

                        </button>

                    </form>

                    <?php else: ?>

                        <a
                            href="login.php"
                            class="btn btn-success w-100">

                            Go to Login

                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>