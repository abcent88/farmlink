<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';
require_once '../includes/error_handler.php';

requireRole('farmer');

$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    appFail('Invalid order.');
}

/*
|--------------------------------------------------------------------------
| Fetch Order
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        o.id,
        o.quantity,
        o.status,
        p.product_name
    FROM orders o
    JOIN products p
        ON p.id = o.product_id
    WHERE o.id = ?
");

$stmt->execute([$orderId]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    appFail('Order not found.');
}

if ($order['status'] !== 'accepted') {
    appFail('Order must be approved before assigning a truck.');
}

/*
|--------------------------------------------------------------------------
| Prevent Duplicate Delivery
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM deliveries
    WHERE order_id = ?
");

$stmt->execute([$orderId]);

if ($stmt->fetch()) {

    include '../includes/header.php';
    include '../includes/navbar.php';
?>

<div class="container mt-5">

    <div class="alert alert-info">
        Delivery has already been assigned for this order.
    </div>

    <a href="orders.php" class="btn btn-primary">
        Back to Orders
    </a>

</div>

<?php
    include '../includes/footer.php';
    exit;
}

/*
|--------------------------------------------------------------------------
| Available Truckers
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        fullname,
        truck_type,
        truck_capacity
    FROM users
    WHERE role='trucker'
    AND status='active'
    AND truck_capacity IS NOT NULL
    AND truck_capacity >= ?
    ORDER BY truck_capacity ASC
");

$stmt->execute([$order['quantity']]);

$truckers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Save Delivery
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $truckerId = (int)($_POST['trucker_id'] ?? 0);

    if ($truckerId <= 0) {
        appFail('Please select a trucker.');
    }

    $stmt = $pdo->prepare("
        INSERT INTO deliveries
        (
            order_id,
            trucker_id,
            status
        )
        VALUES
        (
            ?,
            ?,
            'open'
        )
    ");

    if (!$stmt->execute([$orderId, $truckerId])) {
        appFail('Unable to assign delivery.');
    }

    header("Location: orders.php?assigned=1");
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-body">

            <h2>Assign Delivery</h2>

            <hr>

            <p>
                <strong>Order:</strong>
                #<?= $order['id']; ?>
            </p>

            <p>
                <strong>Product:</strong>
                <?= htmlspecialchars($order['product_name']); ?>
            </p>

            <p>
                <strong>Quantity:</strong>
                <?= $order['quantity']; ?> Tonnes
            </p>

            <?php if (count($truckers) > 0): ?>

                <form method="POST">

                    <?= csrfField(); ?>

                    <div class="mb-3">

                        <label class="form-label">
                            Choose Trucker
                        </label>

                        <select
                            name="trucker_id"
                            class="form-control"
                            required>

                            <option value="">
                                Select Trucker
                            </option>

                            <?php foreach ($truckers as $trucker): ?>

                                <option value="<?= $trucker['id']; ?>">

                                    <?= htmlspecialchars($trucker['fullname']); ?>

                                    -

                                    <?= htmlspecialchars($trucker['truck_type']); ?>

                                    -

                                    <?= $trucker['truck_capacity']; ?> Tonnes

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-success">

                        Assign Delivery

                    </button>

                </form>

            <?php else: ?>

                <div class="alert alert-warning">

                    No available truck can carry this order.

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>