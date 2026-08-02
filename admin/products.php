<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';

requireRole(['super_admin', 'lga_admin']);

$role   = $_SESSION['role'];
$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get Logged-in Admin Information
|--------------------------------------------------------------------------
*/

$admin = [];

if ($role === 'lga_admin') {

    $stmt = $pdo->prepare("
        SELECT id, fullname, lga
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$search       = trim($_GET['search'] ?? '');
$category     = trim($_GET['category'] ?? '');
$status       = trim($_GET['status'] ?? '');
$verification = trim($_GET['verification'] ?? '');

$where  = [];
$params = [];

/*
|--------------------------------------------------------------------------
| Base Product Query
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    p.*,

    u.fullname,
    u.email,
    u.phone,
    u.lga,
    u.town,

    fp.farm_name,
    fp.farm_type,
    fp.verification_status,

    approver.fullname AS approved_by_name

FROM products p

INNER JOIN users u
    ON u.id = p.farmer_id

LEFT JOIN farmer_profiles fp
    ON fp.user_id = u.id

LEFT JOIN users approver
    ON approver.id = p.approved_by

";

/*
|--------------------------------------------------------------------------
| Restrict LGA Admin
|--------------------------------------------------------------------------
*/

if ($role === 'lga_admin') {

    $where[] = "u.lga = ?";
    $params[] = $admin['lga'];
}

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $where[] = "(
        p.product_name LIKE ?
        OR u.fullname LIKE ?
    )";

    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

/*
|--------------------------------------------------------------------------
| Category
|--------------------------------------------------------------------------
*/

if ($category !== '') {

    $where[] = "p.category = ?";
    $params[] = $category;
}

/*
|--------------------------------------------------------------------------
| Product Status
|--------------------------------------------------------------------------
*/

if ($status !== '') {

    $where[] = "p.status = ?";
    $params[] = $status;
}

/*
|--------------------------------------------------------------------------
| Verification Status
|--------------------------------------------------------------------------
*/

if ($verification !== '') {

    $where[] = "fp.verification_status = ?";
    $params[] = $verification;
}

/*
|--------------------------------------------------------------------------
| Execute Product Query
|--------------------------------------------------------------------------
*/

if (!empty($where)) {

    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$statsSql = "

SELECT

    COUNT(*) AS total_products,

    SUM(p.status='pending')  AS pending_products,

    SUM(p.status='approved') AS approved_products,

    SUM(p.status='rejected') AS rejected_products

FROM products p

INNER JOIN users u
    ON u.id = p.farmer_id

";

$statsParams = [];

if ($role === 'lga_admin') {

    $statsSql .= " WHERE u.lga = ?";
    $statsParams[] = $admin['lga'];
}

$statsStmt = $pdo->prepare($statsSql);
$statsStmt->execute($statsParams);

$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$totalProducts    = (int)($stats['total_products'] ?? 0);
$pendingProducts  = (int)($stats['pending_products'] ?? 0);
$approvedProducts = (int)($stats['approved_products'] ?? 0);
$rejectedProducts = (int)($stats['rejected_products'] ?? 0);

/*
|--------------------------------------------------------------------------
| Page Includes
|--------------------------------------------------------------------------
*/

include '../includes/header.php';
include '../includes/navbar.php';

?>
<!-- ==========================================================
     PRODUCT DASHBOARD
=========================================================== -->

<div class="container-fluid py-4">

    <!-- Statistics -->
    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">

                    <div class="display-5 text-primary mb-2">
                        <i class="bi bi-box-seam"></i>
                    </div>

                    <h2 class="fw-bold mb-1">
                        <?= number_format($totalProducts) ?>
                    </h2>

                    <small class="text-muted">
                        Total Products
                    </small>

                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">

                    <div class="display-5 text-warning mb-2">
                        <i class="bi bi-hourglass-split"></i>
                    </div>

                    <h2 class="fw-bold text-warning mb-1">
                        <?= number_format($pendingProducts) ?>
                    </h2>

                    <small class="text-muted">
                        Pending Approval
                    </small>

                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">

                    <div class="display-5 text-success mb-2">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <h2 class="fw-bold text-success mb-1">
                        <?= number_format($approvedProducts) ?>
                    </h2>

                    <small class="text-muted">
                        Approved
                    </small>

                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">

                    <div class="display-5 text-danger mb-2">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>

                    <h2 class="fw-bold text-danger mb-1">
                        <?= number_format($rejectedProducts) ?>
                    </h2>

                    <small class="text-muted">
                        Rejected
                    </small>

                </div>
            </div>
        </div>

    </div>

    <!-- Search Card -->
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-success text-white">

            <h5 class="mb-0">
                <i class="bi bi-search"></i>
                Search & Filter Products
            </h5>

        </div>

        <div class="card-body">

            <form method="GET">

                <div class="row g-3">

                    <div class="col-lg-3">

                        <label class="form-label fw-semibold">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Product or Farmer"
                            value="<?= htmlspecialchars($search) ?>">

                    </div>

                    <div class="col-lg-2">

                        <label class="form-label fw-semibold">
                            Category
                        </label>

                        <select
                            name="category"
                            class="form-select">

                            <option value="">All Categories</option>

                            <option value="Grains"
                                <?= $category=='Grains'?'selected':'' ?>>
                                Grains
                            </option>

                            <option value="Tubers"
                                <?= $category=='Tubers'?'selected':'' ?>>
                                Tubers
                            </option>

                            <option value="Vegetables"
                                <?= $category=='Vegetables'?'selected':'' ?>>
                                Vegetables
                            </option>

                            <option value="Fruits"
                                <?= $category=='Fruits'?'selected':'' ?>>
                                Fruits
                            </option>

                            <option value="Livestock"
                                <?= $category=='Livestock'?'selected':'' ?>>
                                Livestock
                            </option>

                        </select>

                    </div>

                    <div class="col-lg-2">

                        <label class="form-label fw-semibold">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select">

                            <option value="">All Status</option>

                            <option value="pending"
                                <?= $status=='pending'?'selected':'' ?>>
                                Pending
                            </option>

                            <option value="approved"
                                <?= $status=='approved'?'selected':'' ?>>
                                Approved
                            </option>

                            <option value="rejected"
                                <?= $status=='rejected'?'selected':'' ?>>
                                Rejected
                            </option>

                        </select>

                    </div>

                    <div class="col-lg-2">

                        <label class="form-label fw-semibold">
                            Verification
                        </label>

                        <select
                            name="verification"
                            class="form-select">

                            <option value="">All</option>

                            <option value="verified"
                                <?= $verification=='verified'?'selected':'' ?>>
                                Verified
                            </option>

                            <option value="pending"
                                <?= $verification=='pending'?'selected':'' ?>>
                                Pending
                            </option>

                            <option value="rejected"
                                <?= $verification=='rejected'?'selected':'' ?>>
                                Rejected
                            </option>

                        </select>

                    </div>

                    <div class="col-lg-3 d-flex align-items-end">

                        <button
                            class="btn btn-success w-100 me-2"
                            type="submit">

                            <i class="bi bi-search"></i>

                            Search

                        </button>

                        <a
                            href="products.php"
                            class="btn btn-outline-secondary w-100">

                            <i class="bi bi-arrow-clockwise"></i>

                            Reset

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- Product List -->
    <div class="card shadow-sm border-0">

        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">

            <h5 class="mb-0">

                <i class="bi bi-box"></i>

                Product List

            </h5>

            <span class="badge bg-light text-success fs-6">

                <?= count($products) ?>

                Product<?= count($products)!=1 ? 's' : '' ?>

            </span>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>ID</th>

                        <th>Image</th>

                        <th>Farmer</th>

                        <th>Location</th>

                        <th>Verification</th>

                        <th>Product</th>

                        <th>Quantity</th>

                        <th>Price</th>

                        <th>Status</th>

                        <th>Approved By</th>

                        <th width="130">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>
                    <?php if (empty($products)): ?>

<tr>

    <td colspan="11" class="text-center py-5">

        <div class="text-muted">

            <i class="bi bi-box display-4"></i>

            <h5 class="mt-3">

                No Products Found

            </h5>

            <p class="mb-0">

                Try adjusting your search filters.

            </p>

        </div>

    </td>

</tr>

<?php else: ?>

<?php foreach ($products as $product):

$image = !empty($product['image'])
    ? '../uploads/products/' . $product['image']
    : '../assets/images/no-image.png';

$statusBadge = match($product['status']) {
    'approved' => '<span class="badge bg-success">Approved</span>',
    'rejected' => '<span class="badge bg-danger">Rejected</span>',
    default => '<span class="badge bg-warning text-dark">Pending</span>'
};

$verificationBadge = match($product['verification_status']) {
    'verified' => '<span class="badge bg-success">Verified</span>',
    'rejected' => '<span class="badge bg-danger">Rejected</span>',
    default => '<span class="badge bg-warning text-dark">Pending</span>'
};

$approvedBy = !empty($product['approved_by_name'])
    ? htmlspecialchars($product['approved_by_name'])
    : '<span class="text-muted">Not Approved</span>';

?>

<tr>

    <td class="fw-semibold">

        <?= $product['id'] ?>

    </td>

    <td>

        <img
            src="<?= htmlspecialchars($image) ?>"
            alt="<?= htmlspecialchars($product['product_name']) ?>"
            class="rounded border shadow-sm"
            style="width:70px;height:70px;object-fit:cover;">

    </td>

    <td>

        <strong>

            <?= htmlspecialchars($product['fullname']) ?>

        </strong>

        <br>

        <small class="text-muted">

            <?= htmlspecialchars($product['email']) ?>

        </small>

    </td>

    <td>

        <?= htmlspecialchars($product['lga']) ?>

        <?php if (!empty($product['town'])): ?>

            <br>

            <small class="text-muted">

                <?= htmlspecialchars($product['town']) ?>

            </small>

        <?php endif; ?>

    </td>

    <td>

        <?= $verificationBadge ?>

    </td>

    <td>

        <strong>

            <?= htmlspecialchars($product['product_name']) ?>

        </strong>

        <br>

        <small class="text-muted">

            <?= htmlspecialchars($product['category']) ?>

        </small>

    </td>

    <td>

        <?= number_format($product['quantity']) ?>

        <?= htmlspecialchars($product['unit']) ?>

    </td>

    <td class="fw-bold text-success">

        ₦<?= number_format($product['price'], 2) ?>

    </td>

    <td>

        <?= $statusBadge ?>

    </td>

    <td>

        <?= $approvedBy ?>

    </td>

    <td>

        <button
            class="btn btn-outline-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#reviewModal<?= $product['id'] ?>">

            <i class="bi bi-eye"></i>

            Review

        </button>

    </td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>

</table>

</div>

</div>
<?php foreach ($products as $product):

$image = !empty($product['image'])
    ? '../uploads/products/' . $product['image']
    : '../assets/images/no-image.png';

$statusBadge = match($product['status']) {
    'approved' => '<span class="badge bg-success">Approved</span>',
    'rejected' => '<span class="badge bg-danger">Rejected</span>',
    default => '<span class="badge bg-warning text-dark">Pending</span>'
};

$verificationBadge = match($product['verification_status']) {
    'verified' => '<span class="badge bg-success">Verified</span>',
    'rejected' => '<span class="badge bg-danger">Rejected</span>',
    default => '<span class="badge bg-warning text-dark">Pending</span>'
};

?>

<div class="modal fade"
     id="reviewModal<?= $product['id'] ?>"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header bg-success text-white">

                <h5 class="modal-title">

                    <i class="bi bi-box-seam me-2"></i>

                    Review Product

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="row g-4">

                    <!-- Product Image -->
                    <div class="col-lg-4 text-center">

                        <img
                            src="<?= htmlspecialchars($image) ?>"
                            class="img-fluid rounded shadow border"
                            style="max-height:320px;object-fit:cover;"
                            alt="<?= htmlspecialchars($product['product_name']) ?>">

                    </div>

                    <!-- Product Details -->
                    <div class="col-lg-8">

                        <h3 class="fw-bold">

                            <?= htmlspecialchars($product['product_name']) ?>

                        </h3>

                        <p class="text-muted">

                            <?= htmlspecialchars($product['category']) ?>

                        </p>

                        <hr>

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <strong>Farmer</strong>

                                <br>

                                <?= htmlspecialchars($product['fullname']) ?>

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Farm Name</strong>

                                <br>

                                <?= htmlspecialchars($product['farm_name'] ?: 'Not Provided') ?>

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Email</strong>

                                <br>

                                <?= htmlspecialchars($product['email']) ?>

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Phone</strong>

                                <br>

                                <?= htmlspecialchars($product['phone']) ?>

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>LGA</strong>

                                <br>

                                <?= htmlspecialchars($product['lga']) ?>

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Town</strong>

                                <br>

                                <?= htmlspecialchars($product['town']) ?>

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Farm Type</strong>

                                <br>

                                <?= htmlspecialchars($product['farm_type'] ?: 'Not Provided') ?>

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Quantity</strong>

                                <br>

                                <?= number_format($product['quantity']) ?>

                                <?= htmlspecialchars($product['unit']) ?>

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Price</strong>

                                <br>

                                <span class="fw-bold text-success">

                                    ₦<?= number_format($product['price'],2) ?>

                                </span>

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Verification</strong>

                                <br>

                                <?= $verificationBadge ?>

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Status</strong>

                                <br>

                                <?= $statusBadge ?>

                            </div>

                            <div class="col-md-6 mb-3">

                                <strong>Approved By</strong>

                                <br>

                                <?= !empty($product['approved_by_name'])
                                    ? htmlspecialchars($product['approved_by_name'])
                                    : '<span class="text-muted">Not Approved Yet</span>' ?>

                            </div>

                        </div>

                        <hr>

                        <h5 class="fw-bold">

                            Product Description

                        </h5>

                        <div class="border rounded bg-light p-3">

                            <?= nl2br(htmlspecialchars($product['description'] ?: 'No description provided.')) ?>

                        </div>

                        <?php if (
                            $product['status'] === 'rejected'
                            && !empty($product['rejection_reason'])
                        ): ?>

                            <div class="alert alert-danger mt-4">

                                <h6 class="fw-bold">

                                    Reason for Rejection

                                </h6>

                                <hr>

                                <?= nl2br(htmlspecialchars($product['rejection_reason'])) ?>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <?php if (
    $product['status'] === 'pending' &&
    $product['verification_status'] === 'verified'
): ?>

    <form
        method="POST"
        action="approve_product.php"
        class="me-2">

        <?= csrfField(); ?>

        <input
            type="hidden"
            name="id"
            value="<?= $product['id'] ?>">

        <button
            type="submit"
            class="btn btn-success">

            <i class="bi bi-check-circle-fill"></i>

            Approve Product

        </button>

    </form>

    <form
        method="POST"
        action="reject_product.php"
        class="flex-grow-1">

        <?= csrfField(); ?>

        <input
            type="hidden"
            name="id"
            value="<?= $product['id'] ?>">

        <textarea
            name="reason"
            class="form-control mb-2"
            rows="3"
            required
            placeholder="Reason for rejection..."></textarea>

        <button
            type="submit"
            class="btn btn-danger">

            <i class="bi bi-x-circle-fill"></i>

            Reject Product

        </button>

    </form>

<?php elseif ($product['status'] === 'approved'): ?>

    <div class="alert alert-success w-100 mb-0">

        <strong>Approved.</strong>

        This product has already been approved.

    </div>

<?php elseif ($product['status'] === 'rejected'): ?>

    <div class="w-100">

        <div class="alert alert-danger">

            <strong>Rejected.</strong>

            This product has already been rejected.

        </div>

        <form
            method="POST"
            action="approve_product.php">

            <?= csrfField(); ?>

            <input
                type="hidden"
                name="id"
                value="<?= $product['id'] ?>">

            <button
                type="submit"
                class="btn btn-success">

                <i class="bi bi-arrow-repeat"></i>

                Approve Instead

            </button>

        </form>

    </div>

<?php else: ?>

    <div class="alert alert-warning w-100 mb-0">

        <strong>Farmer Not Verified.</strong>

        Verify the farmer before approving this product.

    </div>

<?php endif; ?>

            </div>

        </div>

    </div>

</div>

<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>