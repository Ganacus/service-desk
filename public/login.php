<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

if (current_user() !== null) {
    redirect('index.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT id, password_hash, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if ($row && password_verify($password, $row['password_hash'])) {
        login_user((int) $row['id']);
        redirect($row['role'] === 'admin' ? 'admin/index.php' : 'dashboard.php');
    }
    // Same message for unknown email and wrong password (avoids user enumeration).
    $error = 'Invalid email or password.';
}

$title = 'Login';
require __DIR__ . '/../src/views/header.php';
?>
<div class="card form-card">
    <h2>Log in</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" novalidate>
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn btn-block">Log in</button>
    </form>
    <p class="muted">No account yet? <a href="<?= e(url('register.php')) ?>">Register</a></p>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
