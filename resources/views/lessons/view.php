<?php

use App\Support\Csrf;
?>
<section class="card">
    <h1><?= htmlspecialchars((string) ($lesson['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <p><a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0) . '/learn'), ENT_QUOTES, 'UTF-8') ?>">Back to course</a></p>

    <?php if (!empty($lesson['summary'])): ?>
        <p><strong>Summary:</strong> <?= htmlspecialchars((string) $lesson['summary'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <article>
        <?= nl2br(htmlspecialchars((string) ($lesson['content'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>
    </article>

    <?php if (!empty($lesson['video_url'])): ?>
        <p><a href="<?= htmlspecialchars((string) $lesson['video_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Watch video</a></p>
    <?php endif; ?>

    <?php if (!empty($completed)): ?>
        <div class="alert success">Lesson completed.</div>
    <?php else: ?>
        <form method="POST" action="<?= htmlspecialchars(base_url('lessons/' . (int) ($lesson['id'] ?? 0) . '/complete'), ENT_QUOTES, 'UTF-8') ?>" style="margin-top: 24px;">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
            <button class="btn" type="submit">Mark lesson complete</button>
        </form>
    <?php endif; ?>
</section>
