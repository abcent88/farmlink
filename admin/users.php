<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

requireRole('super_admin');

$search = $_GET['search'] ?? '';
$perPage = 10;

$page = max(
    1,
    (int)($_GET['page'] ?? 1)
);

$offset = ($page - 1) * $perPage;

$sql = "
SELECT id, fullname, email, phone, role, status
FROM users
";

$params = [];

if (!empty($search)) {

    $sql .= "
    WHERE fullname LIKE ?
    OR email LIKE ?
    ";

    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY id DESC";
$sql .= "
LIMIT $perPage
OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <h2>User Management</h2>

    <a href="dashboard.php"
       class="btn btn-secondary mb-3">
        Back Dashboard
    </a>
<form method="GET" class="mb-3">
    <?= csrfField(); ?>

    <div class="row">

        <div class="col-md-10">

            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Search users..."
                value="<?= htmlspecialchars($search) ?>">

        </div>

        <div class="col-md-2">

            <button
                class="btn btn-primary w-100">

                Search

            </button>

        </div>

    </div>

</form>
    <table class="table table-bordered">

        <thead>

        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        </thead>

        <tbody>

        <?php foreach($users as $user): ?>

            <tr>

                <td><?= $user['id']; ?></td>

                <td><?= htmlspecialchars($user['fullname']); ?></td>

                <td><?= htmlspecialchars($user['email']); ?></td>

                <td><?= htmlspecialchars($user['phone']); ?></td>

                <td><?= htmlspecialchars($user['role']); ?></td>

                <td><?= htmlspecialchars($user['status']); ?></td>

                <td>

                    <td>

    <form method="POST"
          action="approve_user.php"
          style="display:inline;">

        <input type="hidden"
               name="csrf_token"
               value="<?= csrf_token(); ?>">

        <input type="hidden"
               name="id"
               value="<?= $user['id']; ?>">

        <button type="submit"
                class="btn btn-success btn-sm">
            Approve
        </button>

    </form>

    <form method="POST"
   
          action="block_user.php"
          style="display:inline;">

        <input type="hidden"
               name="csrf_token"
               value="<?= csrf_token(); ?>">

        <input type="hidden"
               name="id"
               value="<?= $user['id']; ?>">

        <button type="submit"
                class="btn btn-warning btn-sm">
            Block
        </button>

    </form>

    <form method="POST"
          action="delete_user.php"
          style="display:inline;">

        <input type="hidden"
               name="csrf_token"
               value="<?= csrf_token(); ?>">

        <input type="hidden"
               name="id"
               value="<?= $user['id']; ?>">

        <button type="submit"
                class="btn btn-danger btn-sm"
                onclick="return confirm('Delete User?');">
            Delete
        </button>

    </form>

    <a href="reset_password.php?id=<?= $user['id']; ?>"
       class="btn btn-primary btn-sm">
        Reset Password
    </a>

</td>
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>
    <div class="mt-3">

    <?php if ($page > 1): ?>

        <a
            class="btn btn-secondary"
            href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
            Previous
        </a>

    <?php endif; ?>

    <a
        class="btn btn-secondary"
        href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
        Next
    </a>

</div>

</div>

</body>
</html>