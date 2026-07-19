<?php

require_once 'config/database.php';
require_once 'includes/homepage-data.php';


include 'includes/header.php';
include 'includes/navbar.php';
?>


<section class="hero">

<div class="container text-center">

<h3 class="display-3 fw-bold">
Welcome to FarmLink
</h3>

<p class="lead">
Connecting Farmers, Buyers and Truckers Across Nigeria
</p>

<form method="GET" class="row g-3 justify-content-center mt-4">

    <div class="col-md-6">
        <input
            type="text"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            class="form-control form-control-lg"
            placeholder="Search products, categories or keywords...">
    </div><div class="col-md-3">

    <select
        name="category"
        class="form-select form-select-lg">

        <option value="">
            All Categories
        </option>

        <?php foreach ($categories as $cat): ?>

            <option
                value="<?= htmlspecialchars($cat) ?>"
                <?= ($category === $cat) ? 'selected' : '' ?>>

                <?= htmlspecialchars($cat) ?>

            </option>

        <?php endforeach; ?>

    </select>

</div>

    <div class="col-auto">
        <button type="submit" class="btn btn-success btn-lg">
           🔍 Search
        </button>
    </div>

</form>

<div class="mt-4">

    <a href="/projects/farmlink/register.php"
class="btn btn-success btn-lg px-5">

Join FarmLink

</a>

<a href="#products"
class="btn btn-warning btn-lg px-5">

Browse Products

</a>
</div>

</div>

</section>
<section class="container py-5">

<h2 class="text-center mb-4">

Browse Categories

</h2>

<div class="row g-4">

<?php foreach ($categories as $category): ?>

    <?php

    $image = "/projects/farmlink/assets/images/categories/default.jpg";

    switch (strtolower($category)) {

        case 'grains':
            $image = "/projects/farmlink/assets/images/categories/grains.jpg";
            break;

        case 'tubers':
            $image = "/projects/farmlink/assets/images/categories/tubers.jpg";
            break;

        case 'vegetables':
            $image = "/projects/farmlink/assets/images/categories/vegetables.jpg";
            break;

        case 'fruits':
            $image = "/projects/farmlink/assets/images/categories/fruits.jpg";
            break;

        case 'maize':
            $image = "/projects/farmlink/assets/images/categories/maize.jpg";
            break;

        case 'cassava':
            $image = "/projects/farmlink/assets/images/categories/cassava.jpg";
            break;

        case 'rice':
            $image = "/projects/farmlink/assets/images/categories/rice.jpg";
            break;

        case 'yam':
            $image = "/projects/farmlink/assets/images/categories/yam.jpg";
            break;
    }

    ?>

    <div class="col-lg-3 col-md-4 col-sm-6">

        <div class="card category-card shadow h-100">

            <img
                src="<?= $image ?>"
                class="card-img-top"
                style="height:180px;object-fit:cover;"
                alt="<?= htmlspecialchars($category) ?>">

            <div class="card-body text-center">

                <h5 class="fw-bold">
                    <?= htmlspecialchars($category) ?>
                </h5>

            </div>

        </div>

    </div>

<?php endforeach; ?>

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
</div>
<section class="container py-5">

    <div class="text-center mb-5">

        <h2 class="fw-bold">
            ⭐ Featured Products
        </h2>

        <p class="text-muted">
            Discover some of the best agricultural products available on FarmLink.
        </p>

    </div>

    <?php if (!empty($featuredProducts)): ?>

    <div class="row">

        <?php foreach ($featuredProducts as $product): ?>

        <div class="col-lg-3 col-md-6 mb-4">

            <div class="card featured-product-card h-100 shadow">

                <?php if (!empty($product['image'])): ?>

                    <img
                        src="/projects/farmlink/uploads/products/<?= htmlspecialchars($product['image']) ?>"
                        class="card-img-top"
                        style="height:220px;object-fit:cover;"
                        alt="<?= htmlspecialchars($product['product_name']) ?>">

                <?php endif; ?>

                <div class="card-body d-flex flex-column">

                    <span class="badge bg-warning text-dark mb-2">
                        Featured
                    </span>

                    <h5 class="fw-bold">
                        <?= htmlspecialchars($product['product_name']) ?>
                    </h5>

                    <p class="mb-1">
                        👨‍🌾 <?= htmlspecialchars($product['farmer_name']) ?>
                    </p>

                    <p class="mb-3">
                        🌾 <?= htmlspecialchars($product['category']) ?>
                    </p>

                    <h4 class="text-success mb-4">
                        ₦<?= number_format($product['price'],2) ?>
                    </h4>

                    <div class="mt-auto">

                        <a href="/projects/farmlink/farmer/profile.php?id=<?= $product['farmer_id'] ?>"
                           class="btn btn-success w-100">
                            View Product
                        </a>

                    </div>

                </div>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <?php endif; ?>

</section>
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
<section class="container py-5">

    <div class="text-center mb-5">
        <h2 class="fw-bold">What Our Buyers Say</h2>
        <p class="text-muted">
            Hear from customers who buy agricultural products through FarmLink.
        </p>
    </div>

    <div class="row">

        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-body">
                    <div class="mb-3 text-warning fs-5">
                        ★★★★★
                    </div>

                    <p class="fst-italic">
                        "FarmLink helped me source quality maize directly from trusted farmers at a fair price."
                    </p>

                    <hr>

                    <h6 class="mb-0 fw-bold">
                        James Adebayo
                    </h6>

                    <small class="text-muted">
                        Food Processor • Lagos
                    </small>

                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-body">
                    <div class="mb-3 text-warning fs-5">
                        ★★★★★
                    </div>

                    <p class="fst-italic">
                        "Ordering vegetables has become much easier. Delivery was fast and reliable."
                    </p>

                    <hr>

                    <h6 class="mb-0 fw-bold">
                        Sarah Okon
                    </h6>

                    <small class="text-muted">
                        Restaurant Owner • Uyo
                    </small>

                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
           <div class="card testimonial-card shadow h-100">
                <div class="card-body">
                    <div class="mb-3 text-warning fs-5">
                        ★★★★★
                    </div>

                    <p class="fst-italic">
                        "The platform connects us directly with verified farmers. It has improved our purchasing process."
                    </p>

                    <hr>

                    <h6 class="mb-0 fw-bold">
                        Ibrahim Musa
                    </h6>

                    <small class="text-muted">
                        Grain Merchant • Kano
                    </small>

                </div>
            </div>
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

<section id="products" class="container py-5">
    <h2 class="text-center mb-5">
        Available Products
    </h2>

    <?php if (empty($products)): ?>

        <div class="alert alert-warning text-center">
            No products found.
        </div>

    <?php else: ?>

        <div class="row">

            <?php foreach ($products as $product): ?>

                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">

                    <div class="card product-card shadow h-100">
                        <div class="position-absolute top-0 end-0 m-2">
    <span class="badge bg-danger">
        NEW
    </span>
</div>

                        <?php if (!empty($product['image'])): ?>

                            <img
                                src="/projects/farmlink/uploads/products/<?= htmlspecialchars($product['image']) ?>"
                                class="card-img-top"
                                style="height:220px;object-fit:cover;"
                                alt="<?= htmlspecialchars($product['product_name']) ?>">

                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">

    <h5 class="card-title">
        <?= htmlspecialchars($product['product_name']) ?>
    </h5>

    <p class="mb-2">
        <strong>Farmer:</strong>
        <?= htmlspecialchars($product['farmer_name']) ?>
    </p>

    <p class="mb-2">
        <?= htmlspecialchars($product['category']) ?>
    </p>

    <p class="mb-2">
        <?= htmlspecialchars($product['quantity']) ?>
        <?= htmlspecialchars($product['unit']) ?>
    </p>

    <h4 class="text-success fw-bold mb-4">
        ₦<?= number_format($product['price'],2) ?>
    </h4>

    <div class="mt-auto d-grid gap-2">

    <a href="/projects/farmlink/login.php"
       class="btn btn-success">
        View Details
    </a>

    <a href="/projects/farmlink/login.php"
       class="btn btn-outline-success">
        Contact Farmer
    </a>

</div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

<?php include 'includes/footer.php'; ?>

                    

