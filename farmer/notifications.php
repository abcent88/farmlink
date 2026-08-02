<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole(['farmer']);

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT *
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$userId]);

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Mark unread notifications as read */

$pdo->prepare("
    UPDATE notifications
    SET status='read'
    WHERE user_id=?
    AND status='unread'
")->execute([$userId]);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-4">

<div class="card shadow-sm border-0">

<div class="card-header bg-success text-white">

<h4 class="mb-0">

Notifications

</h4>

</div>

<div class="card-body">
    <?php if (empty($notifications)): ?>

    <div class="text-center py-5">

        <i class="bi bi-bell display-3 text-muted"></i>

        <h4 class="mt-3">

            No Notifications

        </h4>

        <p class="text-muted">

            You don't have any notifications yet.

        </p>

    </div>

<?php else: ?>

<div class="list-group">

<?php foreach ($notifications as $notification): ?>

<div class="list-group-item py-3">

    <div class="d-flex justify-content-between align-items-start">

        <div>

            <h5 class="mb-1">

                <?= htmlspecialchars($notification['title']) ?>

            </h5>

            <p class="mb-2">

                <?= nl2br(htmlspecialchars($notification['message'])) ?>

            </p>

            <small class="text-muted">

                <?= date('d M Y h:i A', strtotime($notification['created_at'])) ?>

            </small>

        </div>

        <div>

            <?php if ($notification['status'] === 'unread'): ?>

                <span class="badge bg-danger">

                    Unread

                </span>

            <?php else: ?>

                <span class="badge bg-success">

                    Read

                </span>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>
</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>