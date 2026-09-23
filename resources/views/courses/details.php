<?php

use App\Core\Auth;
use App\Support\Csrf;
?>
<section class="card">
    <h1><?= htmlspecialchars((string) ($course['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <span class="status-badge"><?= htmlspecialchars((string) ($course['status'] ?? 'draft'), ENT_QUOTES, 'UTF-8') ?></span>
    <p><?= htmlspecialchars((string) ($course['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

    <?php if (Auth::check()): ?>
        <form method="POST" action="/courses/<?= (int) ($course['id'] ?? 0) ?>/enroll">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
            <button class="btn" type="submit">Enroll in this course</button>
        </form>
    <?php else: ?>
        <p><a href="/login">Log in</a> to enroll in this course.</p>
    <?php endif; ?>

    <p style="margin-top: 18px;"><a class="btn secondary" href="/courses">Back to catalogue</a></p>
</section>
