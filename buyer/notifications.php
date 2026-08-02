<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('buyer');

$userId = $_SESSION['user_id'];
/*
|--------------------------------------------------------------------------
| Mark Notifications As Read
|--------------------------------------------------------------------------
*/

$markRead = $pdo->prepare("
    UPDATE notifications
    SET status='read'
    WHERE user_id = ?
      AND status='unread'
");

$markRead->execute([$userId]);
/*
|--------------------------------------------------------------------------
| Load Buyer Notifications
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

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">

                Notifications

            </h2>

            <p class="text-muted mb-0">

                View all your recent notifications.

            </p>

        </div>

        <span class="badge bg-success fs-6">

            <?= count($notifications) ?>

            Notification<?= count($notifications)!=1 ? 's' : '' ?>

        </span>

    </div>
    <div class="card border-0 shadow-sm">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">

            My Notifications

        </h5>

    </div>

    <div class="card-body p-0">

        <?php if (empty($notifications)): ?>

            <div class="text-center py-5">

                <i class="bi bi-bell display-4 text-muted"></i>

                <h5 class="mt-3">

                    No Notifications

                </h5>

                <p class="text-muted mb-0">

                    You don't have any notifications yet.

                </p>

            </div>

        <?php else: ?>

            <div class="list-group list-group-flush">

                <?php foreach ($notifications as $notification): ?>

                    <div class="list-group-item py-3">

                        <div class="d-flex justify-content-between align-items-start">

                            <div class="me-3">

                                <h6 class="mb-1">

                                    <?= htmlspecialchars($notification['title']) ?>

                                </h6>

                                <p class="mb-2 text-muted">

                                    <?= nl2br(htmlspecialchars($notification['message'])) ?>

                                </p>

                                <small class="text-secondary">

                                    <?= date('F d, Y h:i A', strtotime($notification['created_at'])) ?>

                                </small>

                            </div>

                            <div>

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