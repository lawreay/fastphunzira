<?php

use App\Support\Csrf;

$videoUrl = trim((string) ($lesson['video_url'] ?? ''));
$youtubeId = null;

if ($videoUrl !== '') {
    $parsed = parse_url($videoUrl);
    $host = strtolower((string) ($parsed['host'] ?? ''));

    if (str_contains($host, 'youtube.com')) {
        parse_str((string) ($parsed['query'] ?? ''), $query);
        $youtubeId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($query['v'] ?? '')) ?: null;
    } elseif (str_contains($host, 'youtu.be')) {
        $youtubeId = preg_replace('/[^A-Za-z0-9_-]/', '', trim((string) ($parsed['path'] ?? ''), '/')) ?: null;
    }
}
?>
<section class="lesson-page">
    <div class="lesson-topbar">
        <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0) . '/learn'), ENT_QUOTES, 'UTF-8') ?>">← Back to course</a>
        <span class="status-badge">Lesson</span>
    </div>

    <div class="lesson-layout">
        <article class="lesson-content-card">
            <div class="lesson-heading">
                <span class="eyebrow">Learning</span>
                <h1><?= htmlspecialchars((string) ($lesson['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
                <?php if (!empty($lesson['summary'])): ?>
                    <p><?= htmlspecialchars((string) $lesson['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <?php if ($youtubeId !== null): ?>
                <div class="video-shell youtube-shell">
                    <iframe
                        src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($youtubeId, ENT_QUOTES, 'UTF-8') ?>?rel=0&modestbranding=1"
                        title="<?= htmlspecialchars((string) ($lesson['title'] ?? 'Lesson video'), ENT_QUOTES, 'UTF-8') ?>"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                </div>
            <?php elseif ($videoUrl !== ''): ?>
                <div class="video-shell">
                    <video
                        controls
                        controlsList="nodownload noplaybackrate"
                        disablePictureInPicture
                        preload="metadata"
                        oncontextmenu="return false"
                        playsinline>
                        <source src="<?= htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') ?>">
                        Your browser does not support HTML5 video.
                    </video>
                </div>
                <p class="video-protection-note">Download controls are disabled in the player. This is a playback deterrent, not DRM, because browsers cannot guarantee that streamed media cannot be copied.</p>
            <?php endif; ?>

            <div class="lesson-body">
                <?= nl2br(htmlspecialchars((string) ($lesson['content'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>
            </div>

            <?php if (!empty($completed)): ?>
                <div class="alert success">Lesson completed.</div>
            <?php else: ?>
                <form method="POST" action="<?= htmlspecialchars(base_url('lessons/' . (int) ($lesson['id'] ?? 0) . '/complete'), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button class="btn" type="submit">Mark lesson complete</button>
                </form>
            <?php endif; ?>
        </article>

        <aside class="lesson-sidebar">
            <span class="eyebrow">Course</span>
            <h2><?= htmlspecialchars((string) ($course['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
            <p>Complete the lesson, then continue through the course sequence.</p>
            <a href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0) . '/learn'), ENT_QUOTES, 'UTF-8') ?>">View course lessons →</a>
        </aside>
    </div>
</section>
