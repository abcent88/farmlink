<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';
require_once '../includes/csrf.php';

requireRole('farmer');

$userId = $_SESSION['user_id'];

$message = '';

/*
|--------------------------------------------------------------------------
| Ensure profile exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT id
FROM farmer_profiles
WHERE user_id=?
LIMIT 1
");

$stmt->execute([$userId]);

if(!$stmt->fetch()){

    $stmt=$pdo->prepare("
    INSERT INTO farmer_profiles(user_id)
    VALUES(?)
    ");

    $stmt->execute([$userId]);
}

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

if($_SERVER['REQUEST_METHOD']==='POST'){

    verify_csrf();

    $farm_name=trim($_POST['farm_name']);
    $farm_type=trim($_POST['farm_type']);
    $farm_size=$_POST['farm_size'];
    $farm_size_unit=$_POST['farm_size_unit'];
    $years_experience=$_POST['years_experience'];
    $farm_address=trim($_POST['farm_address']);
    $about=trim($_POST['about']);

    $stmt=$pdo->prepare("

    UPDATE farmer_profiles

    SET

    farm_name=?,

    farm_type=?,

    farm_size=?,

    farm_size_unit=?,

    years_experience=?,

    farm_address=?,

    about=?

    WHERE user_id=?

    ");

    $stmt->execute([

        $farm_name,
        $farm_type,
        $farm_size,
        $farm_size_unit,
        $years_experience,
        $farm_address,
        $about,
        $userId

    ]);

    $message="Profile updated successfully.";

}

/*
|--------------------------------------------------------------------------
| Load profile
|--------------------------------------------------------------------------
*/

$stmt=$pdo->prepare("

SELECT *

FROM farmer_profiles

WHERE user_id=?

");

$stmt->execute([$userId]);

$profile=$stmt->fetch();

include '../includes/header.php';
include '../includes/navbar.php';

?>
<div class="container py-4">

<div class="row justify-content-center">

<div class="col-lg-8">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h4 class="mb-0">

Edit Farmer Profile

</h4>

</div>

<div class="card-body">

<?php if($message): ?>

<div class="alert alert-success">

<?= htmlspecialchars($message) ?>

</div>

<?php endif; ?>

<form method="POST">

<?= csrfField(); ?>

<div class="mb-3">

<label class="form-label">

Farm Name

</label>

<input
type="text"
name="farm_name"
class="form-control"
value="<?= htmlspecialchars($profile['farm_name']) ?>">

</div>

<div class="mb-3">

<label class="form-label">

Farm Type

</label>

<input
type="text"
name="farm_type"
class="form-control"
value="<?= htmlspecialchars($profile['farm_type']) ?>">

</div>

<div class="row">

<div class="col-md-6">

<label class="form-label">

Farm Size

</label>

<input
type="number"
step="0.01"
name="farm_size"
class="form-control"
value="<?= htmlspecialchars($profile['farm_size']) ?>">

</div>

<div class="col-md-6">

<label class="form-label">

Unit

</label>

<select
name="farm_size_unit"
class="form-select">

<option
value="hectares"
<?= $profile['farm_size_unit']=='hectares'?'selected':'' ?>>

Hectares

</option>

<option
value="acres"
<?= $profile['farm_size_unit']=='acres'?'selected':'' ?>>

Acres

</option>

</select>

</div>

</div>

<br>

<div class="mb-3">

<label>

Years of Experience

</label>

<input
type="number"
name="years_experience"
class="form-control"
value="<?= htmlspecialchars($profile['years_experience']) ?>">

</div>

<div class="mb-3">

<label>

Farm Address

</label>

<textarea
name="farm_address"
class="form-control"
rows="3"><?= htmlspecialchars($profile['farm_address']) ?></textarea>

</div>

<div class="mb-3">

<label>

About Farm

</label>

<textarea
name="about"
class="form-control"
rows="5"><?= htmlspecialchars($profile['about']) ?></textarea>

</div>

<button
class="btn btn-success">

Save Profile

</button>

<a
href="profile.php"
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