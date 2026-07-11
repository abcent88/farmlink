<?php

mobileLink(
    "/projects/farmlink/admin/dashboard.php",
    "📊",
    "Dashboard"
);

mobileLink(
    "/projects/farmlink/admin/users.php",
    "👥",
    "Users"
);

mobileLink(
    "/projects/farmlink/admin/products.php",
    "🌾",
    "Products"
);

mobileLink(
    "/projects/farmlink/admin/orders.php",
    "📦",
    "Orders"
);

mobileLink(
    "/projects/farmlink/admin/revenue.php",
    "💰",
    "Revenue"
);

?>

<button
    type="button"
    class="nav-card border-0 bg-transparent"
    data-bs-toggle="offcanvas"
    data-bs-target="#adminMenu">

    <span>☰</span>

    <small>Menu</small>

</button>
<?php

mobileLink(
    "/projects/farmlink/logout.php",
    "🚪",
    "Logout"
);

?>