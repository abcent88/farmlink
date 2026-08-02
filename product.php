<?php

require_once 'config/database.php';
require_once 'includes/product_image.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
SELECT
    p.*,
    u.fullname,
    u.phone,
    u.email,
    u.lga,
    u.town,
    fp.farm_name,
    fp.farm_type,
    fp.farm_size,
    fp.farm_size_unit,
    fp.about,
    fp.verification_status,
    fp.profile_photo
FROM products p

INNER JOIN users u
ON u.id=p.farmer_id

LEFT JOIN farmer_profiles fp
ON fp.user_id=u.id

WHERE
    p.id=?
AND
    p.status='approved'

LIMIT 1
");

$stmt->execute([$id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {

    include 'includes/header.php';
    include 'includes/navbar.php';

    echo '
    <div class="container py-5">

        <div class="alert alert-warning">

            Product not found.

        </div>

    </div>';

    include 'includes/footer.php';

    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';

?>
<div class="container py-5">

<div class="row">

<div class="col-lg-6">

<img
src="<?= productImage($product['image']) ?>"
alt="<?= htmlspecialchars($product['product_name']) ?>"
class="img-fluid rounded shadow"
style="width:100%;height:500px;object-fit:cover;">

</div>

<div class="col-lg-6">

<h1 class="fw-bold mb-2">
    <?= htmlspecialchars($product['product_name']) ?>
</h1>

<p class="text-muted">
    Sold by
    <strong><?= htmlspecialchars($product['fullname']) ?></strong>
</p>

<div class="mb-3">

<?php if($product['verification_status']=='verified'): ?>

<span class="badge bg-success">

Verified Farmer

</span>

<?php endif; ?>

<span class="badge bg-warning text-dark">

<?= htmlspecialchars($product['category']) ?>

</span>

</div>

<h2 class="text-success">

₦<?= number_format($product['price'],2) ?>

</h2>

<hr>

<p>

<strong>Available Quantity</strong>

<br>

<?= htmlspecialchars($product['quantity']) ?>

<?= htmlspecialchars($product['unit']) ?>

</p>

<p>

<?= nl2br(htmlspecialchars($product['description'] ?: 'No product description available.')) ?>

</p>
<div class="d-grid gap-3 mt-4">

<a

href="login.php"

class="btn btn-success btn-lg">

Contact Farmer

</a>

<a

href="login.php"

class="btn btn-warning btn-lg">

Buy Product

</a>

<a

href="javascript:window.print();"

class="btn btn-outline-secondary">

Print Product

</a>


</div>
<hr class="my-4">
</div>

</div>
<div class="row mt-5">

    <!-- Farmer Information -->

    <div class="col-lg-5 mb-4">

        <div class="card shadow h-100">

            <div class="card-header bg-success text-white">

                <h4 class="mb-0">
                    👨‍🌾 Farmer Information
                </h4>

            </div>

            <div class="card-body text-center">

                <?php if (!empty($product['profile_photo'])): ?>

                    <img
                        src="/projects/farmlink/uploads/profiles/<?= htmlspecialchars($product['profile_photo']) ?>"
                        class="rounded-circle shadow mb-3"
                        style="width:140px;height:140px;object-fit:cover;"
                        alt="<?= htmlspecialchars($product['fullname']) ?>">

                <?php else: ?>

                    <div
                        class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                        style="width:140px;height:140px;font-size:55px;font-weight:bold;">

                        <?= strtoupper(substr($product['fullname'],0,1)); ?>

                    </div>

                <?php endif; ?>

                <h4>

                    <?= htmlspecialchars($product['fullname']) ?>

                </h4>

                <p class="text-muted">

                    <?= htmlspecialchars($product['lga']) ?>

                    <?php if (!empty($product['town'])): ?>

                        ,

                        <?= htmlspecialchars($product['town']) ?>

                    <?php endif; ?>
                    <?php if (!empty($product['phone'])): ?>

<p class="mb-2">

📞

<?= htmlspecialchars($product['phone']) ?>

</p>

<?php endif; ?>
                    <?php if (!empty($product['email'])): ?>

<p class="small text-muted">

<?= htmlspecialchars($product['email']) ?>

</p>

<?php endif; ?>

                </p>

                <?php if ($product['verification_status'] == 'verified'): ?>

                    <span class="badge bg-success">

                       ✅ Verified Farmer

                    </span>

                <?php else: ?>

                    <span class="badge bg-warning text-dark">

                        ⏳ Pending Verification

                    </span>

                <?php endif; ?>

            </div>

        </div>

    </div>

    <!-- Farm Details -->

    <div class="col-lg-7">

        <div class="card shadow h-100">

            <div class="card-header bg-success text-white">

                <h4 class="mb-0">
                    🌾 Farm Details
                </h4>

            </div>

            <div class="card-body">

                <table class="table table-bordered align-middle">

                    <tr>

                        <th width="35%">Farm Name</th>

                        <td>

                            <?= htmlspecialchars($product['farm_name'] ?: 'Not Provided') ?>

                        </td>

                    </tr>

                    <tr>

                        <th>Farm Type</th>

                        <td>

                            <?= htmlspecialchars($product['farm_type'] ?: 'Not Provided') ?>

                        </td>

                    </tr>

                    <tr>

                        <th>Farm Size</th>

                        <td>

                            <?=
                                !empty($product['farm_size'])
                                ? htmlspecialchars($product['farm_size']) . ' ' . htmlspecialchars($product['farm_size_unit'])
                                : 'Not Provided';
                            ?>

                        </td>

                    </tr>

                    <tr>

                        <th>Location</th>

                        <td>

                            <?= htmlspecialchars($product['lga']) ?>

                            <?php if (!empty($product['town'])): ?>

                                ,

                                <?= htmlspecialchars($product['town']) ?>

                            <?php endif; ?>

                        </td>

                    </tr>

                </table>

                <h5 class="mt-4">

                    About this Farm

                </h5>

                <p class="text-muted">

                    <?= nl2br(htmlspecialchars($product['about'] ?: 'No farm description has been provided yet.')) ?>

                </p>

            </div>

        </div>

    </div>

</div>
</div>
<?php

/*
|--------------------------------------------------------------------------
| Similar Products
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
    p.id,
    p.product_name,
    p.price,
    p.image,
    p.category,
    u.fullname AS farmer_name
FROM products p
INNER JOIN users u
    ON u.id = p.farmer_id
WHERE
    p.status='approved'
AND
    p.category=?
AND
    p.id<>?
ORDER BY RAND()
LIMIT 4
");

$stmt->execute([
    $product['category'],
    $product['id']
]);

$similarProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<hr class="my-5">

<div class="row">

    <div class="col-lg-8">

        <h3 class="mb-4">

            🌾 Similar Products

        </h3>

    </div>

    <div class="col-lg-4 text-lg-end">

        <button
            class="btn btn-outline-success"
            onclick="navigator.share ?
                navigator.share({
                    title:'<?= htmlspecialchars($product['product_name'], ENT_QUOTES) ?>',
                    url:window.location.href
                }) :
                alert('Copy this link:\\n'+window.location.href);">

            📤 Share Product

        </button>

    </div>

</div>

<?php if (!empty($similarProducts)): ?>

<div class="row">

<?php foreach ($similarProducts as $item): ?>

<div class="col-lg-3 col-md-6 mb-4">

<div class="card shadow h-100">

<img
src="<?= productImage($item['image']) ?>"
class="card-img-top"
style="height:180px;object-fit:cover;"
alt="<?= htmlspecialchars($item['product_name']) ?>">

<div class="card-body d-flex flex-column">

<h6>

<?= htmlspecialchars($item['product_name']) ?>

</h6>

<p class="small text-muted mb-1">

<?= htmlspecialchars($item['farmer_name']) ?>

</p>

<p class="text-success fw-bold">

₦<?= number_format($item['price'],2) ?>

</p>

<a
href="product.php?id=<?= $item['id'] ?>"
class="btn btn-success mt-auto">

View Product

</a>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

<hr class="my-5">

<div class="card shadow">

<div class="card-header bg-light">

<h4 class="mb-0">

⭐ Buyer Reviews

</h4>

</div>

<div class="card-body text-center">

<h2 class="text-warning">

★★★★★

</h2>

<p>

Buyer reviews will appear here after purchases are completed.

</p>

<p class="text-muted">

FarmLink will display only verified buyer reviews.

</p>

</div>

</div>

<hr class="my-5">

<div class="bg-success text-white rounded p-5 text-center">

<h2>

Interested in this product?

</h2>

<p>

Create a FarmLink account to contact the farmer,
place orders,
track deliveries
and receive notifications.

</p>

<div class="mt-4">

<a
href="register.php"
class="btn btn-warning btn-lg">

Create Free Account

</a>

<a
href="login.php"
class="btn btn-light btn-lg">

Login

</a>

</div>

</div>

<?php include 'includes/footer.php'; ?>