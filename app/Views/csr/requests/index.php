<?php ob_start(); ?>
<section class="card">
    <h1>Volunteer Opportunities</h1>
    <form class="form-inline" method="GET">
        <input type="text" name="q" placeholder="Keyword" value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES) ?>">
        <select name="category_id">
            <option value="">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category->id ?>" <?= (string)($filters['category_id'] ?? '') === (string)$category->id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category->name, ENT_QUOTES) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? '', ENT_QUOTES) ?>">
        <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? '', ENT_QUOTES) ?>">
        <button type="submit" class="btn-secondary">Filter</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Location</th>
                <th>Date</th>
                <th>Views</th>
                <th>Shortlisted</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $requestItem): ?>
                <tr>
                    <td><?= htmlspecialchars($requestItem->title, ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($requestItem->location, ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($requestItem->requested_date, ENT_QUOTES) ?></td>
                    <td><?= $requestItem->views_count ?></td>
                    <td><?= $requestItem->shortlist_count ?></td>
                    <td><a href="/csr/requests/<?= $requestItem->id ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php $content = ob_get_clean(); include __DIR__.'/../../layouts/app.php'; ?>
