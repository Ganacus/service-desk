<?php
/** Shared page header. Expects $title (string) to be set by the page. */
$navUser = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? APP_NAME) ?> · <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="container topbar-inner">
        <a class="brand" href="<?= e(url('index.php')) ?>">🏛️ <?= e(APP_NAME) ?></a>
        <nav>
            <?php if ($navUser): ?>
                <?php if ($navUser['role'] === 'admin'): ?>
                    <a href="<?= e(url('admin/index.php')) ?>">Admin Dashboard</a>
                <?php else: ?>
                    <a href="<?= e(url('dashboard.php')) ?>">My Requests</a>
                    <a href="<?= e(url('submit.php')) ?>">New Request</a>
                <?php endif; ?>
                <form method="post" action="<?= e(url('logout.php')) ?>" class="inline-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="link-button">Logout (<?= e($navUser['name']) ?>)</button>
                </form>
            <?php else: ?>
                <a href="<?= e(url('login.php')) ?>">Login</a>
                <a href="<?= e(url('register.php')) ?>" class="btn btn-small">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <?php foreach (flash_pull() as $msg): ?>
        <div class="alert alert-<?= e($msg['type']) ?>"><?= e($msg['message']) ?></div>
    <?php endforeach; ?>
