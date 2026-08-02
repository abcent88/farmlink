<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole(['super_admin', 'lga_admin']);

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];

/*
|--------------------------------------------------------------------------
| Mark all unread notifications as read
|--------------------------------------------------------------------------
*/

$mark = $pdo->prepare("
    UPDATE notifications
    SET status = 'read'
    WHERE user_id = ?
      AND status = 'unread'
");

$mark->execute([$userId]);

/*
|--------------------------------------------------------------------------
| Load notifications
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        message,
        status,
        created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$userId]);

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Counts
|--------------------------------------------------------------------------
*/

$totalNotifications = count($notifications);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">

                Notifications

            </h2>

            <p class="text-muted mb-0">

                <?= $role === 'super_admin'
                    ? 'Super Admin Notifications'
                    : 'LGA Admin Notifications' ?>

            </p>

        </div>

        <span class="badge bg-success fs-6">

            <?= $totalNotifications ?>

            Notification<?= $totalNotifications != 1 ? 's' : '' ?>

        </span>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-body">
            <?php if (empty($notifications)): ?>

    <div class="text-center py-5">

        <i class="bi bi-bell display-1 text-muted"></i>

        <h4 class="mt-3">

            No Notifications

        </h4>

        <p class="text-muted mb-0">

            You don't have any notifications yet.

        </p>

    </div>

<?php else: ?>

    <div class="list-group list-group-flush">

        <?php foreach ($notifications as $notification): ?>

            <div class="list-group-item py-4">

                <div class="d-flex justify-content-between align-items-start">

                    <div class="flex-grow-1">

                        <h5 class="mb-2">

                            <?= htmlspecialchars($notification['title']) ?>

                        </h5>

                        <p class="mb-2">

                            <?= nl2br(htmlspecialchars($notification['message'])) ?>

                        </p>

                        <small class="text-muted">

                            <i class="bi bi-clock"></i>

                            <?= date('d M Y, h:i A', strtotime($notification['created_at'])) ?>

                        </small>

                    </div>

                    <div class="ms-3">

                        <?php if ($notification['status'] === 'unread'): ?>

                            <span class="badge bg-danger">

                                New

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