<?php ob_start(); ?>
<section class="card">
    <h1><?= htmlspecialchars($title, ENT_QUOTES) ?></h1>
    <form method="POST" action="<?= isset($profile) ? '/admin/profiles/' . $profile->id : '/admin/profiles' ?>" class="form">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
        <label>Role
            <input type="text" name="role" value="<?= htmlspecialchars($profile->role ?? '', ENT_QUOTES) ?>" required>
        </label>
        <label>Description
            <textarea name="description" required><?= htmlspecialchars($profile->description ?? '', ENT_QUOTES) ?></textarea>
        </label>
        <label>Status
            <select name="status">
                <option value="active" <?= isset($profile) && $profile->status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= isset($profile) && $profile->status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </label>
        <button type="submit" class="btn-primary">Save</button>
        <a href="/admin/profiles" class="btn-secondary">Cancel</a>
    </form>
</section>
<?php $content = ob_get_clean(); include dirname(__DIR__, 3) . '/Shared/Boundary/layouts/app.php'; ?>
