<?php

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

mobileLink(
    "/projects/farmlink/trucker/dashboard.php",
    "🏠",
    "Dashboard"
);

/*
|--------------------------------------------------------------------------
| Available Deliveries
|--------------------------------------------------------------------------
*/

mobileLink(
    "/projects/farmlink/trucker/deliveries.php",
    "📬",
    "Available"
);

/*
|--------------------------------------------------------------------------
| My Deliveries
|--------------------------------------------------------------------------
*/

mobileLink(
    "/projects/farmlink/trucker/my_deliveries.php",
    "🚚",
    "My Deliveries"
);


navLink(
    "/projects/farmlink/testimonial.php",
    "Share Experience",
        "bi bi-star-fill me-1"
      
);
/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

mobileLink(
    "/projects/farmlink/trucker/notifications.php",
    "🔔",
    "Alerts"
);

?>

<button
    type="button"
    class="nav-card border-0 bg-transparent"
    data-bs-toggle="offcanvas"
    data-bs-target="#truckerMenu">

    <span>☰</span>

    <small>Menu</small>

</button>