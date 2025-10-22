<?php
require_once __DIR__ . '/../controller/LoginController.php';

use CSRPlatform\Login\Controller\LoginController;
use CSRPlatform\Shared\Entity\UserAccount;
use CSRPlatform\Shared\Utils\Validation;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$controller = new LoginController(new UserAccount(), new Validation());
$message = null;
$errors = [];
$selectedRole = $_POST['role'] ?? '';
$emailValue = $_POST['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? '');

    if ($controller->login($email, $password, $role)) {
        $user = $_SESSION['user'];
        switch ($user['role'] ?? '') {
            case 'admin':
                header('Location: /index.php?page=admin-accounts');
                break;
            case 'csr':
                header('Location: /index.php?page=csr-requests');
                break;
            case 'pin':
                header('Location: /index.php?page=pin-requests');
                break;
            case 'pm':
                header('Location: /index.php?page=pm-categories');
                break;
            default:
                header('Location: /index.php?page=login');
        }
        exit();
    }

    $errors = $controller->errors();
    if (isset($errors['credentials'])) {
        $message = implode(' ', $errors['credentials']);
    } else {
        $message = 'Please correct the highlighted errors.';
    }
}

$message = $message !== null ? trim($message) : null;
if ($message !== null && $message !== '') {
    $_SESSION['flash_error'] = $message;
    $message = null;
}

$pageTitle = 'Sign in';
$navLinks = [];
include __DIR__ . '/../../shared/boundary/header.php';
?>
<section class="card">
    <h1>Sign in</h1>
    <p class="tagline">Welcome to the CSR Match Platform. Choose your account type to continue.</p>
    <form method="POST" action="/index.php?page=login" class="form">
        <label for="role">I am logging in as
            <select id="role" name="role" required>
                <option value="" disabled <?= $selectedRole === '' ? 'selected' : '' ?>>Select an account type</option>
                <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Administrator</option>
                <option value="csr" <?= $selectedRole === 'csr' ? 'selected' : '' ?>>CSR Representative</option>
                <option value="pin" <?= $selectedRole === 'pin' ? 'selected' : '' ?>>Person in Need</option>
                <option value="pm" <?= $selectedRole === 'pm' ? 'selected' : '' ?>>Project Manager</option>
            </select>
        </label>
        <label for="email">Email
            <input id="email" type="email" name="email" value="<?= htmlspecialchars($emailValue, ENT_QUOTES) ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <span class="form-error"><?= htmlspecialchars(implode(' ', $errors['email']), ENT_QUOTES) ?></span>
            <?php endif; ?>
        </label>
        <label for="password">Password
            <input id="password" type="password" name="password" required>
            <?php if (!empty($errors['password'])): ?>
                <span class="form-error"><?= htmlspecialchars(implode(' ', $errors['password']), ENT_QUOTES) ?></span>
            <?php endif; ?>
        </label>
        <button type="submit" class="btn-primary">Sign in</button>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
