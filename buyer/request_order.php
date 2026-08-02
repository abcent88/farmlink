<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';
require_once '../includes/notify.php';
require_once '../includes/error_handler.php';

requireRole('buyer');

$productId = (int)($_GET['id'] ?? 0);

if ($productId <= 0) {
    appFail('Invalid product.');
}

$stmt = $pdo->prepare("
SELECT
    p.*,
    u.fullname,
    u.phone,
    u.lga,
    u.town,
    fp.farm_name
FROM products p
JOIN users u
    ON p.farmer_id = u.id
LEFT JOIN farmer_profiles fp
    ON fp.user_id = u.id
WHERE
    p.id = ?
    AND p.status = 'approved'
LIMIT 1
");

$stmt->execute([$productId]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    appFail('Product not found.');
}

if ($product['quantity'] <= 0) {
    appFail('This product is out of stock.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $quantity = (float)($_POST['quantity'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $totalAmount = $quantity * (float)$product['price'];

    if ($quantity <= 0) {
        appFail('Invalid quantity.');
    }

    if ($quantity > $product['quantity']) {
        appFail('Requested quantity exceeds available stock.');
    }

    try {

        $stmt = $pdo->prepare("
INSERT INTO orders
(
    buyer_id,
    farmer_id,
    product_id,
    quantity,
    price,
    total,
    notes,
    total_amount
)
VALUES
(
    ?, ?, ?, ?, ?, ?, ?, ?
)
");
$stmt->execute([

    $_SESSION['user_id'],      // buyer

    $product['farmer_id'],     // farmer

    $productId,

    $quantity,

    $product['price'],         // unit price

    $totalAmount,              // total

    $notes,

    $totalAmount               // total_amount

]);

        notify(
            $pdo,
            $product['farmer_id'],
            'New Order Request',
            'A buyer has requested your product "' .
            $product['product_name'] .
            '". Please review the order.'
        );

        header("Location: marketplace.php?success=request_sent");
        exit;

    } catch (Exception $e) {

    die($e->getMessage());

}

    }



include '../includes/header.php';
include '../includes/navbar.php';

$image = !empty($product['image'])
    ? '../uploads/products/' . $product['image']
    : '../assets/images/no-image.png';
?>

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-lg-8">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h3>Request Product</h3>

</div>

<div class="card-body">

<img
src="<?= htmlspecialchars($image) ?>"
class="img-fluid rounded mb-4"
style="max-height:250px;object-fit:cover;">

<h4><?= htmlspecialchars($product['product_name']) ?></h4>

<hr>

<p>
<strong>Farmer:</strong>
<?= htmlspecialchars($product['fullname']) ?>
</p>

<p>
<strong>Farm:</strong>
<?= htmlspecialchars($product['farm_name'] ?: 'Not Provided') ?>
</p>

<p>
<strong>LGA:</strong>
<?= htmlspecialchars($product['lga']) ?>
</p>

<p>
<strong>Available Stock:</strong>
<?= number_format($product['quantity']) ?>
<?= htmlspecialchars($product['unit']) ?>
</p>

<p>
<strong>Price:</strong>
₦<?= number_format($product['price'],2) ?>
</p>

<p>
<?= nl2br(htmlspecialchars($product['description'])) ?>
</p>

<hr>

<form method="POST">

<?= csrfField(); ?>

<div class="mb-3">

<label class="form-label">

Quantity Needed

</label>

<input
type="number"
name="quantity"
step="0.01"
min="1"
max="<?= $product['quantity'] ?>"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">

Order Notes (Optional)

</label>

<textarea
name="notes"
rows="4"
class="form-control"
placeholder="Any delivery instructions or special requests..."></textarea>

</div>

<button
type="submit"
class="btn btn-success">

Submit Request

</button>

<a
href="marketplace.php"
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