<?php
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];
    $lga      = trim($_POST['lga']);
    $town     = trim($_POST['town']);
    $commission_rate = 1.5; // default 1.5%

    // Basic validations
    if (!$fullname || !$email || !$phone || !$password || !$lga || !$town) {
        $errors[] = "All fields are required.";
    }

    // Check duplicate email
    $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        $errors[] = "Email already exists.";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users 
            (fullname, email, phone, password, role, status, lga, town, commission_rate)
            VALUES (:fullname, :email, :phone, :password, 'lga_admin', 'active', :lga, :town, :commission_rate)
        ");

        $stmt->execute([
            ':fullname' => $fullname,
            ':email'    => $email,
            ':phone'    => $phone,
            ':password' => $hashedPassword,
            ':lga'      => $lga,
            ':town'     => $town,
            ':commission_rate' => $commission_rate
        ]);

        $message = "LGA Admin created successfully!";
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <h2>Create LGA Admin</h2>

    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(!empty($message)): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>LGA</label>
            <input type="text" name="lga" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Town</label>
            <input type="text" name="town" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Create Admin</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>