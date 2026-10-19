<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_admin();

/* ---------- Analytics ---------- */
$counts = array_fill_keys(STATUSES, 0);
foreach (db()->query('SELECT status, COUNT(*) AS n FROM requests GROUP BY status') as $row) {
    $counts[$row['status']] = (int) $row['n'];
}
$total    = array_sum($counts);
$resolved = $counts['Resolved'];
$open     = $total - $resolved;
$rate     = $total > 0 ? (int) round($resolved / $total * 100) : 0;

$byCategory = db()->query(
    'SELECT category, COUNT(*) AS n FROM requests GROUP BY category ORDER BY n DESC'
)->fetchAll();
$maxCategory = $byCategory ? max(array_column($byCategory, 'n')) : 1;

/* ---------- Filters ---------- */
$filterStatus   = (string) ($_GET['status'] ?? '');
$filterCategory = (string) ($_GET['category'] ?? '');
$search         = trim((string) ($_GET['q'] ?? ''));

$where  = [];
$params = [];
if (in_array($filterStatus, STATUSES, true)) {
    $where[]  = 'r.status = ?';
    $params[] = $filterStatus;
}
if (in_array($filterCategory, CATEGORIES, true)) {
    $where[]  = 'r.category = ?';
    $params[] = $filterCategory;
}
if ($search !== '') {
    $where[]  = '(r.location LIKE ? OR r.description LIKE ? OR u.name LIKE ?)';
    $like     = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}

$sql = 'SELECT r.*, u.name AS citizen_name FROM requests r JOIN users u ON u.id = r.user_id'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . ' ORDER BY r.created_at DESC, r.id DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

$title = 'Admin Dashboard';
require __DIR__ . '/../../src/views/header.php';
?>
<div class="page-head"><h2>Admin Dashboard</h2></div>

<div class="stats">
    <div class="stat"><span class="stat-number"><?= $total ?></span><span class="stat-label">Total requests</span></div>
    <div class="stat stat-open"><span class="stat-number"><?= $open ?></span><span class="stat-label">Open</span></div>
    <div class="stat stat-resolved"><span class="stat-number"><?= $resolved ?></span><span class="stat-label">Resolved</span></div>
    <div class="stat"><span class="stat-number"><?= $rate ?>%</span><span class="stat-label">Resolution rate</span></div>
</div>

<div class="grid-2">
    <div class="card">
        <h3>By status</h3>
        <?php foreach ($counts as $status => $n): ?>
            <div class="bar-row">
                <span class="bar-label"><?= e($status) ?></span>
                <div class="bar"><div class="bar-fill bar-<?= e(strtolower(str_replace(' ', '-', $status))) ?>"
                     style="width: <?= $total > 0 ? (int) round($n / $total * 100) : 0 ?>%"></div></div>
                <span class="bar-value"><?= $n ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="card">
        <h3>By category</h3>
        <?php if (!$byCategory): ?>
            <p class="muted">No data yet.</p>
        <?php endif; ?>
        <?php foreach ($byCategory as $row): ?>
            <div class="bar-row">
                <span class="bar-label"><?= e($row['category']) ?></span>
                <div class="bar"><div class="bar-fill" style="width: <?= (int) round($row['n'] / $maxCategory * 100) ?>%"></div></div>
                <span class="bar-value"><?= (int) $row['n'] ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <h3>All requests</h3>
    <form method="get" class="filters">
        <input type="search" name="q" placeholder="Search location, description or citizen" value="<?= e($search) ?>">
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach (STATUSES as $s): ?>
                <option value="<?= e($s) ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="category">
            <option value="">All categories</option>
            <?php foreach (CATEGORIES as $c): ?>
                <option value="<?= e($c) ?>" <?= $filterCategory === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-small">Filter</button>
        <a class="btn btn-small btn-outline" href="<?= e(url('admin/index.php')) ?>">Reset</a>
    </form>

    <?php if (!$requests): ?>
        <p class="muted">No requests match your filters.</p>
    <?php else: ?>
    <div class="table-wrap">
    <table>
        <thead><tr><th>#</th><th>Citizen</th><th>Category</th><th>Location</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($requests as $r): ?>
            <tr>
                <td><?= (int) $r['id'] ?></td>
                <td><?= e($r['citizen_name']) ?></td>
                <td><?= e($r['category']) ?></td>
                <td><?= e($r['location']) ?></td>
                <td><span class="<?= e(status_class($r['status'])) ?>"><?= e($r['status']) ?></span></td>
                <td><?= e(format_date($r['created_at'])) ?></td>
                <td><a href="<?= e(url('admin/view.php?id=' . (int) $r['id'])) ?>">Manage</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../../src/views/footer.php'; ?>
