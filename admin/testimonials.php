<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');

/*
|--------------------------------------------------------------------------
| Approve / Reject
|--------------------------------------------------------------------------
*/

require_once '../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {

        $stmt = $pdo->prepare("
            UPDATE testimonials
            SET status='approved'
            WHERE id=?
        ");

        $stmt->execute([$id]);

    } elseif ($action === 'reject') {

        $stmt = $pdo->prepare("
            UPDATE testimonials
            SET status='rejected'
            WHERE id=?
        ");

        $stmt->execute([$id]);

    } elseif ($action === 'delete') {

        $stmt = $pdo->prepare("
            DELETE FROM testimonials
            WHERE id=?
        ");

        $stmt->execute([$id]);
    }
}
/*
|--------------------------------------------------------------------------
| Load testimonials
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
SELECT
    testimonials.*,
    users.fullname,
    users.email
FROM testimonials
JOIN users
ON users.id=testimonials.user_id
ORDER BY testimonials.created_at DESC
");

$testimonials = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-4">

<h2 class="mb-4">
Manage Testimonials
</h2>

<div class="card shadow">

<div class="table-responsive">

<table class="table table-striped table-hover mb-0">

<thead class="table-success">

<tr>

<th>User</th>

<th>Email</th>

<th>Rating</th>

<th>Message</th>

<th>Status</th>

<th>Date</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<?php foreach($testimonials as $row): ?>

<tr>

<td><?= htmlspecialchars($row['fullname']) ?></td>

<td><?= htmlspecialchars($row['email']) ?></td>

<td>

<?php

for($i=1;$i<=5;$i++){

    echo $i <= $row['rating']
        ? "⭐"
        : "☆";
}

?>

</td>

<td style="max-width:350px">

<?= nl2br(htmlspecialchars($row['message'])) ?>

</td>

<td>

<?php

$status = $row['status'];

if($status=='approved'){

    echo '<span class="badge bg-success">Approved</span>';

}elseif($status=='pending'){

    echo '<span class="badge bg-warning text-dark">Pending</span>';

}else{

    echo '<span class="badge bg-danger">Rejected</span>';

}

?>

</td>

<td>

<?= date('d M Y', strtotime($row['created_at'])) ?>

</td>

<td>

<form method="POST" class="d-inline">

    <?= csrfField(); ?>

    <input
        type="hidden"
        name="id"
        value="<?= $row['id'] ?>">

    <button
        class="btn btn-success btn-sm"
        name="action"
        value="approve">

        Approve

    </button>

</form>

<form method="POST" class="d-inline">

    <?= csrfField(); ?>

    <input
        type="hidden"
        name="id"
        value="<?= $row['id'] ?>">

    <button
        class="btn btn-warning btn-sm"
        name="action"
        value="reject">

        Reject

    </button>

</form>

<form
    method="POST"
    class="d-inline"
    onsubmit="return confirm('Delete this testimonial?');">

    <?= csrfField(); ?>

    <input
        type="hidden"
        name="id"
        value="<?= $row['id'] ?>">

    <button
        class="btn btn-danger btn-sm"
        name="action"
        value="delete">

        Delete

    </button>

</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>