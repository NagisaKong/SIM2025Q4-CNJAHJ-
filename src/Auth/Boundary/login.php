<?php ob_start(); ?>
<?php
$availableRoles = $roleOptions ?? [];
$selectedRole = $selectedRole ?? '';
$emailValue = $emailValue ?? '';
$errorMessage = $flash_error ?? '';
$warningMessage = $flash_warning ?? '';
$flash_error = null;
$flash_warning = null;
?>
<section class="card">
    <h1>Sign in</h1>
    <p class="tagline">Welcome to the CSR Match Platform. Choose your account type to continue.</p>
    <div class="alert alert-danger" id="login-error" style="<?= $errorMessage === '' ? 'display:none;' : '' ?>">
        <span id="login-error-message"><?= htmlspecialchars($errorMessage, ENT_QUOTES) ?></span>
    </div>
    <?php if ($warningMessage !== ''): ?>
        <div class="alert alert-warning">
            <?= htmlspecialchars($warningMessage, ENT_QUOTES) ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="/login" class="form" novalidate>
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
<script>
    (function () {
        const form = document.querySelector('.form');
        const errorContainer = document.getElementById('login-error');
        const errorMessageBox = document.getElementById('login-error-message');

        if (!form || !errorContainer || !errorMessageBox) {
            return;
        }

        const showError = (message) => {
            errorContainer.style.display = '';
            errorMessageBox.textContent = message;
        };

        form.addEventListener('submit', function (event) {
            const roleField = form.querySelector('[name="role"]');
            const emailField = form.querySelector('[name="email"]');
            const passwordField = form.querySelector('[name="password"]');

            const missingFields = [];
            if (!roleField.value) {
                missingFields.push('account type');
            }
            if (!emailField.value) {
                missingFields.push('email');
            }
            if (!passwordField.value) {
                missingFields.push('password');
            }

            if (missingFields.length > 0) {
                event.preventDefault();
                if (missingFields.length === 1) {
                    showError('Please provide your ' + missingFields[0] + '.');
                } else {
                    showError('All fields must be filled out before continuing.');
                }
                return;
            }

            const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,20}$/;
            if (!passwordPattern.test(passwordField.value)) {
                event.preventDefault();
                showError('Password must be 8-20 characters long and include both letters and numbers.');
            }
        });
    })();
</script>
<?php $content = ob_get_clean(); include dirname(__DIR__, 2) . '/Shared/Boundary/layouts/app.php'; ?>
