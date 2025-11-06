<?php
require_once __DIR__ . '/../entity/UserAccount.php';
require_once __DIR__ . '/../entity/UserProfiles.php';
require_once __DIR__ . '/../entity/Request.php';
require_once __DIR__ . '/../entity/serviceCategories.php';

use CSRPlatform\Shared\Entity\Request;
use CSRPlatform\Shared\Entity\ServiceCategories;
use CSRPlatform\Shared\Entity\UserAccount;
use CSRPlatform\Shared\Entity\UserProfiles;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
    header('Location: /index.php?page=login');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

$currentUser = $_SESSION['user'] ?? null;
if (!$currentUser) {
    header('Location: /index.php?page=login');
    exit();
}

$roleKey = strtolower((string) ($currentUser['role'] ?? ''));
$displayName = $currentUser['name'] ?? 'User';
$userId = (int) ($currentUser['id'] ?? 0);

$navByRole = [
    'admin' => [
        ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
        ['href' => '/index.php?page=admin-accounts', 'label' => 'Users'],
        ['href' => '/index.php?page=admin-profiles', 'label' => 'Profiles'],
    ],
    'csr' => [
        ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
        ['href' => '/index.php?page=csr-requests', 'label' => 'Opportunities'],
        ['href' => '/index.php?page=csr-shortlist', 'label' => 'Shortlist'],
        ['href' => '/index.php?page=csr-history', 'label' => 'History'],
    ],
    'pin' => [
        ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
        ['href' => '/index.php?page=pin-requests', 'label' => 'Requests'],
        ['href' => '/index.php?page=pin-history', 'label' => 'History'],
    ],
    'pm' => [
        ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
        ['href' => '/index.php?page=pm-categories', 'label' => 'Categories'],
        ['href' => '/index.php?page=pm-report-daily', 'label' => 'Daily report'],
        ['href' => '/index.php?page=pm-report-weekly', 'label' => 'Weekly report'],
    ],
];

$navLinks = $navByRole[$roleKey] ?? [['href' => '/index.php?page=dashboard', 'label' => 'Dashboard']];

$roleDescriptions = [
    'admin' => 'Review the health of your platform and jump into key management tasks.',
    'csr' => 'Stay on top of the latest community requests and track your volunteer history.',
    'pin' => 'Manage the support you have requested and monitor how CSRs are responding.',
    'pm' => 'Keep service categories relevant and analyse platform engagement at a glance.',
];

$cardSets = [
    'admin' => [
        ['href' => '/index.php?page=admin-accounts', 'title' => 'Manage Users', 'meta' => '', 'description' => 'Create, update, or suspend user accounts.'],
        ['href' => '/index.php?page=admin-profiles', 'title' => 'Manage Profiles', 'meta' => '', 'description' => 'Define roles and review profile permissions.'],
    ],
    'csr' => [
        ['href' => '/index.php?page=csr-requests', 'title' => 'Search Opportunities', 'meta' => '', 'description' => 'Filter active requests to find your next match.'],
        ['href' => '/index.php?page=csr-shortlist', 'title' => 'View Shortlist', 'meta' => '', 'description' => 'Review the requests you have saved for later.'],
        ['href' => '/index.php?page=csr-history', 'title' => 'Service History', 'meta' => '', 'description' => 'Track the volunteer services you have completed.'],
    ],
    'pin' => [
        ['href' => '/index.php?page=pin-requests', 'title' => 'Manage Requests', 'meta' => '', 'description' => 'Create, update, or close your assistance requests.'],
        ['href' => '/index.php?page=pin-history', 'title' => 'Match History', 'meta' => '', 'description' => 'See the services that have already been fulfilled.'],
    ],
    'pm' => [
        ['href' => '/index.php?page=pm-categories', 'title' => 'Service Categories', 'meta' => '', 'description' => 'Add, edit, or suspend categories.'],
        ['href' => '/index.php?page=pm-report-daily', 'title' => 'Daily Report', 'meta' => '', 'description' => 'Generate a snapshot of today’s activity.'],
        ['href' => '/index.php?page=pm-report-weekly', 'title' => 'Weekly Report', 'meta' => '', 'description' => 'Summarise the past week’s performance.'],
    ],
];

if ($roleKey === 'admin') {
    $accountEntity = new UserAccount();
    $profileEntity = new UserProfiles();
    $accounts = $accountEntity->listAccounts('all', null);
    $profiles = $profileEntity->listProfiles('all');
    $cardSets['admin'][0]['meta'] = count($accounts) . ' total accounts';
    $cardSets['admin'][1]['meta'] = count($profiles) . ' profiles defined';
} elseif ($roleKey === 'csr') {
    $requestEntity = new Request();
    $shortlisted = $requestEntity->listShortlistedRequests($userId);
    $cardSets['csr'][1]['meta'] = count($shortlisted) . ' saved';
} elseif ($roleKey === 'pin') {
    $requestEntity = new Request();
    $requests = $requestEntity->listRequestsByPin($userId);
    $cardSets['pin'][0]['meta'] = count($requests) . ' requests';
    $history = $requestEntity->requestHistory($userId);
    $cardSets['pin'][1]['meta'] = count($history) . ' records';
} elseif ($roleKey === 'pm') {
    $categoryEntity = new ServiceCategories();
    $categories = $categoryEntity->listCategories('all');
    $cardSets['pm'][0]['meta'] = count($categories) . ' categories';
}

$pageTitle = 'Dashboard overview';
$navLinks = array_values($navLinks);
$roleSummary = $roleDescriptions[$roleKey] ?? 'Explore the modules available to your role.';
$cards = $cardSets[$roleKey] ?? [];

include __DIR__ . '/header.php';
?>
<section class="card dashboard-card">
    <div class="card-heading">
        <div>
            <p class="muted">Hello again</p>
            <h1>Welcome, <?= htmlspecialchars((string) $displayName, ENT_QUOTES) ?></h1>
            <p class="muted"><?= htmlspecialchars($roleSummary, ENT_QUOTES) ?></p>
        </div>
    </div>
    <div class="dashboard-actions">
        <?php foreach ($cards as $card): ?>
            <a class="dashboard-tile" href="<?= htmlspecialchars($card['href'], ENT_QUOTES) ?>">
                <span class="tile-title"><?= htmlspecialchars($card['title'], ENT_QUOTES) ?></span>
                <?php if (!empty($card['meta'])): ?>
                    <span class="tile-meta"><?= htmlspecialchars((string) $card['meta'], ENT_QUOTES) ?></span>
                <?php endif; ?>
                <span class="tile-description"><?= htmlspecialchars($card['description'], ENT_QUOTES) ?></span>
            </a>
        <?php endforeach; ?>
        <?php if ($cards === []): ?>
            <p class="muted">No modules available for your role.</p>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
