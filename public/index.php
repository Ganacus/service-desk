<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

// Logged-in users go straight to their dashboard.
$user = current_user();
if ($user !== null) {
    redirect($user['role'] === 'admin' ? 'admin/index.php' : 'dashboard.php');
}

$title = 'Welcome';
require __DIR__ . '/../src/views/header.php';
?>
<section class="hero">
    <h1>Report a problem. Track it to resolution.</h1>
    <p>Broken streetlight? Water issue? Uncollected garbage? Submit a service request and follow its progress from
        <strong>Pending</strong> to <strong>In Progress</strong> to <strong>Resolved</strong>.</p>
    <p>
        <a class="btn" href="<?= e(url('register.php')) ?>">Create an account</a>
        <a class="btn btn-outline" href="<?= e(url('login.php')) ?>">Log in</a>
    </p>
</section>
<section class="grid-3">
    <div class="card"><h3>1. Submit</h3><p>Choose a category, add the location and a short description. Attach a photo if you can.</p></div>
    <div class="card"><h3>2. Track</h3><p>See the live status of every request you have submitted, with a full update history.</p></div>
    <div class="card"><h3>3. Resolved</h3><p>Administrators review, act on and close requests, leaving notes along the way.</p></div>
</section>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
