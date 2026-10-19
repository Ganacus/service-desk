<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

require_admin();
$id = (int) ($_GET['id'] ?? 0);

$load = db()->prepare(
    'SELECT r.*, u.name AS citizen_name FROM requests r JOIN users u ON u.id = r.user_id WHERE r.id = ?'
);
$load->execute([$id]);
$request = $load->fetch();
if (!$request) {
    http_response_code(404);
    exit('Request not found.');
}

/* ---------- Handle status update ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $newStatus = (string) ($_POST['status'] ?? '');
    $note      = trim((string) ($_POST['note'] ?? ''));

    if (!in_array($newStatus, STATUSES, true)) {
        flash('error', 'Invalid status selected.');
    } elseif (mb_strlen($note) > 1000) {
        flash('error', 'Note is too long (max 1000 characters).');
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE requests SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')
            ->execute([$newStatus, $id]);
        $pdo->prepare('INSERT INTO request_updates (request_id, status, note) VALUES (?, ?, ?)')
            ->execute([$id, $newStatus, $note !== '' ? $note : null]);
        $pdo->commit();
        flash('success', 'Request #' . $id . ' updated to "' . $newStatus . '".');
    }
    redirect('admin/view.php?id=' . $id);
}

$updates = db()->prepare('SELECT * FROM request_updates WHERE request_id = ? ORDER BY created_at DESC, id DESC');
$updates->execute([$id]);
$updates = $updates->fetchAll();

$title = 'Manage Request #' . $id;
require __DIR__ . '/../../src/views/header.php';
require __DIR__ . '/../../src/views/request_detail.php';
?>
<div class="card form-card form-wide">
    <h3>Update status</h3>
    <form method="post">
        <?= csrf_field() ?>
        <label for="status">Status</label>
        <select id="status" name="status">
            <?php foreach (STATUSES as $s): ?>
                <option value="<?= e($s) ?>" <?= $request['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="note">Note to citizen <small>(optional)</small></label>
        <textarea id="note" name="note" rows="3" maxlength="1000" placeholder="e.g. Crew dispatched, expected fix within 2 days"></textarea>

        <button type="submit" class="btn">Save update</button>
    </form>
</div>
<p><a href="<?= e(url('admin/index.php')) ?>">&larr; Back to dashboard</a></p>
<?php require __DIR__ . '/../../src/views/footer.php'; ?>
