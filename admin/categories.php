<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('super_admin');

$stmt = $pdo->query("

SELECT

pc.*,

COUNT(p.id) AS total_products

FROM product_categories pc

LEFT JOIN products p
ON p.category = pc.category_name

GROUP BY pc.id

ORDER BY pc.category_name ASC

");

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';

?>
<?php if(isset($_GET['success'])): ?>

<div class="container mt-3">

<div class="alert alert-success alert-dismissible fade show">

<?php

switch($_GET['success']){

case 'created':

echo "Category created successfully.";

break;

case 'updated':

echo "Category updated successfully.";

break;

case 'deleted':

echo "Category deleted successfully.";

break;

}

?>
<?php if(isset($_GET['error'])): ?>

<div class="alert alert-danger alert-dismissible fade show">

<?php

switch($_GET['error']){

case 'inuse':

echo "This category cannot be deleted because products are already using it.";

break;

}

?>

<button
class="btn-close"
data-bs-dismiss="alert"></button>

</div>

<?php endif; ?>

<button
class="btn-close"
data-bs-dismiss="alert"></button>

</div>

</div>

<?php endif; ?>

<div class="container mt-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>

Product Categories

</h2>

<a
href="create_category.php"
class="btn btn-success">

<i class="bi bi-plus-circle"></i>

New Category

</a>

</div>

<table class="table table-bordered table-hover">

<thead class="table-success">

<tr>

<th>ID</th>

<th>Category</th>

<th>Description</th>

<th>Products</th>

<th>Status</th>

<th width="180">

Action

</th>

</tr>

</thead>

<tbody>

<?php foreach($categories as $category): ?>

<tr>

<td>

<?= $category['id'] ?>

</td>

<td>

<strong>

<?= htmlspecialchars($category['category_name']) ?>

</strong>

</td>

<td>

<?= htmlspecialchars($category['description']) ?>

</td>

<td>

<span class="badge bg-primary">

<?= $category['total_products'] ?>

</span>

</td>

<td>

<?php if($category['status']=='active'): ?>

<span class="badge bg-success">

Active

</span>

<?php else: ?>

<span class="badge bg-danger">

Inactive

</span>

<?php endif; ?>

</td>

<td>

<a

href="edit_category.php?id=<?= $category['id'] ?>"

class="btn btn-primary btn-sm">

Edit

</a>

<a

href="delete_category.php?id=<?= $category['id'] ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Delete this category?')">

Delete

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php include '../includes/footer.php'; ?>