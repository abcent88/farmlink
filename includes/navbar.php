<?php
if(session_status()===PHP_SESSION_NONE){
   
}
?>


<style>

:root{
--nav-bg:#198754;
--nav-card:#ffffff;
--nav-text:#111;
--shadow:0 6px 24px rgba(0,0,0,.15);
}

[data-theme="dark"]{
--nav-bg:#121212;
--nav-card:#1f1f1f;
--nav-text:#fff;
}

.app-navbar{
background:var(--nav-bg);
padding:12px;
position:sticky;
top:0;
z-index:999;
}

.brand{
font-size:28px;
font-weight:700;
color:white;
text-decoration:none;
}

.theme-btn{
border:none;
background:none;
font-size:24px;
color:white;
}

.mobile-menu{
display:grid;
grid-template-columns:
repeat(auto-fit,minmax(90px,1fr));
gap:12px;
margin-top:18px;
}

.nav-card{

background:none;

color:white;

padding:6px;

box-shadow:none;

font-size:11px;

width:72px;

white-space:normal;

word-break:break-word;

}

.nav-card:hover{
transform:translateY(-4px);
}

.nav-card span{
display:block;
font-size:32px;
margin-bottom:8px;
}

@media(max-width:768px){

.mobile-menu{
position:fixed;
left:12px;
right:12px;
bottom:12px;
background:var(--nav-bg);
padding:12px;
border-radius:28px;
display:flex;
justify-content:space-around;
box-shadow:0 10px 30px rgba(0,0,0,.25);
z-index:9999;
}

.nav-card{

background:var(--nav-card);

color:var(--nav-text);

text-decoration:none;

padding:14px;

border-radius:18px;

text-align:center;

box-shadow:var(--shadow);

transition:.25s;

display:flex;

flex-direction:column;

align-items:center;

justify-content:center;

min-height:110px;

overflow:hidden;

word-break:break-word;

font-size:14px;

line-height:1.3;

}

.nav-card span{

display:block;

font-size:28px;

margin-bottom:8px;

line-height:1;

}

body{
padding-bottom:120px;
}

}

</style>

<nav class="app-navbar">

<div class="container">

<div class="d-flex justify-content-between align-items-center">

<a class="brand"
href="/projects/farmlink/index.php">

🌱 FarmLink

</a>

<button
class="theme-btn"
onclick="toggleTheme()">

🌓

</button>

</div>

<div class="mobile-menu">

<?php if(isset($_SESSION['user_id'])): ?>

<?php if($_SESSION['role']=="super_admin"): ?>

<a class="nav-card"
href="/projects/farmlink/admin/dashboard.php"> <span>📊</span>
Dashboard </a>

<a class="nav-card"
href="/projects/farmlink/admin/users.php"> <span>👥</span>
Users </a>

<a class="nav-card"
href="/projects/farmlink/admin/products.php"> <span>🌾</span>
Products </a>

<a class="nav-card"
href="/projects/farmlink/admin/orders.php"> <span>📦</span>
Orders </a>

<a class="nav-card"
href="/projects/farmlink/admin/revenue.php"> <span>💰</span>
Revenue </a>
<a
class="nav-card"
href="/projects/farmlink/admin/investors.php">

<span>🏦</span>

Investors

</a>
<a
class="nav-card"
href="/projects/farmlink/admin/withdrawals.php">

<span>💰</span>

Investor Withdrawals

</a>
<a
class="nav-card"
href="/projects/farmlink/admin/investor_analytics.php">

<span>📈</span>

ROI Analytics

</a>
<a
class="nav-card"
href="/projects/farmlink/admin/withdrawals.php">

<span>💸</span>

Withdrawals

</a>
<a
class="nav-card"
href="/projects/farmlink/admin/pending_users.php">

<span>⏳</span>

Pending Users

</a>


<?php elseif($_SESSION['role']=="investor"): ?>

<?php

$stmt=$pdo->prepare("

SELECT COUNT(*)

FROM notifications

WHERE user_id=?
AND status='unread'

");

$stmt->execute([

$_SESSION['user_id']

]);

$unread=
$stmt->fetchColumn();

?>

<a class="nav-card"
href="/projects/farmlink/investor/dashboard.php">

<span>🏦</span>

Dashboard

</a>

<a class="nav-card"
href="/projects/farmlink/investor/earnings.php">

<span>💵</span>

Earnings

</a>

<a class="nav-card"
href="/projects/farmlink/investor/reports.php">

<span>📊</span>

Reports

</a>
<a class="nav-card"
href="/projects/farmlink/investor/statement.php">

<span>📄</span>

Statement

</a>

<a class="nav-card"
href="/projects/farmlink/investor/profile.php">

<span>👤</span>

Profile

</a>

<a class="nav-card"
href="/projects/farmlink/investor/withdrawals.php">

<span>💰</span>

Withdrawal

</a>

<?php


$unread=0;


if(
isset(
$_SESSION['user_id']
)
){

$stmt=
$pdo->prepare(

"

SELECT COUNT(*)

FROM notifications

WHERE user_id=?
AND status='unread'

"

);


$stmt->execute([

$_SESSION['user_id']

]);


$unread=
$stmt->fetchColumn();

}

?>

<a
class="nav-card"
href="/projects/farmlink/notifications.php">

<span>

🔔

</span>

Notifications

<?php if($unread): ?>

<div
class="badge bg-danger">

<?= $unread ?>

</div>

<?php endif; ?>

</a>

Notifications

</a>
<a
class="nav-card"
href="/projects/farmlink/investor/activity.php">

<span>📝</span>

Activity

</a>

<?php elseif($_SESSION['role']=="farmer"): ?>

<a class="nav-card"
href="/projects/farmlink/farmer/dashboard.php"> <span>🏠</span>
Home </a>

<a class="nav-card"
href="/projects/farmlink/farmer/products.php"> <span>🌾</span>
Products </a>

<a class="nav-card"
href="/projects/farmlink/farmer/orders.php"> <span>📦</span>
Orders </a>

<a class="nav-card"
href="/projects/farmlink/farmer/sales_report.php"> <span>📈</span>
Sales </a>

<a class="nav-card"
href="/projects/farmlink/farmer/earnings.php"> <span>💵</span>
Earnings </a>

<?php elseif($_SESSION['role']=="buyer"): ?>

<a class="nav-card"
href="/projects/farmlink/buyer/dashboard.php"> <span>🏠</span>
Home </a>

<a class="nav-card"
href="/projects/farmlink/buyer/marketplace.php"> <span>🛒</span>
Market </a>

<a class="nav-card"
href="/projects/farmlink/buyer/orders.php"> <span>📦</span>
Orders </a>

<a class="nav-card"
href="/projects/farmlink/buyer/purchase_history.php"> <span>📄</span>
History </a>

<?php elseif($_SESSION['role']=="trucker"): ?>

<a class="nav-card"
href="/projects/farmlink/trucker/dashboard.php"> <span>🚚</span>
Home </a>

<a class="nav-card"
href="/projects/farmlink/trucker/deliveries.php"> <span>📬</span>
Available </a>

<a class="nav-card"
href="/projects/farmlink/trucker/my_deliveries.php"> <span>📦</span>
My Jobs </a>

<?php endif; ?>

<a class="nav-card"
href="/projects/farmlink/logout.php"> <span>🚪</span>
Logout </a>

<?php else: ?>

<a class="nav-card"
href="/projects/farmlink/index.php"> <span>🏠</span>
Home </a>

<a class="nav-card"
href="/projects/farmlink/about.php"> <span>ℹ️</span>
About </a>

<a class="nav-card"
href="/projects/farmlink/contact.php"> <span>📞</span>
Contact </a>

<a class="nav-card"
href="/projects/farmlink/login.php"> <span>🔑</span>
Login </a>

<a class="nav-card"
href="/projects/farmlink/register.php"> <span>📝</span>
Register </a>

<?php endif; ?>

</div>

</div>

</nav>


<script src="/projects/farmlink/assets/js/theme.js"></script>
