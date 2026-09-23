<?php

use App\Core\Auth;
?>
<section class="card">
    <h1>Course Catalogue</h1>
    <p>Browse current learning tracks and course offerings.</p>

    <div class="course-grid">
        <?php foreach ($courses as $course): ?>
            <article class="course-card">
                <span class="status-badge"><?= htmlspecialchars((string) ($course['status'] ?? 'draft'), ENT_QUOTES, 'UTF-8') ?></span>
                <h3><?= htmlspecialchars((string) ($course['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars((string) ($course['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                <a class="btn secondary" href="/courses/<?= (int) ($course['id'] ?? 0) ?>">View details</a>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (Auth::userCan('courses.manage')): ?>
        <p style="margin-top: 20px;"><a class="btn" href="/admin/courses">Manage courses</a></p>
    <?php endif; ?>
</section>
