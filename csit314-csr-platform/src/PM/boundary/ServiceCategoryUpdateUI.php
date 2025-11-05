<?php
require_once __DIR__ . '/../controller/viewServiceCategoryController.php';
require_once __DIR__ . '/../controller/updateServiceCategoryController.php';

use CSRPlatform\PM\Controller\updateServiceCategoryController;
use CSRPlatform\PM\Controller\viewServiceCategoryController;
use CSRPlatform\Shared\Boundary\FormValidator;
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
$updateController = new updateServiceCategoryController($categoriesEntity, new FormValidator());

$mode = strtolower($_GET['mode'] ?? 'edit');
$serviceId = $_GET['id'] ?? '';
$errors = [];

if ($mode !== 'create' && $serviceId === '') {
    $_SESSION['flash_error'] = 'Please choose a service category to edit.';
    header('Location: /index.php?page=pm-categories');
    exit();
}

$category = null;
if ($mode !== 'create') {
    $category = $viewController->viewServiceCategory((string) $serviceId);
    if ($category === []) {
        header('Location: /index.php?page=pm-categories');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = strtolower(trim($_POST['status'] ?? 'active'));
    $action = $_POST['action'] ?? 'save';

    if ($mode === 'create') {
        if ($updateController->createServiceCategory($name, $description, $status)) {
            $newId = $categoriesEntity->getLastInsertedId() ?? 0;
            $_SESSION['flash_success'] = 'Service category created successfully.';
            if ($newId > 0) {
                header('Location: /index.php?page=pm-category-view&id=' . $newId);
            } else {
                header('Location: /index.php?page=pm-categories');
            }
            exit();
        }
        $errors = $updateController->errors();
    } elseif ($action === 'suspend') {
        if ($updateController->suspendServiceCategory((string) $serviceId, $status)) {
            $_SESSION['flash_success'] = 'Service category status updated.';
            header('Location: /index.php?page=pm-category-view&id=' . (int) $serviceId);
            exit();
        }
        $errors = $updateController->errors();
    } else {
        if ($updateController->updateServiceCategory((string) $serviceId, $name, $description, $status)) {
            $_SESSION['flash_success'] = 'Service category updated successfully.';
            header('Location: /index.php?page=pm-category-view&id=' . (int) $serviceId);
            exit();
        }
        $errors = $updateController->errors();
    }
}

$pageTitle = $mode === 'create' ? 'Create service category' : 'Update service category';
$primaryActionLabel = $mode === 'create' ? 'Create category' : 'Save changes';
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
            <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?></h1>
            <p class="muted">Maintain the details of your service categories.</p>
        </div>
        <div class="card-actions">
            <a class="btn-secondary" href="<?= $mode === 'create' ? '/index.php?page=pm-categories' : '/index.php?page=pm-category-view&id=' . (int) $serviceId ?>">Cancel</a>
        </div>
    </div>
    <form method="POST" class="stacked-form">
        <label>Category name
            <input type="text" name="name" value="<?= htmlspecialchars((string) ($category['name'] ?? ($_POST['name'] ?? '')), ENT_QUOTES) ?>" required>
        </label>
        <label>Description
            <textarea name="description" rows="4" required><?= htmlspecialchars((string) ($category['description'] ?? ($_POST['description'] ?? '')), ENT_QUOTES) ?></textarea>
        </label>
        <label>Status
            <select name="status">
                <?php $selectedStatus = strtolower((string) ($category['status'] ?? ($_POST['status'] ?? 'active'))); ?>
                <option value="active" <?= $selectedStatus === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $selectedStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="suspended" <?= $selectedStatus === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </label>
        <?php if ($errors !== []): ?>
            <?php
            $flatMessages = [];
            foreach ($errors as $messages) {
                foreach ((array) $messages as $message) {
                    $flatMessages[] = (string) $message;
                }
            }
            ?>
            <div class="alert alert-error">
                <?= htmlspecialchars(implode(' ', $flatMessages), ENT_QUOTES) ?>
            </div>
        <?php endif; ?>
        <div class="form-actions">
            <button type="submit" name="action" value="save" class="btn-primary"><?= htmlspecialchars($primaryActionLabel, ENT_QUOTES) ?></button>
            <?php if ($mode !== 'create'): ?>
                <button type="submit" name="action" value="suspend" class="btn-secondary">Update status</button>
            <?php endif; ?>
        </div>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
