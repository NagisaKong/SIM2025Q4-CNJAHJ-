<?php
static $loginTemplateRendered = false;
if ($loginTemplateRendered) {
    return;
}
$loginTemplateRendered = true;

ob_start();

$availableRoles = $roleOptions ?? [];
$selectedRole = $selectedRole ?? '';
$emailValue = $emailValue ?? '';
?>
<section class="card">
    <h1>Sign in</h1>
    <p class="tagline">Welcome to the CSR Match Platform. Choose your account type to continue.</p>
    <form method="POST" action="/login" class="form">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
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
            <input id="email" type="email" name="email" value="<?= htmlspecialchars($emailValue, ENT_QUOTES) ?>" required>
        </label>
        <label for="password">Password
            <input id="password" type="password" name="password" required>
        </label>
        <button type="submit" class="btn-primary">Sign in</button>
    </form>
</section>
<?php $content = ob_get_clean(); include __DIR__.'/../layouts/app.php'; ?>
