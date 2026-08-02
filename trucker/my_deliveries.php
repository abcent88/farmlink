<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('trucker');

$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| My Deliveries
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT

    d.id,
    d.status,
    d.created_at,

    o.id AS order_id,
    o.quantity,
    o.total_amount,

    p.product_name,
    p.unit,

    u.fullname AS farmer_name

FROM deliveries d

JOIN orders o
    ON d.order_id = o.id

JOIN products p
    ON o.product_id = p.id

JOIN users u
    ON o.farmer_id = u.id

WHERE d.trucker_id = ?

ORDER BY d.created_at DESC
");

$stmt->execute([$userId]);

$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

    <div class="card shadow">

        <div class="card-header bg-success text-white">

            <h3 class="mb-0">
                🚚 My Deliveries
            </h3>

        </div>

        <div class="card-body">

            <?php if (!empty($deliveries)): ?>

                <div class="table-responsive">

                    <table class="table table-hover table-bordered align-middle">

                        <thead class="table-success">

                            <tr>

                                <th>#</th>

                                <th>Product</th>

                                <th>Farmer</th>

                                <th>Quantity</th>

                                <th>Total</th>

                                <th>Status</th>

                                <th width="240">Action</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($deliveries as $delivery): ?>

                            <tr>

                                <td>

                                    #<?= $delivery['id']; ?>

                                </td>

                                <td>

                                    <strong>

                                        <?= htmlspecialchars($delivery['product_name']); ?>

                                    </strong>

                                </td>

                                <td>

                                    <?= htmlspecialchars($delivery['farmer_name']); ?>

                                </td>

                                <td>

                                    <?= number_format($delivery['quantity']); ?>

                                    <?= htmlspecialchars($delivery['unit']); ?>

                                </td>

                                <td>

                                    ₦<?= number_format($delivery['total_amount'], 2); ?>

                                </td>

                                <td>

                                    <?php

                                    switch ($delivery['status']) {

                                        case 'assigned':
                                            echo '<span class="badge bg-warning text-dark">Awaiting Acceptance</span>';
                                            break;

                                        case 'accepted':
                                            echo '<span class="badge bg-primary">Accepted</span>';
                                            break;

                                        case 'in_transit':
                                            echo '<span class="badge bg-info">In Transit</span>';
                                            break;

                                        case 'delivered':
                                            echo '<span class="badge bg-success">Delivered</span>';
                                            break;

                                        case 'completed':
                                            echo '<span class="badge bg-dark">Completed</span>';
                                            break;

                                        default:
                                            echo '<span class="badge bg-secondary">'
                                                . htmlspecialchars($delivery['status']) .
                                                '</span>';
                                    }

                                    ?>

                                </td>

                                <td>

                                    <?php if ($delivery['status'] === 'accepted'): ?>

                                        <a
                                            href="start_delivery.php?id=<?= $delivery['id']; ?>"
                                            class="btn btn-primary btn-sm">

                                            🚚 Start Journey

                                        </a>

                                    <?php elseif ($delivery['status'] === 'in_transit'): ?>

                                        <a
                                            href="mark_delivered.php?id=<?= $delivery['id']; ?>"
                                            class="btn btn-success btn-sm">

                                            📦 Mark Delivered

                                        </a>

                                    <?php elseif ($delivery['status'] === 'delivered'): ?>

                                        <span class="text-success fw-bold">

                                            Waiting for Buyer Confirmation

                                        </span>

                                    <?php elseif ($delivery['status'] === 'completed'): ?>

                                        <span class="text-muted">

                                            ✅ Completed

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            —

                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="alert alert-info mb-0">

                    You currently have no assigned deliveries.

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>