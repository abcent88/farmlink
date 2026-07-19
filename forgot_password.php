<?php

require_once 'config/database.php';
require_once 'config/app.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';
require_once 'includes/mailer.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("
        SELECT id
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    if ($stmt->fetch()) {

        $token = bin2hex(random_bytes(32));

        $expiry = date(
            'Y-m-d H:i:s',
            time() + 3600
        );

        $stmt = $pdo->prepare("
            INSERT INTO password_resets
            (
                email,
                token,
                expires_at
            )
            VALUES
            (
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([
            $email,
            $token,
            $expiry
        ]);

        $link = APP_URL . "/reset_password.php?token=" . $token;

        $body = "
            <h2>FarmLink Password Reset</h2>

            <p>We received a request to reset your password.</p>

            <p>
                <a href='$link'>Reset Password</a>
            </p>

            <p>This link expires in 1 hour.</p>
        ";

        sendMail(
            $email,
            'FarmLink Password Reset',
            $body
        );
    }

    // Always show the same message
    $message = "If the email exists in our system, a password reset link has been sent.";
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-header bg-success text-white">

                    <h3 class="mb-0">
                        Forgot Password
                    </h3>

                </div>

                <div class="card-body">

                    <?php if ($message): ?>

                        <div class="alert alert-success">

                            <?= htmlspecialchars($message) ?>

                        </div>

                    <?php endif; ?>

                    <form method="POST">

                        <?= csrfField(); ?>

                        <div class="mb-3">

                            <label class="form-label">

                                Email Address

                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                required>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-success w-100">

                            Send Reset Link

                        </button>

                    </form>

                    <div class="text-center mt-3">

                        <a href="login.php">

                            ← Back to Login

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>