<?php
require __DIR__ . '/../vendor/autoload.php';

$page = $_GET['page'] ?? 'login';

$routes = [
    'login' => __DIR__ . '/../csit314-csr-platform/src/login/boundary/loginUI.php',
    'dashboard' => __DIR__ . '/../csit314-csr-platform/src/shared/boundary/DashboardUI.php',
    'admin-dashboard' => __DIR__ . '/../csit314-csr-platform/src/shared/boundary/DashboardUI.php',
    'admin-accounts' => __DIR__ . '/../csit314-csr-platform/src/admin/boundary/ViewAccountsUI.php',
    'admin-account-create' => __DIR__ . '/../csit314-csr-platform/src/admin/boundary/CreateAccountUI.php',
    'admin-account-view' => __DIR__ . '/../csit314-csr-platform/src/admin/boundary/ViewUserAccountUI.php',
    'admin-account-edit' => __DIR__ . '/../csit314-csr-platform/src/admin/boundary/EditUserAccountUI.php',
    'admin-profiles' => __DIR__ . '/../csit314-csr-platform/src/admin/boundary/ViewProfilesUI.php',
    'admin-profile-create' => __DIR__ . '/../csit314-csr-platform/src/admin/boundary/CreateProfileUI.php',
    'admin-profile-view' => __DIR__ . '/../csit314-csr-platform/src/admin/boundary/ViewProfileUI.php',
    'admin-profile-edit' => __DIR__ . '/../csit314-csr-platform/src/admin/boundary/EditProfileUI.php',
    'csr-requests' => __DIR__ . '/../csit314-csr-platform/src/csr-representative/boundary/SearchRequestsUI.php',
    'csr-shortlist' => __DIR__ . '/../csit314-csr-platform/src/csr-representative/boundary/ShortlistUI.php',
    'csr-history' => __DIR__ . '/../csit314-csr-platform/src/csr-representative/boundary/HistoryUI.php',
    'pin-requests' => __DIR__ . '/../csit314-csr-platform/src/pin/boundary/viewRequestsUI.php',
    'pin-request-create' => __DIR__ . '/../csit314-csr-platform/src/pin/boundary/createRequestUI.php',
    'pin-request-edit' => __DIR__ . '/../csit314-csr-platform/src/pin/boundary/editRequestUI.php',
    'pin-request-shortlist' => __DIR__ . '/../csit314-csr-platform/src/pin/boundary/viewRequestShortlistCountUI.php',
    'pin-request-views' => __DIR__ . '/../csit314-csr-platform/src/pin/boundary/viewRequestViewCountUI.php',
    'pin-history' => __DIR__ . '/../csit314-csr-platform/src/pin/boundary/HistoryUI.php',
    'pm-categories' => __DIR__ . '/../csit314-csr-platform/src/PM/boundary/ServiceCategoryUI.php',
    'pm-report-daily' => __DIR__ . '/../csit314-csr-platform/src/PM/boundary/DailyReportUI.php',
    'pm-report-weekly' => __DIR__ . '/../csit314-csr-platform/src/PM/boundary/WeeklyReportUI.php',
];

if (!array_key_exists($page, $routes)) {
    http_response_code(404);
    echo '<h1>404 Not Found</h1>';
    exit();
}

require $routes[$page];
