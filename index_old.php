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

    <form method="GET" class="row g-3 justify-content-center mt-4">

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
<section class="container py-5">

<div class="row text-center">

<div class="col-md-3">

<div class="stats-card">

<h2>5,000+</h2>

<p>Farmers</p>

</div>

</div>

<div class="col-md-3">

<div class="stats-card">

<h2>18,000+</h2>

<p>Products</p>

</div>

</div>

<div class="col-md-3">

<div class="stats-card">

<h2>2,500+</h2>

<p>Buyers</p>

</div>

</div>

<div class="col-md-3">

<div class="stats-card">

<h2>420+</h2>

<p>Truckers</p>

</div>

</div>

</div>

</section>
<section class="container py-5">

<h2 class="text-center mb-4">

Browse Categories

</h2>

<div class="row g-4">

<div class="col-md-3">

<div class="card text-center p-4">

🌽

<h5 class="mt-3">

Grains

</h5>

</div>

</div>

<div class="col-md-3">

<div class="card text-center p-4">

🥔

<h5 class="mt-3">

Tubers

</h5>

</div>

</div>

<div class="col-md-3">

<div class="card text-center p-4">

🥬

<h5 class="mt-3">

Vegetables

</h5>

</div>

</div>

<div class="col-md-3">

<div class="card text-center p-4">

🍎

<h5 class="mt-3">

Fruits

</h5>

</div>

</div>

</div>

</section>

<div class="container mt-5">


<div class="row text-center g-3 mb-5">

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
<section class="container py-5">

<h2 class="text-center mb-5">

Why Choose FarmLink?

</h2>

<div class="row">

<div class="col-md-4 text-center">

🚜

<h4>

Trusted Farmers

</h4>

<p>

Verified agricultural producers.

</p>

</div>

<div class="col-md-4 text-center">

🚚

<h4>

Reliable Delivery

</h4>

<p>

Integrated trucker network.

</p>

</div>

<div class="col-md-4 text-center">

💰

<h4>

Secure Payments

</h4>

<p>

Safe transactions for everyone.

</p>

</div>

</div>

</section>
<section class="bg-success text-white py-5">

<div class="container text-center">

<h2>

Ready to Grow With FarmLink?

</h2>

<p>

Join thousands of farmers and buyers across Nigeria.

</p>

<a href="register.php"

class="btn btn-warning btn-lg">

Create Account

</a>

</div>

</section>


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

        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">

            <div class="card product-card shadow">

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

                    <p class="product-price">
₦<?= number_format($product['price'],2) ?>
</p>

<a href="/projects/farmlink/login.php"
   class="btn btn-success w-100">
    View Product
</a>
<span class="badge bg-success">
Verified
</span>

<span class="badge bg-warning text-dark">
New
</span>

<span class="badge bg-danger">
Sold Out
</span>
<div class="text-warning">

★★★★★

<small class="text-muted">

4.8 (132 Reviews)

</small>

</div>
<p class="mb-1">

👨 John Doe

</p>

<p class="text-muted">

📍 Benue State

</p>
<h4 class="product-price">

₦18,500

</h4>

<small>

per bag

</small>
<span class="badge bg-danger">

Out of Stock

</span>
<div class="d-grid gap-2">

<a
href="product.php?id=<?= $row['id']; ?>"
class="btn btn-success">

View Details

</a>

<a
href="#"
class="btn btn-outline-success">

Contact Farmer

</a>

</div>          
</div>
</div>
</div>

    <?php endforeach; ?>

</div>

<div class="featured-ribbon">

FEATURED

</div>
</div>

<?php include 'includes/footer.php'; ?>

