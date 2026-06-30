<?php

require_once 'config/database.php';

$search = trim($_GET['search'] ?? '');

$farmerCount = $pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE role='farmer'
")->fetchColumn();

$buyerCount = $pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE role='buyer'
")->fetchColumn();

$truckerCount = $pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE role='trucker'
")->fetchColumn();

$productCount = $pdo->query("
    SELECT COUNT(*)
    FROM products
    WHERE status='approved'
")->fetchColumn();

$deliveryCount = $pdo->query("
    SELECT COUNT(*)
    FROM deliveries
    WHERE status='completed'
")->fetchColumn();

$sql = "
    SELECT
        p.*,
        u.fullname AS farmer_name
    FROM products p
    JOIN users u
        ON p.farmer_id = u.id
    WHERE p.status='approved'
";

$params = [];

if ($search !== '') {

    $sql .= "
        AND (
            p.product_name LIKE ?
            OR p.category LIKE ?
            OR p.description LIKE ?
        )
    ";

    $term = "%{$search}%";

    $params = [
        $term,
        $term,
        $term
    ];
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$products = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="hero">
    <div class="container text-center">


    <h1 class="display-4 fw-bold">
        Welcome to FarmLink
    </h1>

    <p class="lead">
        Connecting Farmers, Buyers and Truckers Across Nigeria
    </p>

    <form method="GET" class="row justify-content-center mt-4">

        <div class="col-md-5">
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                class="form-control"
                placeholder="Search products...">
        </div>
        <div class="mt-4">


<a href="/projects/farmlink/register.php"
   class="btn btn-success btn-lg me-2">
    Join FarmLink
</a>

<a href="#products"
   class="btn btn-warning btn-lg">
    Browse Products
</a>


</div>


        <div class="col-md-2">
            <button class="btn btn-warning w-100">
                Search
            </button>
        </div>

    </form>

</section>

<div class="container mt-5">


<div class="row text-center mb-5">

    <div class="col-md-2">
        <div class="card shadow">
            <div class="card-body">
                <h3><?= $farmerCount ?></h3>
                <p>Farmers</p>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card shadow">
            <div class="card-body">
                <h3><?= $buyerCount ?></h3>
                <p>Buyers</p>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card shadow">
            <div class="card-body">
                <h3><?= $truckerCount ?></h3>
                <p>Truckers</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow">
            <div class="card-body">
                <h3><?= $productCount ?></h3>
                <p>Approved Products</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow">
            <div class="card-body">
                <h3><?= $deliveryCount ?></h3>
                <p>Completed Deliveries</p>
            </div>
        </div>
    </div>

</div>
<div class="row mb-5">

<div class="col-12 text-center mb-4">
    <h2>How FarmLink Works</h2>
</div>

<div class="col-md-4">
    <div class="card shadow text-center p-3">
        <h4>🌱 Farmers</h4>
        <p>
            Farmers list products and receive orders.
        </p>
    </div>
</div>

<div class="col-md-4">
    <div class="card shadow text-center p-3">
        <h4>🛒 Buyers</h4>
        <p>
            Buyers search products and place orders.
        </p>
    </div>
</div>

<div class="col-md-4">
    <div class="card shadow text-center p-3">
        <h4>🚚 Truckers</h4>
        <p>
            Truckers transport products to buyers.
        </p>
    </div>
</div>


</div>

<h2 id="products" class="mb-4">
    Available Products
</h2>
<?php if(empty($products)): ?>
    <div class="alert alert-warning text-center">
        No products found.
    </div>
<?php endif; ?>
<div class="row">

    <?php foreach($products as $product): ?>

        <div class="col-md-4 mb-4">

            <div class="card h-100 shadow">

                <?php if(!empty($product['image'])): ?>

                    <img
                        src="/projects/farmlink/uploads/products/<?= htmlspecialchars($product['image']) ?>"
                        class="card-img-top"
                        style="height:220px;object-fit:cover;">

                <?php endif; ?>

                <div class="card-body">

                    <h5>
                        <?= htmlspecialchars($product['product_name']) ?>
                    </h5>

                    <p>
                        Farmer:
                        <?= htmlspecialchars($product['farmer_name']) ?>
                    </p>

                    <p>
                        <?= htmlspecialchars($product['quantity']) ?>
                        <?= htmlspecialchars($product['unit']) ?>
                    </p>

                    <p>
    ₦<?= number_format($product['price'],2) ?>
</p>

<a href="/projects/farmlink/login.php"
   class="btn btn-success w-100">
    View Product
</a>
                </div>

            </div>

        </div>

    <?php endforeach; ?>

</div>


</div>

<?php include 'includes/footer.php'; ?>

