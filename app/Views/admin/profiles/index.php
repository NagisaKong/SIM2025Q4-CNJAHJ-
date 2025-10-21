<?php ob_start(); ?>
<section class="card">
    <div class="card-header">
        <h1>User Profiles</h1>
        <a class="btn-primary" href="/admin/profiles/create">New Profile</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Role</th>
                <th>Description</th>
                <th class="status-col">Status</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($profiles as $profile): ?>
                <tr>
                    <td><?= htmlspecialchars($profile->role, ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($profile->description, ENT_QUOTES) ?></td>
                    <td class="status-col"><span class="tag tag-<?= $profile->status ?>"><?= htmlspecialchars($profile->status, ENT_QUOTES) ?></span></td>
                    <td class="actions">
                        <a href="/admin/profiles/<?= $profile->id ?>">View</a>
                        <a href="/admin/profiles/<?= $profile->id ?>/edit">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php $content = ob_get_clean(); include __DIR__.'/../../layouts/app.php'; ?>
