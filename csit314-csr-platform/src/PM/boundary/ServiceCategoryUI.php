<?php
require_once __DIR__ . '/../controller/viewServiceCategoryController.php';
require_once __DIR__ . '/../controller/updateServiceCategoryController.php';

use CSRPlatform\PM\Controller\updateServiceCategoryController;
use CSRPlatform\PM\Controller\viewServiceCategoryController;
use CSRPlatform\Shared\Entity\ServiceCategories;
use CSRPlatform\Shared\Utils\Validation;

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

$isAdmin = ($currentUser['role'] ?? '') === 'admin';
$isPm = ($currentUser['role'] ?? '') === 'pm';
if (!$isAdmin && !$isPm) {
    header('Location: /index.php?page=login');
    exit();
}

$categoriesEntity = new ServiceCategories();
$viewController = new viewServiceCategoryController($categoriesEntity);
$updateController = new updateServiceCategoryController($categoriesEntity, new Validation());

$showCreateForm = isset($_GET['create']);
$categoryNameValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['form_type'] ?? '') === 'create') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $categoriesEntity->createCategory($name);
            $_SESSION['flash_success'] = 'Category created successfully.';
            header('Location: /index.php?page=pm-categories');
            exit();
        } else {
            $_SESSION['flash_error'] = 'Category name is required.';
            $showCreateForm = true;
            $categoryNameValue = $name;
        }
    } else {
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $payload = [
            'status' => $_POST['status'] ?? null,
        ];
        if (!empty($_POST['name'])) {
            $payload['name'] = $_POST['name'];
        }
        if ($categoryId > 0 && $updateController->update($categoryId, $payload)) {
            $_SESSION['flash_success'] = 'Category updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Unable to update category.';
        }
        header('Location: /index.php?page=pm-categories');
        exit();
    }
}

$searchQuery = $_GET['q'] ?? '';
$categories = $viewController->list('all', $searchQuery);

$pageTitle = 'Service categories';
if ($isAdmin) {
    $baseUrl = '/index.php?page=admin-dashboard';
    $navLinks = [
        ['href' => '/index.php?page=admin-dashboard', 'label' => 'Dashboard'],
        ['href' => '/index.php?page=admin-accounts', 'label' => 'Users'],
        ['href' => '/index.php?page=admin-profiles', 'label' => 'Profiles'],
        ['href' => '/index.php?page=pm-categories', 'label' => 'Categories'],
    ];
} else {
    $navLinks = [
        ['href' => '/index.php?page=pm-categories', 'label' => 'Categories'],
        ['href' => '/index.php?page=pm-report-daily', 'label' => 'Daily report'],
        ['href' => '/index.php?page=pm-report-weekly', 'label' => 'Weekly report'],
    ];
}
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <div class="card-heading">
        <div>
            <h1>Service categories</h1>
            <p class="muted">Manage the categories that classify requests.</p>
        </div>
        <div class="card-actions">
            <a class="btn-primary" href="/index.php?page=pm-categories&amp;create=1#category-create">New Category</a>
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
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?></td>
                    <td><span class="tag tag-<?= htmlspecialchars((string) $category['status'], ENT_QUOTES) ?>"><?= htmlspecialchars((string) $category['status'], ENT_QUOTES) ?></span></td>
                    <td>
                        <form method="POST" action="/index.php?page=pm-categories" class="form-inline">
                            <input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>">
                            <input type="text" name="name" value="<?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?>" placeholder="Rename category">
                            <select name="status">
                                <option value="active" <?= $category['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $category['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <button type="submit" class="btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($categories === []): ?>
                <tr>
                    <td colspan="3" style="text-align: center;">No categories available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php if ($showCreateForm): ?>
<section class="card" id="category-create">
    <div class="card-heading">
        <div>
            <h2>Create new category</h2>
            <p class="muted">Add a fresh service type to keep the catalog current.</p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="/index.php?page=pm-categories">Cancel</a>
        </div>
    </div>
    <form method="POST" action="/index.php?page=pm-categories" class="form-inline">
        <input type="hidden" name="form_type" value="create">
        <input type="text" name="name" placeholder="Category name" value="<?= htmlspecialchars($categoryNameValue, ENT_QUOTES) ?>" required>
        <button type="submit" class="btn-primary">Add category</button>
    </form>
</section>
<?php endif; ?>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
