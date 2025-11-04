<?php
require_once __DIR__ . '/../controller/updateRequestController.php';
require_once __DIR__ . '/../controller/viewRequestController.php';
require_once __DIR__ . '/../../shared/boundary/FormValidator.php';
require_once __DIR__ . '/../../shared/entity/serviceCategories.php';

use CSRPlatform\PIN\Controller\updateRequestController;
use CSRPlatform\PIN\Controller\viewRequestController;
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

$requestId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($requestId <= 0) {
    $_SESSION['flash_error'] = 'The request you are trying to edit was not found.';
    header('Location: /index.php?page=pin-requests');
    exit();
}

$requestEntity = new Request();
$validator = new FormValidator();
$updateController = new updateRequestController($requestEntity, $validator);
$viewController = new viewRequestController($requestEntity);
$categoriesEntity = new ServiceCategories();

$requestDetails = $viewController->viewPostedRequest((int) $currentUser['id'], $requestId, false);
if (!$requestDetails) {
    $_SESSION['flash_error'] = 'The request you are trying to edit was not found.';
    header('Location: /index.php?page=pin-requests');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;
    $additionalDetails = (string) ($_POST['additional_details'] ?? '');
    $status = (string) ($_POST['status'] ?? 'open');

    if ($updateController->updateRequest((int) $currentUser['id'], $requestId, $serviceId, $additionalDetails, $status)) {
        $_SESSION['flash_success'] = 'Request updated successfully.';
        header('Location: /index.php?page=pin-requests&view=' . $requestId);
        exit();
    }

    $errors = $updateController->errors();
    if ($errors !== []) {
        $_SESSION['flash_error'] = implode(' ', array_map(static fn($messages) => implode(' ', (array) $messages), $errors));
    } else {
        $_SESSION['flash_error'] = 'We could not update your request.';
    }

    $requestDetails['category_id'] = $serviceId;
    $requestDetails['additional_details'] = $additionalDetails;
    $requestDetails['status'] = $status;
}

$categories = $categoriesEntity->listCategories('active');

$pageTitle = 'Edit request';
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
            <h1>Edit request</h1>
            <p>Update the service category or status for this request.</p>
        </div>
        <a href="/index.php?page=pin-requests&amp;view=<?= $requestId ?>" class="btn-secondary">Back to request</a>
    </div>
    <form method="POST" class="form-grid">
        <label>Service
            <select name="service_id" required>
                <option value="">Select a service</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= (int) ($requestDetails['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $category['name'], ENT_QUOTES) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Status
            <select name="status" required>
                <?php $statuses = ['open' => 'Open', 'in_progress' => 'In progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'suspended' => 'Suspended']; ?>
                <?php foreach ($statuses as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value, ENT_QUOTES) ?>" <?= strtolower((string) ($requestDetails['status'] ?? '')) === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Additional details
            <textarea name="additional_details" rows="6" required><?= htmlspecialchars((string) ($requestDetails['additional_details'] ?? $requestDetails['description']), ENT_QUOTES) ?></textarea>
        </label>
        <button type="submit" class="btn-primary">Save changes</button>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
