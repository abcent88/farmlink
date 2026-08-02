<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';
require_once '../includes/notify.php';

requireRole('farmer');

$orderId = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));

if ($orderId <= 0) {
    die('Invalid order.');
}

/*
|--------------------------------------------------------------------------
| Load Order
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
    o.*,
    p.product_name
FROM orders o
JOIN products p
ON p.id=o.product_id
WHERE
    o.id=?
AND
    o.farmer_id=?
LIMIT 1
");

$stmt->execute([
    $orderId,
    $_SESSION['user_id']
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Order not found.');
}

if ($order['status'] != 'pending') {
    die('This order has already been processed.');
}

/*
|--------------------------------------------------------------------------
| Reject Order
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $reason = trim($_POST['reason']);

    if ($reason == '') {
        die('Rejection reason is required.');
    }

    $stmt = $pdo->prepare("
    UPDATE orders
    SET
        status='rejected',
        rejection_reason=?
    WHERE id=?
    ");

    $stmt->execute([
        $reason,
        $orderId
    ]);

    notify(
        $pdo,
        $order['buyer_id'],
        'Order Rejected',
        'Your order for "' .
        $order['product_name'] .
        '" was rejected. Reason: ' .
        $reason
    );

    header("Location: orders.php?success=rejected");
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-lg-6">

<div class="card shadow">

<div class="card-header bg-danger text-white">

<h3>Reject Order</h3>

</div>

<div class="card-body">

<h5>

<?= htmlspecialchars($order['product_name']) ?>

</h5>

<form method="POST">

<?= csrfField(); ?>

<input
type="hidden"
name="id"
value="<?= $orderId ?>">

<div class="mb-3">

<label class="form-label">

Reason for rejection

</label>

<textarea
name="reason"
class="form-control"
rows="5"
required></textarea>

</div>

<button
type="submit"
class="btn btn-danger">

Reject Order

</button>

<a
href="orders.php"
class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>