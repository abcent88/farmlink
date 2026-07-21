<?php

require_once 'config/database.php';
require_once 'includes/validation.php';
require_once 'includes/csrf.php';

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();


$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$role = trim($_POST['role']);
$lga = trim($_POST['lga']);
$town = trim($_POST['town']);
$password = $_POST['password'];
if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

$truckType =
$_POST['truck_type'] ?? null;

$truckCapacity =
$_POST['truck_capacity'] ?? null;

$errors = validateRegistration(
$fullname,
$email,
$phone,
$password
);

/* Check duplicate email */

$check =
$pdo->prepare(
"SELECT id FROM users WHERE email=? OR phone=?"
);

$check->execute([
    $email,
    $phone
]);

if($row = $check->fetch()){

    $errors[] =
    "Email address or phone number already exists.";

}
if(empty($errors)){

try{

$hashedPassword =
password_hash(
$password,
PASSWORD_DEFAULT
);

$stmt =
$pdo->prepare(

"

INSERT INTO users
(
fullname,
email,
phone,
password,
role,
lga,
town,
truck_type,
truck_capacity,
status
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
?,
?,
'pending'
)

"

);

$stmt->execute([

$fullname,
$email,
$phone,
$hashedPassword,
$role,
$lga,
$town,

$role==='trucker'
? $truckType
: null,

$role==='trucker'
? $truckCapacity
: null

]);

$message =
"Registration successful. Awaiting approval.";

}catch(PDOException $e){

$message =
$e->getMessage();

}

}

}

include 'includes/header.php';
include 'includes/navbar.php';

?>

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h3>Create Account</h3>

</div>

<div class="card-body">

<?php if(!empty($errors)): ?>

<div class="alert alert-danger">

<?php foreach($errors as $error): ?>

<div>
<?= htmlspecialchars($error) ?>
</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

<?php if(!empty($message)): ?>

<div class="alert alert-info">

<?= $message ?>

</div>

<?php endif; ?>

<form method="POST">
    <?= csrfField(); ?>
<label>Full Name</label>

<input
type="text"
name="fullname"
class="form-control"
value="<?= htmlspecialchars($fullname ?? '') ?>"
required>

<br>

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required>

<br>

<label>Phone</label>

<input
type="text"
name="phone"
class="form-control"
required>

<br>

<label>Role</label>

<select
name="role"
class="form-control"
required>

<option value="">
Select Role
</option>

<option value="farmer">
Farmer
</option>

<option value="buyer">
Buyer
</option>

<option value="trucker">
Trucker
</option>

</select>

<br>

<label>Password</label>

<div class="input-group">

<input
type="password"
name="password"
id="password"
class="form-control"
required>

<button
class="btn btn-outline-secondary"
type="button"
id="togglePassword">

👁

</button>


</div>
<div class="progress mt-2">

<div
id="passwordStrengthBar"
class="progress-bar"
style="width:0%">

</div>

</div>

<small
id="passwordStrengthText"
class="form-text text-muted">

Password Strength

</small>

<br>
<label>Confirm Password</label>

<div class="input-group">

<input
type="password"
name="confirm_password"
id="confirm_password"
class="form-control"
required>

<button
class="btn btn-outline-secondary"
type="button"
id="toggleConfirmPassword">

👁

</button>

</div>

<small id="passwordMatch"></small>

<br>

<label>LGA</label>

<select
name="lga"
class="form-control"
required>

<option value="">
Select LGA
</option>

<option value="Makurdi">
Makurdi
</option>

<option value="Tarka">
Tarka
</option>

<option value="Gboko">
Gboko
</option>

<option value="Guma">
Guma
</option>

<option value="Buruku">
Buruku
</option>

</select>

<br>

<label>Town</label>

<input
type="text"
name="town"
class="form-control"
required>

<br>

<div
id="truckFields"
style="display:none;">

<label>
Truck Type
</label>

<select
id="truck_type"
name="truck_type"
class="form-control">

<option value="">
Select Truck
</option>

<option value="Mini Truck"
data-capacity="3">
Mini Truck
</option>

<option value="Pickup"
data-capacity="5">
Pickup
</option>

<option value="Flatbed"
data-capacity="10">
Flatbed
</option>

<option value="Medium Duty"
data-capacity="20">
Medium Duty
</option>

<option value="Heavy Duty"
data-capacity="30">
Heavy Duty
</option>

<option value="Trailer"
data-capacity="40">
Trailer
</option>

</select>

<br>

<label>
Truck Capacity (Tonnes)
</label>

<input
type="number"
step="0.01"
id="truck_capacity"
name="truck_capacity"
class="form-control"
readonly>

<br>

</div>

<button
type="submit"
class="btn btn-success w-100">

Register

</button>

</form>

</div>

</div>

</div>

</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Form Elements
    const role = document.querySelector('[name="role"]');
    const truckFields = document.getElementById('truckFields');
    const truckType = document.getElementById('truck_type');
    const capacity = document.getElementById('truck_capacity');

    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

    const passwordMatch = document.getElementById('passwordMatch');

    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');

    // ===========================
    // Trucker Fields
    // ===========================

    function toggleTruck() {

        if (role.value === 'trucker') {

            truckFields.style.display = 'block';

        } else {

            truckFields.style.display = 'none';
            truckType.value = '';
            capacity.value = '';

        }

    }

    function loadCapacity() {

        const selected = truckType.options[truckType.selectedIndex];

        capacity.value = selected.dataset.capacity || '';

    }

    toggleTruck();

    role.addEventListener('change', toggleTruck);

    truckType.addEventListener('change', loadCapacity);

    // ===========================
    // Show / Hide Password
    // ===========================

    togglePassword.addEventListener('click', function () {

        password.type =
            password.type === 'password'
                ? 'text'
                : 'password';

    });

    toggleConfirmPassword.addEventListener('click', function () {

        confirmPassword.type =
            confirmPassword.type === 'password'
                ? 'text'
                : 'password';

    });

    // ===========================
    // Password Match
    // ===========================

    function checkPasswordMatch() {

        if (confirmPassword.value === '') {

            passwordMatch.innerHTML = '';
            return;

        }

        if (password.value === confirmPassword.value) {

            passwordMatch.innerHTML = '✓ Passwords match';
            passwordMatch.style.color = 'green';

        } else {

            passwordMatch.innerHTML = '✗ Passwords do not match';
            passwordMatch.style.color = 'red';

        }

    }

    password.addEventListener('keyup', checkPasswordMatch);
    confirmPassword.addEventListener('keyup', checkPasswordMatch);

    // ===========================
    // Password Strength
    // ===========================

    function checkStrength() {

        const value = password.value;

        let score = 0;

        if (value.length >= 8) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[a-z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        switch (score) {

            case 0:
                strengthBar.style.width = '0%';
                strengthBar.className = 'progress-bar';
                strengthText.innerHTML = 'Password Strength';
                break;

            case 1:
                strengthBar.style.width = '20%';
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.innerHTML = 'Weak';
                break;

            case 2:
                strengthBar.style.width = '40%';
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.innerHTML = 'Fair';
                break;

            case 3:
                strengthBar.style.width = '60%';
                strengthBar.className = 'progress-bar bg-info';
                strengthText.innerHTML = 'Good';
                break;

            case 4:
                strengthBar.style.width = '80%';
                strengthBar.className = 'progress-bar bg-primary';
                strengthText.innerHTML = 'Strong';
                break;

            case 5:
                strengthBar.style.width = '100%';
                strengthBar.className = 'progress-bar bg-success';
                strengthText.innerHTML = 'Very Strong';
                break;

        }

    }

    password.addEventListener('keyup', checkStrength);

});
</script>

<?php include 'includes/footer.php'; ?>
