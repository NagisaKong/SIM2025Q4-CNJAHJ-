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

$categoriesEntity = new ServiceCategories();
$viewController = new viewServiceCategoryController($categoriesEntity);

$searchQuery = $_GET['q'] ?? '';
$categories = $viewController->listServiceCategories('all', $searchQuery);

$pageTitle = 'Service categories';
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
            <h1>Service categories</h1>
            <p class="muted">Manage the categories that classify requests.</p>
        </div>
        <div class="card-actions">
            <a class="btn-primary" href="/index.php?page=pm-category-update&amp;mode=create">New category</a>
        </div>
    </div>
    <form method="GET" action="/index.php" class="form-inline">
        <input type="hidden" name="page" value="pm-categories">
        <input type="search" name="q" placeholder="Search categories" value="<?= htmlspecialchars((string) $searchQuery, ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Search</button>
        <?php if ($searchQuery !== ''): ?>
            <a href="/index.php?page=pm-categories" class="btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Updated</th>
                <th class="align-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?></td>
                    <td><span class="tag tag-<?= htmlspecialchars((string) $category['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $category['status'], ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars((string) ($category['updated_at'] ?? ''), ENT_QUOTES) ?></td>
                    <td class="align-right">
                        <a class="btn-secondary" href="/index.php?page=pm-category-view&amp;id=<?= (int) $category['id'] ?>">View</a>
                        <a class="btn-primary" href="/index.php?page=pm-category-update&amp;id=<?= (int) $category['id'] ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($categories === []): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No categories available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
