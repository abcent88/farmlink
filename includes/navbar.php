<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('navLink')) {

    function navLink($href, $label, $icon = '')
    {
        echo '
        <li class="nav-item">
            <a class="nav-link" href="'.$href.'">';

        if($icon){
            echo '<i class="'.$icon.' me-1"></i>';
        }

        echo htmlspecialchars($label);

        echo '
            </a>
        </li>';
    }

}

if (!function_exists('mobileLink')) {

    function mobileLink($href,$icon,$title)
    {
        echo '
        <a class="nav-card" href="'.$href.'">
            <span>'.$icon.'</span>
            <small>'.$title.'</small>
        </a>';
    }

}

$unread = 0;

if(
    isset($_SESSION['user_id'])
    &&
    isset($pdo)
){

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM notifications
        WHERE user_id=?
        AND status='unread'
    ");

    $stmt->execute([
        $_SESSION['user_id']
    ]);

    $unread = $stmt->fetchColumn();

}

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow sticky-top">

<div class="container">

<a
class="navbar-brand fw-bold"
href="/projects/farmlink/index.php">

🌱 FarmLink

</a>

<button
class="navbar-toggler"
type="button"
data-bs-toggle="collapse"
data-bs-target="#mainNavbar"
aria-controls="mainNavbar"
aria-expanded="false"
aria-label="Toggle navigation">

<span class="navbar-toggler-icon"></span>

</button>

<div
class="collapse navbar-collapse"
id="mainNavbar">

<ul class="navbar-nav me-auto">
   <?php

if(!isset($_SESSION['user_id'])){

    require __DIR__.'/menus/guest.php';

}else{

    switch($_SESSION['role']){

        case 'super_admin':
            require __DIR__.'/menus/super_admin.php';
            break;

        case 'investor':
            require __DIR__.'/menus/investor.php';
            break;

        case 'farmer':
            require __DIR__.'/menus/farmer.php';
            break;

        case 'buyer':
            require __DIR__.'/menus/buyer.php';
            break;

        case 'trucker':
            require __DIR__.'/menus/trucker.php';
            break;
            case 'lga_admin':
    include __DIR__ . '/menus/lga_admin.php';
    break;
            
    }

}

?>

</ul>
<ul class="navbar-nav ms-auto">

    <li class="nav-item">

        <button
            class="btn btn-outline-light"
            onclick="toggleTheme()">

            <i class="bi bi-moon-stars"></i>

        </button>

    </li>

</ul>

</div>

</div>

</nav>

<?php

if (isset($_SESSION['role'])) {

    switch ($_SESSION['role']) {

        case 'super_admin':
            include __DIR__ . '/menus/admin_offcanvas.php';
            break;

        case 'investor':
            include __DIR__ . '/menus/investor_offcanvas.php';
            break;

        case 'farmer':
            include __DIR__ . '/menus/farmer_offcanvas.php';
            break;

        case 'buyer':
            include __DIR__ . '/menus/buyer_offcanvas.php';
            break;

        case 'trucker':
            include __DIR__ . '/menus/trucker_offcanvas.php';
            break;

        case 'lga_admin':
            include __DIR__ . '/menus/lga_admin_offcanvas.php';
            break;
    }
}

?>

<script src="/projects/farmlink/assets/js/theme.js"></script>