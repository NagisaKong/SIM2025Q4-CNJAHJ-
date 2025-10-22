<?php
require __DIR__ . '/../vendor/autoload.php';

$page = $_GET['page'] ?? 'login';

$routes = [
    'login' => __DIR__ . '/../csit314-csr-platform/src/login/boundary/loginUI.php',
    'admin-dashboard' => __DIR__ . '/../csit314-csr-platform/src/admin/boundary/DashboardUI.php',
    'admin-accounts' => __DIR__ . '/../csit314-csr-platform/src/admin/boundary/ViewAccountsUI.php',
    'admin-profiles' => __DIR__ . '/../csit314-csr-platform/src/admin/boundary/ViewProfilesUI.php',
    'csr-requests' => __DIR__ . '/../csit314-csr-platform/src/csr-representative/boundary/SearchRequestsUI.php',
    'csr-shortlist' => __DIR__ . '/../csit314-csr-platform/src/csr-representative/boundary/ShortlistUI.php',
    'csr-history' => __DIR__ . '/../csit314-csr-platform/src/csr-representative/boundary/HistoryUI.php',
    'pin-requests' => __DIR__ . '/../csit314-csr-platform/src/pin/boundary/RequestManagementUI.php',
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
