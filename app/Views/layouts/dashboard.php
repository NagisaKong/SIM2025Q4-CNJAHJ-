<section class="card">
    <h1>Welcome, <?= htmlspecialchars($user->name ?? 'Guest', ENT_QUOTES) ?></h1>
    <p>Your role: <?= htmlspecialchars($user->profile?->role ?? 'N/A', ENT_QUOTES) ?></p>
    <div class="grid">
        <?php if ($user && $user->profile?->role === 'user_admin'): ?>
            <a class="shortcut" href="/admin/users">Manage Users</a>
            <a class="shortcut" href="/admin/profiles">Manage Profiles</a>
            <a class="shortcut" href="/admin/categories">Service Categories</a>
        <?php elseif ($user && $user->profile?->role === 'csr_rep'): ?>
            <a class="shortcut" href="/csr/requests">Browse Requests</a>
            <a class="shortcut" href="/csr/shortlist">My Shortlist</a>
            <a class="shortcut" href="/csr/history">Service History</a>
        <?php elseif ($user && $user->profile?->role === 'pin'): ?>
            <a class="shortcut" href="/pin/requests">My Requests</a>
            <a class="shortcut" href="/pin/requests/create">Create Request</a>
            <a class="shortcut" href="/pin/history">Completed Matches</a>
        <?php elseif ($user && $user->profile?->role === 'platform_manager'): ?>
            <a class="shortcut" href="/admin/categories">Service Categories</a>
            <a class="shortcut" href="/reports">Generate Reports</a>
        <?php else: ?>
            <p>Please contact an administrator for assistance.</p>
        <?php endif; ?>
    </div>
</section>
