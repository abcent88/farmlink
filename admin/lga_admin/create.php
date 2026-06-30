<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';

requireRole('super_admin');

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $lga = trim($_POST['lga']);
    $town = trim($_POST['town']);
    $password = trim($_POST['password']);

    if (
        empty($fullname) ||
        empty($email) ||
        empty($phone) ||
        empty($lga) ||
        empty($town) ||
        empty($password)
    ) {
        $errors[] = "All fields are required.";
    }

    $check = $pdo->prepare("
        SELECT id
        FROM users
        WHERE email = ?
    ");

    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $errors[] = "Email already exists.";
    }

    if (empty($errors)) {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare("
            INSERT INTO users
            (
                fullname,
                email,
                phone,
                password,
                role,
                status,
                lga,
                town,
                commission_rate
            )
            VALUES
            (
                ?, ?, ?, ?,
                'lga_admin',
                'active',
                ?, ?,
                1.50
            )
        ");

        $stmt->execute([
            $fullname,
            $email,
            $phone,
            $hashedPassword,
            $lga,
            $town
        ]);

        $message = "LGA Admin created successfully.";
    }
}

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container mt-5">

```
<div class="card shadow">

    <div class="card-header bg-primary text-white">
        <h3>Create LGA Admin</h3>
    </div>

    <div class="card-body">

        <?php if(!empty($message)): ?>
            <div class="alert alert-success">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label>Full Name</label>
                <input
                    type="text"
                    name="fullname"
                    class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input
                    type="email"
                    name="email"
                    class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label>Phone</label>
                <input
                    type="text"
                    name="phone"
                    class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label>LGA</label>
                <input
                    type="text"
                    name="lga"
                    class="form-control"
                    placeholder="e.g. Makurdi"
                    required>
            </div>

            <div class="mb-3">
                <label>Town</label>
                <input
                    type="text"
                    name="town"
                    class="form-control"
                    placeholder="e.g. North Bank"
                    required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label>Commission Rate</label>
                <input
                    type="text"
                    class="form-control"
                    value="1.50%"
                    readonly>
            </div>

            <button
                type="submit"
                class="btn btn-success">

                Create LGA Admin

            </button>

            <a
                href="../dashboard.php"
                class="btn btn-secondary">

                Back

            </a>

        </form>

    </div>

</div>
```

</div>

<?php include '../../includes/footer.php'; ?>
