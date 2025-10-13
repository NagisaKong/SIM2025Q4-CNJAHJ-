<section class="card">
    <h1><?= htmlspecialchars($title, ENT_QUOTES) ?></h1>
    <form method="POST" action="<?= isset($category) ? '/admin/categories/' . $category->id : '/admin/categories' ?>" class="form">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
        <label>Name
            <input type="text" name="name" value="<?= htmlspecialchars($category->name ?? '', ENT_QUOTES) ?>" required>
        </label>
        <label>Status
            <select name="status">
                <option value="active" <?= isset($category) && $category->status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= isset($category) && $category->status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </label>
        <button type="submit" class="btn-primary">Save</button>
        <a href="/admin/categories" class="btn-secondary">Cancel</a>
    </form>
</section>
