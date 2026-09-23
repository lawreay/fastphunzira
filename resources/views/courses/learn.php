<?php
use App\Support\Csrf;
?>
<section class="card">
    <h1><?= htmlspecialchars((string) ($course['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars((string) ($course['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Progress:</strong> <?= (float) ($progress['percent'] ?? 0.0) ?>% (<?= (int) ($progress['completed_lessons'] ?? 0) ?>/<?= (int) ($progress['total_lessons'] ?? 0) ?> lessons)</p>

    <?php if (empty($modules)): ?>
        <p>No modules are available in this course yet.</p>
    <?php else: ?>
        <?php foreach ($modules as $entry): ?>
            <?php $module = $entry['module']; ?>
            <div style="margin-top: 24px;">
                <h2><?= htmlspecialchars((string) ($module['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars((string) ($module['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

                <?php if (empty($entry['lessons'])): ?>
                    <p>No lessons in this module yet.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($entry['lessons'] as $lesson): ?>
                            <li>
                                <a href="/lessons/<?= (int) ($lesson['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($lesson['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p style="margin-top: 24px;"><a class="btn secondary" href="/courses/<?= (int) ($course['id'] ?? 0) ?>">Back to course</a></p>
</section>
