<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';
require_once '../includes/error_handler.php';

requireRole('farmer');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $product_name = trim($_POST['product_name']);
    $category     = trim($_POST['category']);
    $quantity     = (float)$_POST['quantity'];
    $unit         = trim($_POST['unit']);
    $price        = (float)$_POST['price'];
    $description  = trim($_POST['description']);

    $imageName = null;
/*
UPLOAD SECURITY
*/

if(

isset($_FILES['image'])

&&

$_FILES['image']['error']
!==4

){

if(

$_FILES['image']['error']
!==0

){

appFail(
'Upload failed.'
);

}


if(

$_FILES['image']['size']

>

5*1024*1024

){

appFail(
'Image must not exceed 5MB.'
);

}


$allowed=[

'image/jpeg',

'image/png',

'image/webp'

];


$type=

mime_content_type(

$_FILES['image']['tmp_name']

);


if(

!in_array(
$type,
$allowed
)

){

appFail(
'Only JPG, PNG and WEBP images are allowed.'
);

}


$uploadDir=
'../uploads/products/';


if(

!is_dir(
$uploadDir
)

){

mkdir(

$uploadDir,

0775,

true

);

}


$extension=

pathinfo(

$_FILES['image']['name'],

PATHINFO_EXTENSION

);


$imageName=

uniqid()

.

'_'

.

time()

.

'.'

.

$extension;


if(

!move_uploaded_file(

$_FILES['image']['tmp_name'],

$uploadDir.$imageName

)

){

appFail(
'Could not upload image.'
);

}

}


try{

$stmt=
$pdo->prepare(

"

INSERT INTO products
(

farmer_id,

product_name,

category,

quantity,

unit,

price,

description,

image

)

VALUES
(

?,

?,

?,

?,

?,

?,

?,

?

)

"

);


$stmt->execute([

$_SESSION['user_id'],

$product_name,

$category,

$quantity,

$unit,

$price,

$description,

$imageName

]);


$message=
'Product submitted successfully and awaiting approval.';


}catch(
PDOException $e
){

appError(
$e->getMessage()
);

appFail();

}

}


include '../includes/header.php';

include '../includes/navbar.php';

?>

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-8">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h3>

Add Product

</h3>

</div>

<div class="card-body">

<?php if($message): ?>

<div class="alert alert-success">

<?= htmlspecialchars($message) ?>

</div>

<?php endif; ?>

<form
method="POST"
enctype="multipart/form-data">
<?= csrfField(); ?>

<div class="mb-3">

<label>

Product Name

</label>

<input
type="text"
name="product_name"
class="form-control"
required>

</div>

<div class="mb-3">

<label>

Category

</label>

<input
type="text"
name="category"
class="form-control"
required>

</div>

<div class="mb-3">

<label>

Quantity

</label>

<input
type="number"
step="0.01"
name="quantity"
class="form-control"
required>

</div>

<div class="mb-3">

<label>

Unit

</label>

<select
name="unit"
class="form-control">

<option>

Kg

</option>

<option>

Ton

</option>

<option>

Bag

</option>

<option>

Crate

</option>

</select>

</div>

<div class="mb-3">

<label>

Price (₦)

</label>

<input
type="number"
step="0.01"
name="price"
class="form-control"
required>

</div>

<div class="mb-3">

<label>

Description

</label>

<textarea
name="description"
class="form-control"
rows="4">

</textarea>

</div>

<div class="mb-3">

<label>

Product Image

</label>

<input
type="file"
name="image"
accept=".jpg,.jpeg,.png,.webp"
class="form-control">

<small>

Maximum size:
5MB

</small>

</div>

<button
type="submit"
class="btn btn-success">

Save Product

</button>

</form>

</div>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>
