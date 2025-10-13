<?php ob_start(); ?>
<section class="card">
    <div class="card-header">
        <h1>Service Categories</h1>
        <a class="btn-primary" href="/admin/categories/create">New Category</a>
    </div>
    <form class="form-inline" method="GET">
        <input type="text" name="q" placeholder="Search" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES) ?>">
        <select name="status">
            <option value="">All statuses</option>
            <option value="active" <?= (($_GET['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
            <option value="suspended" <?= (($_GET['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Suspended</option>
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars($category->name, ENT_QUOTES) ?></td>
                    <td><span class="tag tag-<?= $category->status ?>"><?= htmlspecialchars($category->status, ENT_QUOTES) ?></span></td>
                    <td class="actions">
                        <a href="/admin/categories/<?= $category->id ?>">View</a>
                        <a href="/admin/categories/<?= $category->id ?>/edit">Edit</a>
                        <form method="POST" action="/admin/categories/<?= $category->id ?>/delete">
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
