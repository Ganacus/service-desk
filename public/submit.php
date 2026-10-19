<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
if ($user['role'] === 'admin') {
    redirect('admin/index.php');
}

$errors = [];
$old = ['category' => '', 'location' => '', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $category    = (string) ($_POST['category'] ?? '');
    $location    = trim((string) ($_POST['location'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $old = compact('category', 'location', 'description');

    if (!in_array($category, CATEGORIES, true)) {
        $errors[] = 'Please choose a valid category.';
    }
    if ($location === '' || mb_strlen($location) > 200) {
        $errors[] = 'Please enter the location (max 200 characters).';
    }
    if (mb_strlen($description) < 10 || mb_strlen($description) > 2000) {
        $errors[] = 'Description must be between 10 and 2000 characters.';
    }

    $image = null;
    if (!$errors) {
        try {
            $image = save_uploaded_image($_FILES['image'] ?? []);
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        $insert = $pdo->prepare(
            'INSERT INTO requests (user_id, category, location, description, image_path) VALUES (?, ?, ?, ?, ?)'
        );
        $insert->execute([$user['id'], $category, $location, $description, $image]);
        $requestId = (int) $pdo->lastInsertId();

        $log = $pdo->prepare('INSERT INTO request_updates (request_id, status, note) VALUES (?, ?, ?)');
        $log->execute([$requestId, 'Pending', 'Request submitted.']);
        $pdo->commit();

        flash('success', 'Your request has been submitted. Reference #' . $requestId . '.');
        redirect('request.php?id=' . $requestId);
    }
}

$title = 'New Request';
require __DIR__ . '/../src/views/header.php';
?>
<div class="card form-card form-wide">
    <h2>Submit a service request</h2>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>

        <label for="category">Category</label>
        <select id="category" name="category" required>
            <option value="">— Select a category —</option>
            <?php foreach (CATEGORIES as $cat): ?>
                <option value="<?= e($cat) ?>" <?= $old['category'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="location">Location</label>
        <input type="text" id="location" name="location" maxlength="200" value="<?= e($old['location']) ?>"
               placeholder="e.g. Ngong Road, near Prestige Plaza" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5" maxlength="2000"
                  placeholder="Describe the problem in a few sentences" required><?= e($old['description']) ?></textarea>

        <label for="image">Photo <small>(optional · JPG, PNG or WEBP · max 2 MB)</small></label>
        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">

        <button type="submit" class="btn">Submit request</button>
        <a class="btn btn-outline" href="<?= e(url('dashboard.php')) ?>">Cancel</a>
    </form>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
