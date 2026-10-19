<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
if ($user['role'] === 'admin') {
    redirect('admin/index.php');
}

$stmt = db()->prepare('SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC, id DESC');
$stmt->execute([$user['id']]);
$requests = $stmt->fetchAll();

$counts = array_fill_keys(STATUSES, 0);
foreach ($requests as $r) {
    $counts[$r['status']]++;
}

$title = 'My Requests';
require __DIR__ . '/../src/views/header.php';
?>
<div class="page-head">
    <h2>My Requests</h2>
    <a class="btn" href="<?= e(url('submit.php')) ?>">+ New Request</a>
</div>

<div class="stats">
    <?php foreach ($counts as $status => $n): ?>
        <div class="stat"><span class="stat-number"><?= $n ?></span><span class="stat-label"><?= e($status) ?></span></div>
    <?php endforeach; ?>
</div>

<div class="card">
<?php if (!$requests): ?>
    <p class="muted">You have not submitted any requests yet. <a href="<?= e(url('submit.php')) ?>">Submit your first one.</a></p>
<?php else: ?>
    <div class="table-wrap">
    <table>
        <thead><tr><th>#</th><th>Category</th><th>Location</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($requests as $r): ?>
            <tr>
                <td><?= (int) $r['id'] ?></td>
                <td><?= e($r['category']) ?></td>
                <td><?= e($r['location']) ?></td>
                <td><span class="<?= e(status_class($r['status'])) ?>"><?= e($r['status']) ?></span></td>
                <td><?= e(format_date($r['created_at'])) ?></td>
                <td><a href="<?= e(url('request.php?id=' . (int) $r['id'])) ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
