<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole(['super_admin','lga_admin']);

$role   = $_SESSION['role'];
$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Logged In Admin
|--------------------------------------------------------------------------
*/

$admin = [];

if ($role === 'lga_admin') {

    $stmt = $pdo->prepare("
        SELECT lga
        FROM users
        WHERE id=?
    ");

    $stmt->execute([$userId]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| Delivery Query
|--------------------------------------------------------------------------
*/

$params = [];

$sql = "

SELECT

d.id,

d.status,

d.created_at,

o.id AS order_id,

o.quantity,

o.total_amount,

o.status AS order_status,

buyer.fullname AS buyer_name,

buyer.phone AS buyer_phone,

buyer.lga AS buyer_lga,

farmer.fullname AS farmer_name,
farmer.phone AS farmer_phone,
farmer.lga AS farmer_lga,

p.product_name,
p.category,
p.image,

trucker.fullname AS trucker_name,
trucker.phone AS trucker_phone

FROM deliveries d

JOIN orders o
ON o.id=d.order_id

JOIN users buyer
ON buyer.id=o.buyer_id

JOIN users farmer
ON farmer.id=o.farmer_id

JOIN products p
ON p.id=o.product_id

LEFT JOIN users trucker
ON trucker.id=d.trucker_id

";

/*
|--------------------------------------------------------------------------
| LGA Restriction
|--------------------------------------------------------------------------
*/

if($role=='lga_admin'){

    $sql .= "

    WHERE farmer.lga=?

    ";

    $params[] = $admin['lga'];

}

$sql .= "

ORDER BY d.id DESC

";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$statsSql = "

SELECT

COUNT(*) AS total_deliveries,

SUM(d.status='assigned') AS assigned,

SUM(d.status='approved') AS approved,

SUM(d.status='accepted') AS accepted,

SUM(d.status='in_transit') AS in_transit,

SUM(d.status='delivered') AS delivered,

SUM(d.status='completed') AS completed

FROM deliveries d

JOIN orders o
ON o.id = d.order_id

JOIN users farmer
ON farmer.id = o.farmer_id

";

$statsParams = [];

if($role=='lga_admin'){

    $statsSql .= "

    WHERE farmer.lga=?

    ";

    $statsParams[] = $admin['lga'];

}
$statsStmt = $pdo->prepare($statsSql);

$statsStmt->execute($statsParams);

$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$totalDeliveries = $stats['total_deliveries'] ?? 0;

$assigned = $stats['assigned'] ?? 0;

$approved = $stats['approved'] ?? 0;

$accepted = $stats['accepted'] ?? 0;

$inTransit = $stats['in_transit'] ?? 0;

$delivered = $stats['delivered'] ?? 0;

$completed = $stats['completed'] ?? 0;

include '../includes/header.php';
include '../includes/navbar.php';

?>
<div class="container mt-4">

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            Delivery Management
        </h2>

        <p class="text-muted mb-0">
            Monitor deliveries across the FarmLink platform.
        </p>

    </div>

</div>
<?php if(isset($_GET['success'])): ?>

<div class="alert alert-success alert-dismissible fade show">

<?php

switch($_GET['success']){

case 'approved':

echo "Delivery approved successfully.";

break;

case 'completed':

echo "Delivery completed successfully.";

break;

}

?>

<button
class="btn-close"
data-bs-dismiss="alert">
</button>

</div>

<?php endif; ?>

<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">

        <div class="card shadow-sm border-0">

            <div class="card-body text-center">

                <h2 class="fw-bold text-success">

                    <?= number_format($totalDeliveries) ?>

                </h2>

                <div class="text-muted">

                    Total Deliveries

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card shadow-sm border-0">

            <div class="card-body text-center">

                <h2 class="fw-bold text-warning">

                    <?= number_format($assigned) ?>

                </h2>

                <div class="text-muted">

                    Assigned

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card shadow-sm border-0">

            <div class="card-body text-center">

                <h2 class="fw-bold text-primary">

                    <?= number_format($inTransit) ?>

                </h2>

                <div class="text-muted">

                    In Transit

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card shadow-sm border-0">

            <div class="card-body text-center">

                <h2 class="fw-bold text-success">

                    <?= number_format($completed) ?>

                </h2>

                <div class="text-muted">

                    Completed

                </div>

            </div>

        </div>

    </div>

</div>
<!-- Search & Filters -->

<form method="GET" class="card shadow-sm mb-4">

    <div class="card-body">

        <div class="row g-3">

            <div class="col-lg-4">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search buyer, farmer or product..."
                    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

            </div>

            <div class="col-lg-3">

                <select
                    name="status"
                    class="form-select">

                    <option value="">All Delivery Status</option>

                    <option value="assigned"
                        <?= (($_GET['status'] ?? '')=='assigned')?'selected':'' ?>>
                        Assigned
                    </option>

                    <option value="approved"
                        <?= (($_GET['status'] ?? '')=='approved')?'selected':'' ?>>
                        Approved
                    </option>

                    <option value="accepted"
                        <?= (($_GET['status'] ?? '')=='accepted')?'selected':'' ?>>
                        Accepted
                    </option>

                    <option value="in_transit"
                        <?= (($_GET['status'] ?? '')=='in_transit')?'selected':'' ?>>
                        In Transit
                    </option>

                    <option value="delivered"
                        <?= (($_GET['status'] ?? '')=='delivered')?'selected':'' ?>>
                        Delivered
                    </option>

                    <option value="completed"
                        <?= (($_GET['status'] ?? '')=='completed')?'selected':'' ?>>
                        Completed
                    </option>

                </select>

            </div>

            <div class="col-lg-3">

                <button
                    type="submit"
                    class="btn btn-success w-100">

                    Search

                </button>

            </div>

            <div class="col-lg-2">

                <a
                    href="deliveries.php"
                    class="btn btn-outline-secondary w-100">

                    Reset

                </a>

            </div>

        </div>

    </div>

</form>
<div class="card shadow-sm">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">
            Delivery Records
        </h5>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover table-bordered align-middle mb-0">

                <thead class="table-success">

                <tr>

                    <th>ID</th>

                    <th>Product</th>

                    <th>Buyer</th>

                    <th>Farmer</th>

                    <th>Trucker</th>

                    <th>Quantity</th>

                    <th>Total</th>

                    <th>Status</th>

                    <th>Date</th>

                    <th width="120">
                        Action
                    </th>

                </tr>

                </thead>

                <tbody>

                <?php if(empty($deliveries)): ?>

<tr>
    <td colspan="10" class="text-center py-5 text-muted">
        No deliveries found.
    </td>
</tr>

<?php else: ?>

<?php foreach($deliveries as $delivery):

$statusBadge = match($delivery['status']){

    'assigned'    => '<span class="badge bg-warning text-dark">Assigned</span>',
    'approved'    => '<span class="badge bg-primary">Approved</span>',
    'accepted'    => '<span class="badge bg-info text-dark">Accepted</span>',
    'in_transit'  => '<span class="badge bg-warning">In Transit</span>',
    'delivered'   => '<span class="badge bg-success">Delivered</span>',
    'completed'   => '<span class="badge bg-success">Completed</span>',
    default       => '<span class="badge bg-secondary">Unknown</span>'

};

?>

<tr>

<td><?= $delivery['id'] ?></td>

<td>
    <strong><?= htmlspecialchars($delivery['product_name']) ?></strong><br>
    <small class="text-muted"><?= htmlspecialchars($delivery['category']) ?></small>
</td>

<td><?= htmlspecialchars($delivery['buyer_name']) ?></td>

<td><?= htmlspecialchars($delivery['farmer_name']) ?></td>

<td>
<?= $delivery['trucker_name']
    ? htmlspecialchars($delivery['trucker_name'])
    : '<span class="text-muted">Not Assigned</span>' ?>
</td>

<td><?= number_format($delivery['quantity'],2) ?></td>

<td>₦<?= number_format($delivery['total_amount'],2) ?></td>

<td><?= $statusBadge ?></td>

<td><?= date('d M Y', strtotime($delivery['created_at'])) ?></td>

<td>
<button
class="btn btn-success btn-sm"
data-bs-toggle="modal"
data-bs-target="#deliveryModal<?= $delivery['id'] ?>">
View
</button>
</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

<!-- ALL DELIVERY MODALS -->
<?php foreach($deliveries as $delivery):

$statusBadge = match($delivery['status']){

    'assigned'    => '<span class="badge bg-warning text-dark">Assigned</span>',
    'approved'    => '<span class="badge bg-primary">Approved</span>',
    'accepted'    => '<span class="badge bg-info text-dark">Accepted</span>',
    'in_transit'  => '<span class="badge bg-warning">In Transit</span>',
    'delivered'   => '<span class="badge bg-success">Delivered</span>',
    'completed'   => '<span class="badge bg-success">Completed</span>',
    default       => '<span class="badge bg-secondary">Unknown</span>'

};

$productImage = !empty($delivery['image'])
    ? '../uploads/products/'.$delivery['image']
    : '../assets/images/no-image.png';

?>

<div class="modal fade"
id="deliveryModal<?= $delivery['id'] ?>"
tabindex="-1"
aria-hidden="true">

<div class="modal-dialog modal-lg modal-dialog-scrollable">

<div class="modal-content">

<div class="modal-header bg-success text-white">

<h5 class="modal-title">
Delivery #<?= $delivery['id'] ?>
</h5>

<button
type="button"
class="btn-close btn-close-white"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<div class="row">

<div class="col-md-4 text-center">

<img
src="<?= htmlspecialchars($productImage) ?>"
class="img-fluid rounded shadow"
style="max-height:250px;object-fit:cover;"
alt="<?= htmlspecialchars($delivery['product_name']) ?>">

</div>

<div class="col-md-8">

<h4><?= htmlspecialchars($delivery['product_name']) ?></h4>
<hr>

<p><strong>Delivery ID:</strong> <?= $delivery['id'] ?></p>
<p><strong>Order ID:</strong> <?= $delivery['order_id'] ?></p>
<p><strong>Buyer:</strong> <?= htmlspecialchars($delivery['buyer_name']) ?></p>
<p><strong>Buyer Phone:</strong> <?= htmlspecialchars($delivery['buyer_phone']) ?></p>
<p><strong>Farmer:</strong> <?= htmlspecialchars($delivery['farmer_name']) ?></p>
<p><strong>Farmer Phone:</strong> <?= htmlspecialchars($delivery['farmer_phone']) ?></p>

<p><strong>Trucker:</strong>
<?= $delivery['trucker_name']
? htmlspecialchars($delivery['trucker_name'])
: '<span class="text-muted">Not Assigned</span>' ?>
</p>

<p><strong>Trucker Phone:</strong>
<?= $delivery['trucker_phone']
? htmlspecialchars($delivery['trucker_phone'])
: '-' ?>
</p>

<p><strong>Category:</strong> <?= htmlspecialchars($delivery['category']) ?></p>

<p><strong>Quantity:</strong> <?= number_format($delivery['quantity'],2) ?></p>

<p><strong>Total:</strong> ₦<?= number_format($delivery['total_amount'],2) ?></p>

<p><strong>Order Status:</strong> <?= htmlspecialchars($delivery['order_status']) ?></p>

<p><strong>Delivery Status:</strong> <?= $statusBadge ?></p>

<p><strong>Created:</strong>
<?= date('d M Y h:i A', strtotime($delivery['created_at'])) ?>
</p>

</div>

</div>

</div>

<div class="modal-footer">

<?php if($delivery['status']=='assigned'): ?>

<form method="POST" action="approve_delivery.php">

<?= csrfField(); ?>

<input
type="hidden"
name="delivery_id"
value="<?= $delivery['id'] ?>">

<button
type="submit"
class="btn btn-primary">

Approve Delivery

</button>

</form>

<?php elseif($delivery['status']=='delivered'): ?>

<form method="POST" action="complete_delivery.php">

<?= csrfField(); ?>

<input
type="hidden"
name="delivery_id"
value="<?= $delivery['id'] ?>">

<button
type="submit"
class="btn btn-success">

Mark Completed

</button>

</form>

<?php endif; ?>

<button
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Close

</button>

</div>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

<?php include '../includes/footer.php'; ?>