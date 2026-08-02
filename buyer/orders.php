<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('buyer');

/*
|--------------------------------------------------------------------------
| Load Buyer's Orders
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT

    o.*,

    p.product_name,
    p.image,
    p.unit,

    u.fullname AS farmer_name,
    u.phone AS farmer_phone,

    d.id AS delivery_id,
    d.status AS delivery_status

FROM orders o

JOIN products p
    ON p.id = o.product_id

JOIN users u
    ON u.id = o.farmer_id

LEFT JOIN deliveries d
    ON d.order_id = o.id

WHERE o.buyer_id = ?

ORDER BY o.created_at DESC
");

$stmt->execute([
    $_SESSION['user_id']
]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            My Orders
        </h2>

    </div>

    <?php if (empty($orders)): ?>

        <div class="alert alert-info">

            You have not placed any orders yet.

        </div>

    <?php else: ?>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-success">

                    <tr>

                        <th>Image</th>

                        <th>Product</th>

                        <th>Farmer</th>

                        <th>Quantity</th>

                        <th>Total</th>

                        <th>Order Status</th>

                        <th>Delivery</th>

                        <th>Date</th>

                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach ($orders as $order): ?>

                    <?php

                    $image = !empty($order['image'])
                        ? '../uploads/products/' . $order['image']
                        : '../assets/images/no-image.png';

                    ?>

                    <tr>

                        <td width="90">

                            <img
                                src="<?= htmlspecialchars($image) ?>"
                                alt="<?= htmlspecialchars($order['product_name']) ?>"
                                class="img-fluid rounded"
                                style="width:70px;height:70px;object-fit:cover;">

                        </td>

                        <td>

                            <strong>

                                <?= htmlspecialchars($order['product_name']) ?>

                            </strong>

                        </td>

                        <td>

                            <strong>

                                <?= htmlspecialchars($order['farmer_name']) ?>

                            </strong>

                            <br>

                            <small class="text-muted">

                                <?= htmlspecialchars($order['farmer_phone']) ?>

                            </small>

                        </td>

                        <td>

                            <?= number_format($order['quantity']) ?>

                            <?= htmlspecialchars($order['unit']) ?>

                        </td>

                        <td>

                            ₦<?= number_format($order['total_amount'], 2) ?>

                        </td>

                        <td>

                            <?php

switch ($order['status']) {

    case 'pending':

        echo '<span class="badge bg-warning text-dark">Pending Farmer Approval</span>';

        break;

    case 'farmer_approved':

        echo '<span class="badge bg-info">Awaiting LGA Approval</span>';

        break;

    case 'accepted':

        echo '<span class="badge bg-primary">Preparing Delivery</span>';

        break;

    case 'in_transit':

        echo '<span class="badge bg-info">🚚 In Transit</span>';

        break;

    case 'delivered':

        echo '<span class="badge bg-success">Delivered - Confirm Receipt</span>';

        break;

    case 'completed':

        echo '<span class="badge bg-dark">Completed</span>';

        break;

    case 'rejected':

        echo '<span class="badge bg-danger">Rejected</span>';

        break;

}
?>

                        </td>

                        <td>

                            <?php

                            if (!$order['delivery_status']) {

                                echo '<span class="badge bg-secondary">Not Assigned</span>';

                            } else {

                                switch ($order['delivery_status']) {

                                    case 'assigned':
                                        echo '<span class="badge bg-warning text-dark">Assigned</span>';
                                        break;

                                    case 'accepted':
                                        echo '<span class="badge bg-info">Accepted</span>';
                                        break;

                                    case 'in_transit':
                                        echo '<span class="badge bg-primary">In Transit</span>';
                                        break;

                                    case 'delivered':
                                        echo '<span class="badge bg-success">Delivered</span>';
                                        break;

                                    case 'completed':
                                        echo '<span class="badge bg-dark">Completed</span>';
                                        break;

                                    default:
                                        echo '<span class="badge bg-secondary">'
                                            . htmlspecialchars($order['delivery_status'])
                                            . '</span>';

                                }

                            }

                            ?>

                        </td>

                        <td>

                            <?= date('d M Y', strtotime($order['created_at'])) ?>

                        </td>

                        <td>

                            <?php if ($order['delivery_status'] == 'delivered'): ?>

                                <a
                                    href="confirm_delivery.php?id=<?= $order['delivery_id'] ?>"
                                    class="btn btn-success btn-sm"
                                    onclick="return confirm('Confirm you have received this order?');">

                                    Confirm Delivery

                                </a>

                            <?php elseif ($order['status'] == 'rejected'): ?>

                                <button
                                    class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#reason<?= $order['id'] ?>">

                                    View Reason

                                </button>

                                <div
                                    class="modal fade"
                                    id="reason<?= $order['id'] ?>"
                                    tabindex="-1">

                                    <div class="modal-dialog">

                                        <div class="modal-content">

                                            <div class="modal-header bg-danger text-white">

                                                <h5 class="modal-title">

                                                    Order Rejected

                                                </h5>

                                                <button
                                                    class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal">

                                                </button>

                                            </div>

                                            <div class="modal-body">

                                                <?= nl2br(htmlspecialchars($order['rejection_reason'])) ?>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            <?php elseif ($order['delivery_status'] == 'completed'): ?>

                                <span class="text-success fw-bold">

                                    ✔ Completed

                                </span>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>