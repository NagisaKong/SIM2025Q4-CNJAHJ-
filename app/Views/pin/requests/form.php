<section class="card">
    <h1><?= htmlspecialchars($title, ENT_QUOTES) ?></h1>
    <form method="POST" action="<?= isset($requestItem) ? '/pin/requests/' . $requestItem->id : '/pin/requests' ?>" class="form">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
        <label>Title
            <input type="text" name="title" value="<?= htmlspecialchars($requestItem->title ?? '', ENT_QUOTES) ?>" required>
        </label>
        <label>Description
            <textarea name="description" required><?= htmlspecialchars($requestItem->description ?? '', ENT_QUOTES) ?></textarea>
        </label>
        <label>Location
            <input type="text" name="location" value="<?= htmlspecialchars($requestItem->location ?? '', ENT_QUOTES) ?>" required>
        </label>
        <label>Requested Date
            <input type="date" name="requested_date" value="<?= htmlspecialchars($requestItem->requested_date ?? '', ENT_QUOTES) ?>" required>
        </label>
        <label>Category
            <select name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category->id ?>" <?= isset($requestItem) && $requestItem->category_id === $category->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category->name, ENT_QUOTES) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="open" <?= isset($requestItem) && $requestItem->status === 'open' ? 'selected' : '' ?>>Open</option>
                <option value="in_progress" <?= isset($requestItem) && $requestItem->status === 'in_progress' ? 'selected' : '' ?>>In progress</option>
                <option value="completed" <?= isset($requestItem) && $requestItem->status === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= isset($requestItem) && $requestItem->status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </label>
        <button type="submit" class="btn-primary">Save</button>
        <a href="/pin/requests" class="btn-secondary">Cancel</a>
    </form>
</section>
