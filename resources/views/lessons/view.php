<?php

use App\Support\Csrf;

$courseId = (int) ($course['id'] ?? 0);
$lessonId = (int) ($lesson['id'] ?? 0);
$lessonTitle = trim((string) ($lesson['title'] ?? 'Lesson'));
$lessonSummary = trim((string) ($lesson['summary'] ?? ''));
$lessonContent = (string) ($lesson['content'] ?? '');
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
<section class="lesson-workspace">
    <div class="lesson-breadcrumb">
        <a href="<?= htmlspecialchars(base_url('courses/' . $courseId), ENT_QUOTES, 'UTF-8') ?>">Course</a>
        <span aria-hidden="true">/</span>
        <a href="<?= htmlspecialchars(base_url('courses/' . $courseId . '/learn'), ENT_QUOTES, 'UTF-8') ?>">Lessons</a>
        <span aria-hidden="true">/</span>
        <strong><?= htmlspecialchars($lessonTitle, ENT_QUOTES, 'UTF-8') ?></strong>
    </div>

    <div class="lesson-workspace-header">
        <div>
            <span class="eyebrow">Learning workspace</span>
            <h1><?= htmlspecialchars($lessonTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($lessonSummary !== ''): ?>
                <p><?= htmlspecialchars($lessonSummary, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
        <a class="btn secondary lesson-back-btn" href="<?= htmlspecialchars(base_url('courses/' . $courseId . '/learn'), ENT_QUOTES, 'UTF-8') ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>
            Course lessons
        </a>
    </div>

    <div class="lesson-workspace-grid">
        <article class="lesson-main-card">
            <?php if ($youtubeId !== null): ?>
                <div class="lesson-media youtube-shell">
                    <iframe
                        src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($youtubeId, ENT_QUOTES, 'UTF-8') ?>?rel=0&modestbranding=1"
                        title="<?= htmlspecialchars($lessonTitle, ENT_QUOTES, 'UTF-8') ?>"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                </div>
            <?php elseif ($videoUrl !== ''): ?>
                <div class="lesson-media">
                    <video controls controlsList="nodownload noplaybackrate" disablePictureInPicture preload="metadata" oncontextmenu="return false" playsinline>
                        <source src="<?= htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') ?>">
                        Your browser does not support HTML5 video.
                    </video>
                </div>
                <p class="lesson-media-note">Download controls are disabled in the player. This is a playback deterrent, not DRM.</p>
            <?php endif; ?>

            <div class="lesson-reading">
                <span class="eyebrow">Lesson content</span>
                <div class="lesson-body">
                    <?= nl2br(htmlspecialchars($lessonContent, ENT_QUOTES, 'UTF-8')) ?>
                </div>
            </div>

            <div class="lesson-completion">
                <?php if (!empty($completed)): ?>
                    <div class="lesson-completed-state">
                        <span class="lesson-completed-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>
                        </span>
                        <div>
                            <strong>Lesson completed</strong>
                            <span>Your progress has been recorded.</span>
                        </div>
                    </div>
                    <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . $courseId . '/learn'), ENT_QUOTES, 'UTF-8') ?>">Continue course</a>
                <?php else: ?>
                    <div>
                        <span class="eyebrow">Ready to move on?</span>
                        <strong>Mark this lesson complete</strong>
                    </div>
                    <form method="POST" action="<?= htmlspecialchars(base_url('lessons/' . $lessonId . '/complete'), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                        <button class="btn" type="submit">
                            Mark complete
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </article>

        <aside class="lesson-context-card">
            <div class="lesson-context-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M5 4h12a2 2 0 0 1 2 2v14H7a2 2 0 0 0-2 2V4Zm0 16a2 2 0 0 1 2-2h12M8 8h7M8 12h5"/></svg>
            </div>
            <span class="eyebrow">Course</span>
            <h2><?= htmlspecialchars((string) ($course['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
            <p>Work through the lesson, record your progress, then return to the course sequence.</p>
            <a class="lesson-context-link" href="<?= htmlspecialchars(base_url('courses/' . $courseId . '/learn'), ENT_QUOTES, 'UTF-8') ?>">
                View all lessons
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
            </a>
        </aside>
    </div>
</section>
