<?php ob_start(); ?>
<?php $detail = $profileDetail ?? ['profile' => $profile, 'label' => $profile->role, 'role' => $profile->role, 'information' => $profile->description, 'permissions' => [], 'status' => $profile->status]; ?>
<section class="card">
    <h1><?= htmlspecialchars($detail['label'], ENT_QUOTES) ?></h1>
    <p><small>Identifier: <?= htmlspecialchars($detail['role'], ENT_QUOTES) ?></small></p>
    <dl class="definition-list">
        <dt>Information</dt><dd><?= htmlspecialchars($detail['information'], ENT_QUOTES) ?></dd>
        <dt>Status</dt><dd><?= htmlspecialchars($detail['status'], ENT_QUOTES) ?></dd>
        <dt>Created</dt><dd><?= htmlspecialchars($detail['profile']->created_at, ENT_QUOTES) ?></dd>
        <dt>Last Updated</dt><dd><?= htmlspecialchars($detail['profile']->updated_at, ENT_QUOTES) ?></dd>
    </dl>
    <h2>Permissions</h2>
    <?php if ($detail['permissions'] === []): ?>
        <p><em>No permissions assigned to this profile.</em></p>
    <?php else: ?>
        <ul>
            <?php foreach ($detail['permissions'] as $permission): ?>
                <li><?= htmlspecialchars($permission, ENT_QUOTES) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <a href="/admin/profiles" class="btn-secondary">Back</a>
</section>
<?php $content = ob_get_clean(); include __DIR__.'/../../layouts/app.php'; ?>
