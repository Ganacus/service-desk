<?php
/**
 * Shared request detail + timeline block.
 * Expects: $request (array), $updates (array).
 */
$steps = STATUSES;
$currentIndex = array_search($request['status'], $steps, true);
?>
<div class="page-head">
    <h2>Request #<?= (int) $request['id'] ?></h2>
    <span class="<?= e(status_class($request['status'])) ?>"><?= e($request['status']) ?></span>
</div>

<ol class="progress">
    <?php foreach ($steps as $i => $step): ?>
        <li class="<?= $i <= $currentIndex ? 'done' : '' ?>"><?= e($step) ?></li>
    <?php endforeach; ?>
</ol>

<div class="card">
    <dl class="details">
        <dt>Category</dt><dd><?= e($request['category']) ?></dd>
        <dt>Location</dt><dd><?= e($request['location']) ?></dd>
        <dt>Submitted by</dt><dd><?= e($request['citizen_name']) ?></dd>
        <dt>Submitted</dt><dd><?= e(format_date($request['created_at'])) ?></dd>
        <dt>Last updated</dt><dd><?= e(format_date($request['updated_at'])) ?></dd>
        <dt>Description</dt><dd><?= nl2br(e($request['description'])) ?></dd>
    </dl>
    <?php if (!empty($request['image_path'])): ?>
        <img class="request-image" src="<?= e(url('uploads/' . $request['image_path'])) ?>" alt="Photo attached to request #<?= (int) $request['id'] ?>">
    <?php endif; ?>
</div>

<div class="card">
    <h3>Update history</h3>
    <ul class="timeline">
        <?php foreach ($updates as $u): ?>
            <li>
                <span class="<?= e(status_class($u['status'])) ?>"><?= e($u['status']) ?></span>
                <span class="muted"><?= e(format_date($u['created_at'])) ?></span>
                <?php if (!empty($u['note'])): ?><p><?= nl2br(e($u['note'])) ?></p><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
