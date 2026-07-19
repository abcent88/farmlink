<?php

require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/logger.php';
require_once 'includes/csrf.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if (!$user) {

        $message = 'User not found';

    } elseif ($user['status'] !== 'active') {

        $message = 'Account awaiting approval.';

    } elseif (!password_verify($password, $user['password'])) {

        $message = 'Invalid password';

    } else {

        session_regenerate_id(true);

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role']     = $user['role'];

        logActivity(
            $pdo,
            $user['id'],
            'Login',
            'User signed into FarmLink'
        );

        switch ($user['role']) {

            case 'super_admin':
                $redirect = 'admin/dashboard.php';
                break;

            case 'lga_admin':
                $redirect = 'admin/lga_admin/lga_admin_dashboard.php';
                break;

            case 'farmer':
                $redirect = 'farmer/dashboard.php';
                break;

            case 'buyer':
                $redirect = 'buyer/dashboard.php';
                break;

            case 'trucker':
                $redirect = 'trucker/dashboard.php';
                break;

            case 'investor':
                $redirect = 'investor/dashboard.php';
                break;

            default:
                $redirect = 'index.php';
                break;
        }

        header("Location: $redirect");
        exit;
    }
}

?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-5 col-md-7">

            <div class="card shadow border-0">

                <div class="card-header bg-success text-white text-center">

                    <h3 class="mb-0">
                        Login to FarmLink
                    </h3>

                </div>

                <div class="card-body p-4">

                    <?php if (!empty($message)): ?>

                        <div class="alert alert-warning">

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
                                required
                                autofocus>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Password

                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                required>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-success w-100">

                            Login

                        </button>

                    </form>

                    <div class="text-center mt-3">

                        <a
                            href="forgot_password.php"
                            class="text-success text-decoration-none">

                            Forgot Password?

                        </a>

                    </div>

                    <hr>

                    <div class="text-center">

                        Don't have an account?

                        <a
                            href="register.php"
                            class="text-success text-decoration-none fw-bold">

                            Register

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>