<?php

require_once 'config/database.php';
require_once 'includes/homepage-data.php';
include 'includes/header.php';
include 'includes/navbar.php';
require_once 'includes/product_image.php';
?>


<section class="hero">

<div class="container text-center">

<h1 class="display-2 fw-bold">

Nigeria's Digital Agricultural Marketplace

</h1>

<p class="lead">
Connecting verified farmers, trusted buyers and reliable truckers
on one secure marketplace.
</p>

<form method="GET" class="row g-3 justify-content-center mt-4">

    <div class="col-md-6">
        <input
            type="text"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            class="form-control form-control-lg"
            placeholder="Search products, categories or keywords...">
    </div>
    <div class="col-md-3">

    <select
        name="category"
        class="form-select form-select-lg">

        <option value="">
            All Categories
        </option>

        <?php foreach ($categories as $cat): ?>

<option
    value="<?= htmlspecialchars($cat['category']) ?>"
    <?= ($category === $cat['category']) ? 'selected' : '' ?>>

    <?= htmlspecialchars($cat['category']) ?>

</option>

<?php endforeach; ?>

    </select>

</div>

    <div class="col-auto">
        <button type="submit" class="btn btn-success btn-lg">
           Search Products
        </button>
    </div>

</form>

<div class="mt-4">

    <a href="/projects/farmlink/register.php"
class="btn btn-success btn-lg px-5">

Create Free Account

</a>

<a href="#products"
class="btn btn-warning btn-lg px-5">

Explore Marketplace

</a>
</div>

</div>

</section>
</div>
<section class="container py-5">

<h2 class="text-center mb-4">

Browse Categories

</h2>

<div class="row g-4">

<?php foreach ($categories as $cat):

    $category = $cat['category'];
?>

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

<p class="text-muted mb-0">
    <?= $cat['total'] ?> Products
</p>

            </div>

        </div>

    </div>

<?php endforeach; ?>

</div>

</section>

<div class="container mt-5">


<div class="row text-center g-3 mb-5">

    <div class="col-md-2">
        <div class="card shadow stat-card border-0">
            <div class="card-body">
                <h3 class="counter" data-target="<?= $farmerCount ?>">0</h3>
                <p>Farmers</p>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card shadow stat-card border-0">
            <div class="card-body">
                <h3 class="counter" data-target="<?= $buyerCount ?>">0</h3>
                <p>Buyers</p>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card shadow stat-card border-0">
            <div class="card-body">
                <h3 class="counter" data-target="<?= $truckerCount ?>">0</h3>
                <p>Truckers</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="counter" data-target="<?= $productCount ?>">0</h3>
                <p>Approved Products</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow stat-card border-0">
            <div class="card-body">
                <h3 class="counter" data-target="<?= $deliveryCount ?>">0</h3>
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

<div class="row g-4">

<?php foreach ($featuredProducts as $product): ?>

    <?php include 'includes/product_card.php'; ?>

<?php endforeach; ?>

</div>

<?php else: ?>

<div class="alert alert-info text-center">
    Featured products will appear here.
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

<h2 class="text-center mb-5">
What Our Clients Say
</h2>

<div class="row g-4">

<?php if (!empty($testimonials)): ?>

<div class="row g-4">

<?php foreach ($testimonials as $testimonial): ?>

<div class="col-md-4">

<div class="card h-100 shadow-sm">

<div class="card-body">

<div class="mb-2">

<?php
for ($i = 1; $i <= 5; $i++) {
    echo $i <= $testimonial['rating'] ? '⭐' : '☆';
}
?>

</div>

<p class="mb-3">

<?= nl2br(htmlspecialchars($testimonial['message'])) ?>

</p>

<h6 class="text-success mb-0">

<?= htmlspecialchars($testimonial['fullname']) ?>

</h6>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

<?php else: ?>

<div class="alert alert-info text-center">

No testimonials yet.

</div>

<?php endif; ?>
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

    <?php include 'includes/product_card.php'; ?>

<?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

<?php include 'includes/footer.php'; ?>

                    

