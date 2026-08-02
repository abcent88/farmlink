<?php

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

navLink(
    "/projects/farmlink/admin/lga_admin/lga_admin_dashboard.php",
    "Dashboard",
    "bi bi-speedometer2"
);

/*
|--------------------------------------------------------------------------
| Farmer Verification
|--------------------------------------------------------------------------
*/

navLink(
    "/projects/farmlink/admin/lga_admin/farmer_verifications.php",
    "Pending Verifications",
    "bi bi-patch-check"
);

navLink(
    "/projects/farmlink/admin/lga_admin/verified_farmers.php",
    "Verified Farmers",
    "bi bi-check-circle"
);

navLink(
    "/projects/farmlink/admin/lga_admin/rejected_farmers.php",
    "Rejected Farmers",
    "bi bi-x-circle"
);

/*
|--------------------------------------------------------------------------
| Order & Delivery Management
|--------------------------------------------------------------------------
*/

navLink(
    "/projects/farmlink/admin/lga_admin/orders.php",
    "Pending Approvals",
    "bi bi-cart-check"
);

navLink(
    "/projects/farmlink/admin/deliveries.php",
    "Approved Deliveries",
    "bi bi-truck"
);

/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
*/

navLink(
    "/projects/farmlink/admin/lga_admin/commissions.php",
    "Commissions",
    "bi bi-cash-stack"
);

navLink(
    "/projects/farmlink/admin/notifications.php",
    "Notifications",
    "bi bi-bell"
);

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

navLink(
    "/projects/farmlink/logout.php",
    "Logout",
    "bi bi-box-arrow-right"
);