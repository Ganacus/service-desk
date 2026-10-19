<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

if (current_user() !== null) {
    redirect('index.php');
}

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name     = trim((string) ($_POST['name'] ?? ''));
    $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['password_confirm'] ?? '');
    $old = ['name' => $name, 'email' => $email];

    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = 'Please enter your name (max 100 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $check = db()->prepare('SELECT 1 FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetchColumn()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (!$errors) {
        $insert = db()->prepare(
            "INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'citizen')"
        );
        $insert->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        login_user((int) db()->lastInsertId());
        flash('success', 'Welcome, ' . $name . '! Your account has been created.');
        redirect('dashboard.php');
    }
}

$title = 'Register';
require __DIR__ . '/../src/views/header.php';
?>
<div class="card form-card">
    <h2>Create your account</h2>
    <?php foreach ($errors as $error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>
    <form method="post" novalidate>
        <?= csrf_field() ?>
        <label for="name">Full name</label>
        <input type="text" id="name" name="name" value="<?= e($old['name']) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" required>

        <label for="password">Password <small>(min. 8 characters)</small></label>
        <input type="password" id="password" name="password" required>

        <label for="password_confirm">Confirm password</label>
        <input type="password" id="password_confirm" name="password_confirm" required>

        <button type="submit" class="btn btn-block">Register</button>
    </form>
    <p class="muted">Already registered? <a href="<?= e(url('login.php')) ?>">Log in</a></p>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
