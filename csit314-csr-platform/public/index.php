<?php
declare(strict_types=1);

$routes = [
    'login' => __DIR__ . '/../src/login/boundary/loginUI.php',
    'dashboard' => __DIR__ . '/../src/shared/boundary/DashboardUI.php',
    'admin/view-accounts' => __DIR__ . '/../src/admin/boundary/ViewAccountsUI.php',
    'admin/create-account' => __DIR__ . '/../src/admin/boundary/CreateAccountUI.php',
    'admin/update-account' => __DIR__ . '/../src/admin/boundary/UpdateAccountUI.php',
    'admin/view-profiles' => __DIR__ . '/../src/admin/boundary/ViewProfilesUI.php',
    'admin/create-profile' => __DIR__ . '/../src/admin/boundary/CreateProfileUI.php',
    'csr/search-requests' => __DIR__ . '/../src/csr-representative/boundary/SearchRequestUI.php',
    'csr/shortlist' => __DIR__ . '/../src/csr-representative/boundary/ViewShortlistedRequestUI.php',
    'csr/history' => __DIR__ . '/../src/csr-representative/boundary/ViewCSRHistoryUI.php',
    'csr/view-request' => __DIR__ . '/../src/csr-representative/boundary/ViewRequestDetailUI.php',
    'pin/my-requests' => __DIR__ . '/../src/pin/boundary/SearchPostedRequestsUI.php',
    'pin/create-request' => __DIR__ . '/../src/pin/boundary/CreateRequestUI.php',
    'pin/view-request' => __DIR__ . '/../src/pin/boundary/ViewRequestUI.php',
    'pin/history' => __DIR__ . '/../src/pin/boundary/ViewPINHistoryUI.php',
    'pm/categories' => __DIR__ . '/../src/PM/boundary/ViewServiceCategoryUI.php',
    'pm/update-category' => __DIR__ . '/../src/PM/boundary/UpdateServiceCategoryUI.php',
    'pm/daily-report' => __DIR__ . '/../src/PM/boundary/GenerateDailyReportUI.php',
    'pm/weekly-report' => __DIR__ . '/../src/PM/boundary/GenerateWeeklyReportUI.php',
];

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$route = $_GET['route'] ?? null;

if (!$route) {
    if (isset($_SESSION['user'])) {
        $route = 'dashboard';
    } else {
        $route = 'login';
    }
}

if (!isset($routes[$route])) {
    http_response_code(404);
    echo '<h1>404 Not Found</h1>';
    exit();
}

require $routes[$route];
