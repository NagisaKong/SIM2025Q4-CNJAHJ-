<?php
require_once __DIR__ . '/../controller/createRequestController.php';
require_once __DIR__ . '/../../shared/boundary/FormValidator.php';
require_once __DIR__ . '/../../shared/entity/serviceCategories.php';

use CSRPlatform\PIN\Controller\createRequestController;
use CSRPlatform\Shared\Boundary\FormValidator;
use CSRPlatform\Shared\Entity\Request;
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
if (!$currentUser || strtolower((string) ($currentUser['role'] ?? '')) !== 'pin') {
    header('Location: /index.php?page=login');
    exit();
}

$requestEntity = new Request();
$validator = new FormValidator();
$createController = new createRequestController($requestEntity, $validator);
$categoriesEntity = new ServiceCategories();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = isset($_POST['service_id']) ? (int) $_POST['service_id'] : (isset($_POST['type']) ? (int) $_POST['type'] : 0);
    $additionalDetails = isset($_POST['additional_details'])
        ? (string) $_POST['additional_details']
        : (string) ($_POST['additionalDetails'] ?? '');

    if ($createController->createRequest((int) $currentUser['id'], $serviceId, $additionalDetails)) {
        $_SESSION['flash_success'] = 'Request created successfully.';
        header('Location: /index.php?page=pin-requests');
        exit();
    }

    $errors = $createController->errors();
    if ($errors !== []) {
        $_SESSION['flash_error'] = implode(' ', array_map(static fn($messages) => implode(' ', (array) $messages), $errors));
    } else {
        $_SESSION['flash_error'] = 'We could not create your request.';
    }
}

$categories = $categoriesEntity->listCategories('active');

$pageTitle = 'Create request';
$navLinks = [
    ['href' => '/index.php?page=dashboard', 'label' => 'Dashboard'],
    ['href' => '/index.php?page=pin-requests', 'label' => 'My Requests'],
    ['href' => '/index.php?page=pin-history', 'label' => 'History'],
];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <div class="card-header">
        <div>
            <h1>Create a new request</h1>
            <p>Select the service you need and provide supporting details.</p>
        </div>
        <a href="/index.php?page=pin-requests" class="btn-secondary">Back to requests</a>
    </div>
    <form method="POST" class="form-grid">
        <label>Type
            <select name="type" required>
                <option value="">Select a type</option>
                <?php foreach ($categories as $category): ?>
                    <?php $selected = isset($_POST['service_id']) ? (int) $_POST['service_id'] === (int) $category['id'] : (isset($_POST['type']) && (int) $_POST['type'] === (int) $category['id']); ?>
                    <option value="<?= (int) $category['id'] ?>" <?= $selected ? 'selected' : '' ?>><?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Additional details
            <textarea name="additionalDetails" rows="6" placeholder="Describe the support you need" required><?= isset($_POST['additional_details']) ? htmlspecialchars((string) $_POST['additional_details'], ENT_QUOTES) : (isset($_POST['additionalDetails']) ? htmlspecialchars((string) $_POST['additionalDetails'], ENT_QUOTES) : '') ?></textarea>
        </label>
        <button type="submit" class="btn-primary">Create request</button>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
