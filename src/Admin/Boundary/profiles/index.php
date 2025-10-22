<?php ob_start(); ?>
<section class="card">
    <div class="card-header">
        <h1>User Profiles</h1>
        <a class="btn-primary" href="/admin/profiles/create">New Profile</a>
    </div>
    <form method="GET" action="/admin/profiles" class="form-inline">
        <input
            id="profile-search"
            type="search"
            name="q"
            placeholder="Search profiles"
            aria-label="Search profiles"
            value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES) ?>"
        >
        <button type="submit" class="btn-primary">Search</button>
        <?php if (!empty($filters['q'])): ?>
            <a href="/admin/profiles" class="btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Profile</th>
                <th>Information</th>
                <th>Permissions</th>
                <th class="status-col">Status</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($profileDetails ?? [])): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No profiles found.</td>
                </tr>
            <?php endif; ?>
            <?php foreach (($profileDetails ?? []) as $detail): ?>
                <?php $profile = $detail['profile']; ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($detail['label'], ENT_QUOTES) ?></strong>
                        <div><small>Identifier: <?= htmlspecialchars($detail['role'], ENT_QUOTES) ?></small></div>
                    </td>
                    <td><?= htmlspecialchars($detail['information'], ENT_QUOTES) ?></td>
                    <td>
                        <?php if ($detail['permissions'] === []): ?>
                            <em>No permissions assigned</em>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($detail['permissions'] as $permission): ?>
                                    <li><?= htmlspecialchars($permission, ENT_QUOTES) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </td>
                    <td class="status-col"><span class="tag tag-<?= $profile->status ?>"><?= htmlspecialchars($detail['status'], ENT_QUOTES) ?></span></td>
                    <td class="actions">
                        <a href="/admin/profiles/<?= $profile->id ?>">View</a>
                        <a href="/admin/profiles/<?= $profile->id ?>/edit">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php $content = ob_get_clean(); include dirname(__DIR__, 3) . '/Shared/Boundary/layouts/app.php'; ?>
