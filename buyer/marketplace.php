<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/lgas.php';

requireRole('buyer');

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$lga      = trim($_GET['lga'] ?? '');

$params = [];

$sql = "

SELECT

    p.*,

    u.fullname AS farmer_name,

    u.lga,

    u.town,

    fp.farm_name

FROM products p

JOIN users u
    ON p.farmer_id = u.id

LEFT JOIN farmer_profiles fp
    ON fp.user_id = u.id

WHERE

    p.status='approved'

AND p.quantity > 0

AND fp.verification_status='verified'

";

if ($search !== '') {

    $sql .= "

    AND (

        p.product_name LIKE ?

        OR u.fullname LIKE ?

    )

    ";

    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($category !== '') {

    $sql .= " AND p.category = ? ";

    $params[] = $category;
}

if ($lga !== '') {

    $sql .= " AND u.lga = ? ";

    $params[] = $lga;
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container py-5">

    <h2 class="fw-bold mb-4">
        Marketplace
    </h2>

    <form method="GET" class="card shadow-sm mb-5">

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-4">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search product or farmer"
                        value="<?= htmlspecialchars($search) ?>">

                </div>

                <div class="col-md-3">

                    <input
                        type="text"
                        name="category"
                        class="form-control"
                        placeholder="Category"
                        value="<?= htmlspecialchars($category) ?>">

                </div>

                <select name="lga" class="form-control" required>

<option value="">Select LGA</option>

<?php foreach ($lgas as $row): ?>

<option
    value="<?= htmlspecialchars($row['lga']) ?>"
    <?= (($currentLga ?? '') == $row['lga']) ? 'selected' : '' ?>>

    <?= htmlspecialchars($row['lga']) ?>

</option>

<?php endforeach; ?>

</select>

                <div class="col-md-2 d-grid">

                    <button class="btn btn-success">
                        Search
                    </button>

                </div>

            </div>

        </div>

    </form>

    <div class="row">

    <?php if (count($products) > 0): ?>

        <?php foreach ($products as $product): ?>

            <?php

            $image = !empty($product['image'])
                ? "../uploads/products/" . $product['image']
                : "../assets/images/no-image.png";

            ?>

            <div class="col-lg-4 col-md-6 mb-4">

                <div class="card h-100 shadow-sm border-0">

                    <img
                        src="<?= htmlspecialchars($image) ?>"
                        class="card-img-top"
                        alt="<?= htmlspecialchars($product['product_name']) ?>"
                        style="height:230px;object-fit:cover;">

                    <div class="card-body">

                        <h5 class="fw-bold">

                            <?= htmlspecialchars($product['product_name']) ?>

                        </h5>

                        <span class="badge bg-success mb-2">

                            <?= htmlspecialchars($product['category']) ?>

                        </span>

                        <p class="mb-1">

                            <strong>Farm:</strong>

                            <?= htmlspecialchars($product['farm_name'] ?: 'Not Provided') ?>

                        </p>

                        <p class="mb-1">

                            <strong>Farmer:</strong>

                            <?= htmlspecialchars($product['farmer_name']) ?>

                        </p>

                        <select name="lga" class="form-control" required>

<option value="">Select LGA</option>

<?php foreach ($lgas as $row): ?>

<option
    value="<?= htmlspecialchars($row['lga']) ?>"
    <?= (($currentLga ?? '') == $row['lga']) ? 'selected' : '' ?>>

    <?= htmlspecialchars($row['lga']) ?>

</option>

<?php endforeach; ?>

</select>

                        <p class="mb-1">

                            <strong>Available:</strong>

                            <?= number_format($product['quantity']) ?>

                            <?= htmlspecialchars($product['unit']) ?>

                        </p>

                        <?php if ($product['quantity'] <= 10): ?>

                            <span class="badge bg-danger mb-2">

                                Low Stock

                            </span>

                        <?php endif; ?>

                        <h5 class="text-success mt-2">

                            ₦<?= number_format($product['price'],2) ?>

                        </h5>

                        <p class="text-muted">

                            <?= nl2br(htmlspecialchars(substr($product['description'],0,120))) ?>

                            <?= strlen($product['description']) > 120 ? '...' : '' ?>

                        </p>

                    </div>

                    <div class="card-footer bg-white border-0">

                        <a
                            href="request_order.php?id=<?= $product['id'] ?>"
                            class="btn btn-success w-100">

                            Request Product

                        </a>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="col-12">

            <div class="alert alert-info text-center">

                No products match your search.

            </div>

        </div>

    <?php endif; ?>

    </div>

</div>

<?php include '../includes/footer.php'; ?>