<?php ob_start(); ?>
<section class="card">
    <h1><?= htmlspecialchars($title, ENT_QUOTES) ?></h1>
    <form method="POST" action="<?= isset($user) ? '/admin/users/' . $user->id : '/admin/users' ?>" class="form">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
        <label>Name
            <input type="text" name="name" value="<?= htmlspecialchars($user->name ?? '', ENT_QUOTES) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars($user->email ?? '', ENT_QUOTES) ?>" required>
        </label>
        <?php if (empty($user)): ?>
            <label>Password
                <input type="password" name="password" required>
            </label>
        <?php else: ?>
            <label>New Password (leave blank to keep current)
                <input type="password" name="password">
            </label>
        <?php endif; ?>
        <label>Profile
            <select name="profile_id" required>
                <?php foreach ($profiles as $profile): ?>
                    <option value="<?= $profile->id ?>" <?= isset($user) && $user->profile_id === $profile->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($profile->role, ENT_QUOTES) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="active" <?= isset($user) && $user->status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= isset($user) && $user->status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </label>
        <button type="submit" class="btn-primary">Save</button>
        <a href="/admin/users" class="btn-secondary">Cancel</a>
    </form>
</section>
<?php $content = ob_get_clean(); include __DIR__.'/../../layouts/app.php'; ?>
