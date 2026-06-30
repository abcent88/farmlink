<?php
if(session_status()===PHP_SESSION_NONE){
session_start();
}
?>

<style>

:root{

--nav-bg:#198754;
--nav-card:#ffffff;
--nav-text:#111;
--shadow:
0 6px 24px
rgba(
0,
0,
0,
0.15
);

}

[data-theme="dark"]{

--nav-bg:#121212;
--nav-card:#1f1f1f;
--nav-text:#fff;

}

.app-navbar{

background:
var(--nav-bg);

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
repeat(
auto-fit,
minmax(
90px,
1fr
));

gap:12px;

margin-top:18px;

}

.nav-card{

background:
var(--nav-card);

color:
var(--nav-text);

text-decoration:none;

padding:18px;

border-radius:18px;

text-align:center;

box-shadow:
var(--shadow);

transition:.25s;

}

.nav-card:hover{

transform:
translateY(-4px);

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

background:
var(--nav-bg);

padding:12px;

border-radius:28px;

display:flex;

justify-content:space-around;

box-shadow:
0 10px 30px
rgba(
0,
0,
0,
0.25
);

z-index:9999;

}

.nav-card{

background:none;

color:white;

padding:6px;

box-shadow:none;

font-size:12px;

}

.nav-card span{

font-size:24px;

margin-bottom:4px;

}

body{

padding-bottom:120px;

}

}

</style>


<nav class="app-navbar">

<div class="container">

<div class="d-flex justify-content-between align-items-center">

<a
class="brand"
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

<a
class="nav-card"
href="/projects/farmlink/admin/dashboard.php">

<span>📊</span>

Dashboard

</a>

<a
class="nav-card"
href="/projects/farmlink/admin/users.php">

<span>👥</span>

Users

</a>

<a
class="nav-card"
href="/projects/farmlink/admin/products.php">

<span>🌾</span>

Products

</a>

<a
class="nav-card"
href="/projects/farmlink/admin/orders.php">

<span>📦</span>

Orders

</a>

<a
class="nav-card"
href="/projects/farmlink/admin/revenue.php">

<span>💰</span>

Revenue

</a>

<?php elseif($_SESSION['role']=="farmer"): ?>

<a
class="nav-card"
href="/projects/farmlink/farmer/dashboard.php">

<span>🏠</span>

Home

</a>

<a
class="nav-card"
href="/projects/farmlink/farmer/products.php">

<span>🌾</span>

Products

</a>

<a
class="nav-card"
href="/projects/farmlink/farmer/orders.php">

<span>📦</span>

Orders

</a>

<a
class="nav-card"
href="/projects/farmlink/farmer/sales_report.php">

<span>📈</span>

Sales

</a>

<a
class="nav-card"
href="/projects/farmlink/farmer/earnings.php">

<span>💵</span>

Earnings

</a>

<?php elseif($_SESSION['role']=="buyer"): ?>

<a
class="nav-card"
href="/projects/farmlink/buyer/dashboard.php">

<span>🏠</span>

Home

</a>

<a
class="nav-card"
href="/projects/farmlink/buyer/marketplace.php">

<span>🛒</span>

Market

</a>

<a
class="nav-card"
href="/projects/farmlink/buyer/orders.php">

<span>📦</span>

Orders

</a>

<a
class="nav-card"
href="/projects/farmlink/buyer/purchase_history.php">

<span>📄</span>

History

</a>

<?php elseif($_SESSION['role']=="trucker"): ?>

<a
class="nav-card"
href="/projects/farmlink/trucker/dashboard.php">

<span>🚚</span>

Home

</a>

<a
class="nav-card"
href="/projects/farmlink/trucker/deliveries.php">

<span>📬</span>

Available

</a>

<a
class="nav-card"
href="/projects/farmlink/trucker/my_deliveries.php">

<span>📦</span>

My Jobs

</a>

<?php endif; ?>

<a
class="nav-card"
href="/projects/farmlink/logout.php">

<span>🚪</span>

Logout

</a>

<?php else: ?>

<a
class="nav-card"
href="/projects/farmlink/index.php">

<span>🏠</span>

Home

</a>

<a
class="nav-card"
href="/projects/farmlink/about.php">

<span>ℹ️</span>

About

</a>

<a
class="nav-card"
href="/projects/farmlink/contact.php">

<span>📞</span>

Contact

</a>

<a
class="nav-card"
href="/projects/farmlink/login.php">

<span>🔑</span>

Login

</a>

<a
class="nav-card"
href="/projects/farmlink/register.php">

<span>📝</span>

Register

</a>

<?php endif; ?>

</div>

</div>

</nav>

<script src="/projects/farmlink/assets/js/theme.js"></script>
