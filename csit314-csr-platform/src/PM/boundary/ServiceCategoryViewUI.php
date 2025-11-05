<?php
require_once __DIR__ . '/../controller/viewServiceCategoryController.php';

use CSRPlatform\PM\Controller\viewServiceCategoryController;
use CSRPlatform\Shared\Entity\ServiceCategories;

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
if ($roleKey !== 'pm') {
    header('Location: /index.php?page=dashboard');
    exit();
}

$serviceId = $_GET['id'] ?? '';
if ($serviceId === '') {
    $_SESSION['flash_error'] = 'Please choose a service category to view.';
    header('Location: /index.php?page=pm-categories');
    exit();
}

$categoriesEntity = new ServiceCategories();
$viewController = new viewServiceCategoryController($categoriesEntity);
$category = $viewController->viewServiceCategory((string) $serviceId);

if ($category === []) {
    header('Location: /index.php?page=pm-categories');
    exit();
}

$pageTitle = 'Service category details';
$navLinks = [
    ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
    ['href' => '/index.php?page=pm-categories', 'label' => 'Categories'],
    ['href' => '/index.php?page=pm-report-daily', 'label' => 'Daily report'],
    ['href' => '/index.php?page=pm-report-weekly', 'label' => 'Weekly report'],
    ['href' => '/index.php?page=pm-report-monthly', 'label' => 'Monthly report'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <div class="card-heading">
        <div>
            <h1><?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?></h1>
            <p class="muted">Full details for this service category.</p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="/index.php?page=pm-categories">Back to categories</a>
            <a class="btn-primary" href="/index.php?page=pm-category-update&amp;id=<?= (int) $category['id'] ?>">Edit category</a>
        </div>
    </div>
    <div class="card-body">
        <dl class="detail-list">
            <div class="detail-item">
                <dt>Status</dt>
                <dd><span class="tag tag-<?= htmlspecialchars((string) $category['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $category['status'], ENT_QUOTES) ?></span></dd>
            </div>
            <div class="detail-item">
                <dt>Description</dt>
                <dd><?= nl2br(htmlspecialchars((string) ($category['description'] ?? ''), ENT_QUOTES)) ?></dd>
            </div>
            <div class="detail-item">
                <dt>Created</dt>
                <dd><?= htmlspecialchars((string) ($category['created_at'] ?? ''), ENT_QUOTES) ?></dd>
            </div>
            <div class="detail-item">
                <dt>Last updated</dt>
                <dd><?= htmlspecialchars((string) ($category['updated_at'] ?? ''), ENT_QUOTES) ?></dd>
            </div>
        </dl>
    </div>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
