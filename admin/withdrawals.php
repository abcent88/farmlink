<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';
require_once '../includes/mailer.php';
require_once '../includes/notify.php';
require_once '../includes/csrf.php';

requireRole('super_admin');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $withdrawalId = (int) $_POST['withdrawal_id'];
    $action = trim($_POST['action']);

    if (in_array($action, ['approved', 'rejected'])) {

        // Update withdrawal status
        $stmt = $pdo->prepare("
            UPDATE investor_withdrawals
            SET status=?
            WHERE id=?
        ");

        $stmt->execute([
            $action,
            $withdrawalId
        ]);

        // Get investor details
        $stmt = $pdo->prepare("
            SELECT
                u.id,
                u.email,
                u.fullname,
                w.amount,
                w.status
            FROM investor_withdrawals w
            JOIN investors i
                ON w.investor_id=i.id
            JOIN users u
                ON i.user_id=u.id
            WHERE w.id=?
            LIMIT 1
        ");

        $stmt->execute([
            $withdrawalId
        ]);

        $user = $stmt->fetch();

        if (!empty($user['id'])) {

    notify(
        $pdo,
        $user['id'],
        'Withdrawal ' . ucfirst($action),
        $action === 'approved'
            ? 'Your withdrawal request has been approved and payment is being processed.'
            : 'Your withdrawal request has been rejected. Please contact support.'
    );

}

            // Email subject
            $subject = $action === 'approved'
                ? 'FarmLink Withdrawal Approved'
                : 'FarmLink Withdrawal Rejected';

            // Email body
            $body = "
            <h2>Withdrawal Update</h2>

            <p>Hello {$user['fullname']},</p>

            <p>
                Your withdrawal request has been
                <strong>" . strtoupper($action) . "</strong>.
            </p>

            <p>
                Amount:
                <strong>₦" . number_format($user['amount'], 2) . "</strong>
            </p>";

            if ($action === 'approved') {

                $body .= "
                <p>
                    Your payment will be processed shortly.
                </p>";

            } else {

                $body .= "
                <p>
                    Unfortunately your withdrawal request was rejected.
                    Kindly contact FarmLink support for further assistance.
                </p>";

            }

            sendMail(
                $user['email'],
                $subject,
                $body
            );
        }

        $message = 'Withdrawal updated successfully.';
    }


// Fetch withdrawals
$stmt = $pdo->query("
    SELECT
        w.id,
        u.id AS user_id,
        u.fullname,
        u.email,
        w.amount,
        w.status,
        w.request_date,
        w.account_name,
        w.bank_name,
        w.account_number
    FROM investor_withdrawals w
    INNER JOIN investors i
        ON w.investor_id = i.id
    INNER JOIN users u
        ON i.user_id = u.id
    ORDER BY w.id DESC
");

$withdrawals = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

<h2>Investor Withdrawals</h2>

<?php if ($message): ?>

<div class="alert alert-success">
    <?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>ID</th>
<th>Investor</th>
<th>Email</th>
<th>Amount</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php foreach ($withdrawals as $w): ?>

<tr>

<td><?= $w['id'] ?></td>

<td><?= htmlspecialchars($w['fullname']) ?></td>

<td><?= htmlspecialchars($w['email']) ?></td>

<td>₦<?= number_format($w['amount'],2) ?></td>

<td>

<?php

$badge = match($w['status']) {

    'approved' => 'success',

    'rejected' => 'danger',

    'paid' => 'primary',

    default => 'warning'

};

?>

<span class="badge bg-<?= $badge ?>">

<?= strtoupper($w['status']) ?>

</span>

</td>

<td><?= htmlspecialchars($w['request_date']) ?></td>

<td>

<?php if ($w['status'] === 'pending'): ?>

<form method="POST">

<?= csrfField(); ?>

<input
type="hidden"
name="withdrawal_id"
value="<?= $w['id'] ?>">

<button
type="submit"
name="action"
value="approved"
class="btn btn-success btn-sm">

Approve

</button>

<button
type="submit"
name="action"
value="rejected"
class="btn btn-danger btn-sm">

Reject

</button>

</form>

<?php else: ?>

<span class="text-muted">Processed</span>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php include '../includes/footer.php'; ?>