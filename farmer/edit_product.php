<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';
require_once '../includes/error_handler.php';

requireRole('farmer');

/*
|--------------------------------------------------------------------------
| Get Product ID
|--------------------------------------------------------------------------
*/

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    appFail('Invalid product.');

}

/*
|--------------------------------------------------------------------------
| Load Product
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT *
FROM products
WHERE id=?
AND farmer_id=?
LIMIT 1
");

$stmt->execute([
    $id,
    $_SESSION['user_id']
]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Product Not Found
|--------------------------------------------------------------------------
*/

if (!$product) {

    appFail('Product not found.');

}

/*
|--------------------------------------------------------------------------
| Load Categories
|--------------------------------------------------------------------------
*/

$categoryStmt = $pdo->query("
SELECT
    id,
    category_name
FROM product_categories
WHERE status='active'
ORDER BY category_name ASC
");

$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $product_name = trim($_POST['product_name']);
    $category     = trim($_POST['category']);
    $quantity     = (float)$_POST['quantity'];
    $unit         = trim($_POST['unit']);
    $price        = (float)$_POST['price'];
    $description  = trim($_POST['description']);

    /*
    |--------------------------------------------------------------------------
    | Keep Existing Image
    |--------------------------------------------------------------------------
    */

    $imageName = $product['image'];

    /*
    |--------------------------------------------------------------------------
    | Upload New Image
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== 4
    ) {

        if ($_FILES['image']['error'] !== 0) {

            appFail('Upload failed.');

        }

        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {

            appFail('Image must not exceed 5MB.');

        }

        $allowed = [

            'image/jpeg',
            'image/png',
            'image/webp'

        ];

        $type = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($type, $allowed)) {

            appFail('Only JPG, PNG and WEBP images are allowed.');

        }

        $uploadDir = '../uploads/products/';

        $extension = pathinfo(

            $_FILES['image']['name'],

            PATHINFO_EXTENSION

        );

        $imageName = uniqid() . '_' . time() . '.' . $extension;

        if (!move_uploaded_file(

            $_FILES['image']['tmp_name'],

            $uploadDir . $imageName

        )) {

            appFail('Could not upload image.');

        }

        /*
        |--------------------------------------------------------------------------
        | Delete Old Image
        |--------------------------------------------------------------------------
        */

        if (

            !empty($product['image']) &&

            file_exists($uploadDir . $product['image'])

        ) {

            unlink($uploadDir . $product['image']);

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Update Product
    |--------------------------------------------------------------------------
    */
if ($product['status'] === 'approved') {
    appFail('Approved products cannot be edited.');
}
    $stmt = $pdo->prepare("

    UPDATE products

    SET

        product_name=?,

        category=?,

        quantity=?,

        unit=?,

        price=?,

        description=?,

        image=?,

        status='pending',

        approved_by=NULL,

        rejection_reason=NULL

    WHERE id=?

    ");

    $stmt->execute([

        $product_name,

        $category,

        $quantity,

        $unit,

        $price,

        $description,

        $imageName,

        $id

    ]);

    header("Location: products.php?success=updated");

    exit;

}
include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-8">

<div class="card shadow">

<div class="card-header bg-warning text-dark">

<h3>Edit Product</h3>

</div>

<div class="card-body">

<form method="POST" enctype="multipart/form-data">

<?= csrfField(); ?>

<div class="mb-3">

<label class="form-label">Product Name</label>

<input
type="text"
name="product_name"
class="form-control"
value="<?= htmlspecialchars($product['product_name']) ?>"
required>

</div>

<div class="mb-3">

<label class="form-label">Category</label>

<select
name="category"
class="form-select"
required>

<?php foreach($categories as $cat): ?>

<option
value="<?= htmlspecialchars($cat['category_name']) ?>"
<?= $product['category']==$cat['category_name'] ? 'selected' : '' ?>>

<?= htmlspecialchars($cat['category_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="row">

<div class="col-md-6">

<label class="form-label">

Quantity

</label>

<input
type="number"
step="0.01"
min="1"
name="quantity"
class="form-control"
value="<?= htmlspecialchars($product['quantity']) ?>"
required>

</div>

<div class="col-md-6">

<label class="form-label">

Unit

</label>

<select
name="unit"
class="form-select"
required>
<?php

$units = [

'Kg',

'Ton',

'Bag',

'Basket',

'Bunch',

'Pieces',

'Litres'

];

foreach($units as $u):

?>

<option
value="<?= $u ?>"
<?= $product['unit']==$u ? 'selected' : '' ?>>

<?= $u ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>

<div class="mt-3">

<label class="form-label">

Price (₦)

</label>

<input
type="number"
step="0.01"
min="0"
name="price"
class="form-control"
value="<?= htmlspecialchars($product['price']) ?>"
required>

</div>

<div class="mt-3">

<label class="form-label">

Description

</label>

<textarea
name="description"
class="form-control"
rows="5"
required><?= htmlspecialchars($product['description']) ?></textarea>

</div>

<div class="mt-3">

<label class="form-label">

Current Image

</label>

<br>

<?php if(!empty($product['image'])): ?>

<img
src="../uploads/products/<?= htmlspecialchars($product['image']) ?>"
alt="<?= htmlspecialchars($product['product_name']) ?>"
class="img-thumbnail mb-3"
style="max-width:200px;">

<?php else: ?>

<p class="text-muted">

No image uploaded.

</p>

<?php endif; ?>

</div>

<div class="mt-3">

<label class="form-label">

Replace Image (Optional)

</label>

<input
type="file"
name="image"
class="form-control"
accept="image/jpeg,image/png,image/webp">
</div>

<div class="mt-4">

<button
type="submit"
class="btn btn-success">

Update Product

</button>

<a
href="products.php"
class="btn btn-secondary">

Cancel

</a>

</div>

</form>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>