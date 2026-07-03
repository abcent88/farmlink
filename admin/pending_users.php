<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');


/*
Bulk Approve
*/

if(
$_SERVER['REQUEST_METHOD']==='POST'
){

if(
isset(
$_POST['approve_selected']
)
&&
!empty(
$_POST['users']
)
){

$ids=
$_POST['users'];

$placeholders=
implode(
',',
array_fill(
0,
count($ids),
'?'
)
);

$stmt=
$pdo->prepare("

UPDATE users

SET status='active'

WHERE id IN
(
$placeholders
)

");

$stmt->execute(
$ids
);

}


if(
isset(
$_POST['approve_all']
)
){

$pdo->exec("

UPDATE users

SET status='active'

WHERE status='pending'

AND role!='super_admin'

");

}

}



/*
Load Queue
*/

$stmt=
$pdo->query("

SELECT

id,
fullname,
email,
role,
created_at

FROM users

WHERE status='pending'

ORDER BY id DESC

");

$users=
$stmt->fetchAll();


include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<h2>

Pending Registrations

</h2>


<div class="mb-3">

<form method="POST">

<button
name="approve_all"
class="btn btn-success">

Approve All

</button>

</form>

</div>


<form method="POST">

<div class="card shadow">

<div class="card-body">

<table class="table">

<tr>

<th>

<input
type="checkbox"
onclick="toggle(this)">

</th>

<th>Name</th>

<th>Email</th>

<th>Role</th>

<th>Date</th>

</tr>

<?php foreach($users as $u): ?>

<tr>

<td>

<input
type="checkbox"

name="users[]"

value="<?= $u['id'] ?>">

</td>

<td>

<?= htmlspecialchars(
$u['fullname']
) ?>

</td>

<td>

<?= htmlspecialchars(
$u['email']
) ?>

</td>

<td>

<?= strtoupper(
$u['role']
) ?>

</td>

<td>

<?= $u['created_at'] ?>

</td>

</tr>

<?php endforeach; ?>

</table>


<button

name="approve_selected"

class="btn btn-primary">

Approve Selected

</button>

</div>

</div>

</form>

</div>


<script>

function toggle(
source
){

boxes=
document.getElementsByName(
'users[]'
);

for(
i=0;
i<boxes.length;
i++
){

boxes[i].checked=
source.checked;

}

}

</script>

<?php include '../includes/footer.php'; ?>