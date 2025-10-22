<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/bootstrap.php';
require_once __DIR__ . '/../controller/LoginController.php';
include __DIR__ . '/../../shared/boundary/header.php';

function logout(): void
{
    session_destroy();
    header('Location: /public/index.php?route=login');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

$availableRoles = [
    'user_admin' => 'User Administrator',
    'csr_rep' => 'CSR Representative',
    'pin' => 'Person In Need',
    'platform_manager' => 'Platform Manager',
];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

$controller = new LoginController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $_SESSION['login_error'] = 'Invalid request token. Please try again.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = (string) ($_POST['role'] ?? '');
        if ($controller->authenticate($email, $password, $role)) {
            header('Location: /public/index.php?route=dashboard');
            exit();
        }
    }
}

$selectedRole = $_POST['role'] ?? '';
$emailValue = $_POST['email'] ?? '';
?>
<section class="card">
    <h1>Sign in</h1>
    <p class="tagline">Welcome to the CSR Match Platform. Choose your account type to continue.</p>
    <?php if (!empty($_SESSION['login_error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['login_error'], ENT_QUOTES) ?></div>
        <?php unset($_SESSION['login_error']); ?>
    <?php endif; ?>
    <form method="POST" action="/public/index.php?route=login" class="form">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
        <label for="role">I am logging in as
            <select id="role" name="role" required>
                <option value="" disabled <?= $selectedRole === '' ? 'selected' : '' ?>>Select an account type</option>
                <?php foreach ($availableRoles as $roleKey => $roleLabel): ?>
                    <option value="<?= htmlspecialchars((string) $roleKey, ENT_QUOTES) ?>" <?= $selectedRole === $roleKey ? 'selected' : '' ?>>
                        <?= htmlspecialchars($roleLabel, ENT_QUOTES) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label for="email">Email
            <input id="email" type="email" name="email" value="<?= htmlspecialchars((string) $emailValue, ENT_QUOTES) ?>" required>
        </label>
        <label for="password">Password
            <input id="password" type="password" name="password" required>
        </label>
        <button type="submit" class="btn-primary">Sign in</button>
    </form>
</section>
<?php include __DIR__ . '/../../shared/boundary/footer.php'; ?>
