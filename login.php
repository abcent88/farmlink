<?php

session_start();

require_once 'config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = :email";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        if ($user['status'] != 'active') {

            $message = "Account awaiting approval.";

        } elseif (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            switch ($user['role']) {

case 'super_admin':
    header("Location: admin/dashboard.php");
    exit;

case 'lga_admin':
    header("Location: admin/lga_admin/lga_admin_dashboard.php");
    exit;

case 'farmer':
    header("Location: farmer/dashboard.php");
    exit;

case 'buyer':
    header("Location: buyer/dashboard.php");
    exit;

case 'trucker':
    header("Location: trucker/dashboard.php");
    exit;

}

        } else {

            $message = "Invalid password";

        }

    } else {

        $message = "User not found";

    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow">

                <div class="card-header bg-success text-white">
                    <h3>Login</h3>
                </div>

                <div class="card-body">

                    <?php if(!empty($message)): ?>
                        <div class="alert alert-warning">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>
                        </div>

                        <button type="submit"
                                class="btn btn-success w-100">

                            Login

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>