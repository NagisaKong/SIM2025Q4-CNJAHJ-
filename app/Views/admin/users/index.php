<?php ob_start(); ?>
<section class="card">
    <div class="card-header">
        <h1>User Accounts</h1>
        <a class="btn-primary" href="/admin/users/create">New User</a>
    </div>
    <form class="form-inline" method="GET">
        <input type="text" name="q" placeholder="Search email or name" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Search</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Role</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user->name, ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($user->email, ENT_QUOTES) ?></td>
                    <td><span class="tag tag-<?= $user->status ?>"><?= htmlspecialchars($user->status, ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars($user->profile?->role ?? 'N/A', ENT_QUOTES) ?></td>
                    <td class="actions">
                        <a href="/admin/users/<?= $user->id ?>">View</a>
                        <a href="/admin/users/<?= $user->id ?>/edit">Edit</a>
                        <form method="POST" action="/admin/users/<?= $user->id ?>/suspend">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
                            <button type="submit" class="link">Suspend</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php $content = ob_get_clean(); include __DIR__.'/../../layouts/app.php'; ?>
