<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
$id = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare(
    'SELECT r.*, u.name AS citizen_name FROM requests r JOIN users u ON u.id = r.user_id WHERE r.id = ?'
);
$stmt->execute([$id]);
$request = $stmt->fetch();

// Citizens may only open their own requests; admins may open any (they use the admin view).
if (!$request || ($user['role'] !== 'admin' && (int) $request['user_id'] !== (int) $user['id'])) {
    http_response_code(404);
    exit('Request not found.');
}
if ($user['role'] === 'admin') {
    redirect('admin/view.php?id=' . $id);
}

$updates = db()->prepare('SELECT * FROM request_updates WHERE request_id = ? ORDER BY created_at DESC, id DESC');
$updates->execute([$id]);
$updates = $updates->fetchAll();

$title = 'Request #' . $id;
require __DIR__ . '/../src/views/header.php';
require __DIR__ . '/../src/views/request_detail.php';
?>
<p><a href="<?= e(url('dashboard.php')) ?>">&larr; Back to my requests</a></p>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
