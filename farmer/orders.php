<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('farmer');

$stmt = $pdo->prepare("

SELECT

o.*,

u.fullname AS buyer_name,

u.phone,

u.email,

p.product_name,

p.image,

p.unit

FROM orders o

JOIN users u
ON o.buyer_id=u.id

JOIN products p
ON o.product_id=p.id

WHERE o.farmer_id=?

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

<h2 class="mb-4">
Incoming Orders
</h2>
<?php if(empty($orders)): ?>

<div class="alert alert-info">

No orders have been received yet.

</div>

<?php else: ?>

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-success">

<tr>

<th>#</th>

<th>Product</th>

<th>Buyer</th>

<th>Quantity</th>

<th>Total</th>

<th>Status</th>

<th>Date</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php foreach($orders as $order): ?>

<tr>

<td><?= $order['id'] ?></td>

<td>

<strong>

<?= htmlspecialchars($order['product_name']) ?>

</strong>

</td>

<td>

<?= htmlspecialchars($order['buyer_name']) ?>

<br>

<small>

<?= htmlspecialchars($order['phone']) ?>

</small>

</td>

<td>

<?= number_format($order['quantity']) ?>

<?= htmlspecialchars($order['unit']) ?>

</td>

<td>

₦<?= number_format($order['total_amount'],2) ?>

</td>

<td>

<?php

switch ($order['status']) {

    case 'pending':

        echo '<span class="badge bg-warning">Pending</span>';

        break;

    case 'farmer_approved':

        echo '<span class="badge bg-info">Awaiting LGA Approval</span>';

        break;

    case 'accepted':

        echo '<span class="badge bg-primary">Awaiting Trucker</span>';

        break;

    case 'in_transit':

        echo '<span class="badge bg-info">🚚 In Transit</span>';

        break;

    case 'delivered':

        echo '<span class="badge bg-success">Delivered</span>';

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

<?= date('d M Y',strtotime($order['created_at'])) ?>

</td>

<td>

<?php if($order['status']=='pending'): ?>

<a

href="accept_order.php?id=<?= $order['id'] ?>"

class="btn btn-success btn-sm">

Accept

</a>

<a

href="reject_order.php?id=<?= $order['id'] ?>"

class="btn btn-danger btn-sm">

Reject

</a>

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