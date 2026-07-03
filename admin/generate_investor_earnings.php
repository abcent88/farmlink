<?php

require_once '../config/database.php';
require_once '../includes/notify.php';
require_once '../includes/logger.php';

/*
Quarterly ROI
Example:
10% annual ROI
→ 2.5% per quarter
*/

$stmt=
$pdo->query("

SELECT setting_value

FROM settings

WHERE setting_key='annual_roi'

LIMIT 1

");

$annualROI=
floatval(
$stmt->fetchColumn()
);

$quarterlyROI =
$annualROI / 4;


/*
Current Quarter Check
*/

$currentQuarter =
date('Y') .
'-Q' .
ceil(
date('n') / 3
);


/*
Load Investors
*/

$stmt =
$pdo->query("

SELECT

id,
ownership_percent,
invested_amount

FROM investors

WHERE status='active'

");

$investors =
$stmt->fetchAll();

$count=0;

foreach(
$investors
as $i
){

/*
Prevent duplicate quarter
*/

$check =
$pdo->prepare("

SELECT id

FROM investor_earnings

WHERE investor_id=?
AND YEAR(earning_date)=YEAR(CURDATE())
AND QUARTER(earning_date)=QUARTER(CURDATE())

LIMIT 1

");

$check->execute([

$i['id']

]);

if(
$check->fetch()
){

continue;

}


/*
ROI Formula
*/

$capital =
$i['invested_amount'];

$payout =

(
$capital
*
$quarterlyROI
)
/
100;


/*
Save
*/

$save =
$pdo->prepare("

INSERT INTO investor_earnings
(

investor_id,

earning_date,

revenue,

share_percent,

payout

)

VALUES

(

?,

CURDATE(),

?,

?,

?

)

");

$save->execute([

$i['id'],

$capital,

$quarterlyROI,

$payout

]);

$count++;


/*
Get User
*/

$get=
$pdo->prepare("

SELECT user_id

FROM investors

WHERE id=?

");

$get->execute([

$i['id']

]);

$user=
$get->fetchColumn();


sendNotification(

$pdo,

$user,

'Quarterly ROI',

'ROI of ₦'.
number_format(
$payout,
2
).
' credited to your account.'

);
logActivity(

$pdo,

$user,

'ROI Generated',

'ROI ₦'.$payout

);
}

?>

<!DOCTYPE html>

<html>

<head>

<title>

Generate ROI

</title>

<link
href="../assets/vendor/bootstrap/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="alert alert-success">

ROI Generated

<br><br>

Processed:

<strong>

<?= $count ?>

</strong>

investor(s)

</div>

<a
href="dashboard.php"
class="btn btn-dark">

Back

</a>

</div>

</body>

</html>