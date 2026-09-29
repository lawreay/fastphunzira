<?php

use App\Support\Csrf;

$courseId = (int) ($course['id'] ?? 0);
$lessonId = (int) ($lesson['id'] ?? 0);
$lessonTitle = trim((string) ($lesson['title'] ?? 'Lesson'));
$lessonSummary = trim((string) ($lesson['summary'] ?? ''));
$lessonContent = (string) ($lesson['content'] ?? '');
$videoUrl = trim((string) ($lesson['video_url'] ?? ''));
$localVideo = trim((string) ($lesson['file_path'] ?? ''));
$progress = $progress ?? null;
$isCompleted = !empty($progress['completed']) || !empty($completed);
$completedAt = trim((string) ($progress['completed_at'] ?? ''));
$materials = $materials ?? [];
$blocks = $blocks ?? [];
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
            <?php if ($localVideo !== ''): ?>
                <div class="lesson-media">
                    <video controls controlsList="nodownload noplaybackrate" disablePictureInPicture preload="metadata" oncontextmenu="return false" playsinline>
                        <source src="<?= htmlspecialchars(base_url('lessons/' . $lessonId . '/video'), ENT_QUOTES, 'UTF-8') ?>" type="<?= htmlspecialchars((string) ($lesson['video_mime_type'] ?? 'video/mp4'), ENT_QUOTES, 'UTF-8') ?>">
                        Your browser does not support HTML5 video.
                    </video>
                </div>
                <p class="lesson-media-note">Local course video. Download controls are disabled as a deterrent, not as DRM.</p>
            <?php elseif ($youtubeId !== null): ?>
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

            <?php if (!empty($materials)): ?>
                <section class="lesson-materials">
                    <div class="lesson-section-heading">
                        <div>
                            <span class="eyebrow">Study materials</span>
                            <h2>Resources for this lesson</h2>
                            <p>Open supported files in FastPhunzira. Downloads are available only when the instructor has enabled them.</p>
                        </div>
                    </div>
                    <div class="lesson-material-grid">
                        <?php foreach ($materials as $material): ?>
                            <?php $materialType = strtolower((string) ($material['type'] ?? 'other')); ?>
                            <article class="lesson-material-card">
                                <div class="lesson-material-icon" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($materialType, 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="lesson-material-info">
                                    <strong><?= htmlspecialchars((string) ($material['title'] ?? $material['original_name'] ?? 'Study material'), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars(strtoupper($materialType), ENT_QUOTES, 'UTF-8') ?> · <?= number_format(((int) ($material['file_size'] ?? 0)) / 1048576, 2) ?> MB</span>
                                </div>
                                <div class="lesson-material-actions">
                                    <a class="btn secondary" target="_blank" rel="noopener" href="<?= htmlspecialchars(base_url('lesson-materials/' . (int) $material['id'] . '/view'), ENT_QUOTES, 'UTF-8') ?>">View</a>
                                    <?php if (!empty($material['download_allowed'])): ?>
                                        <a class="btn secondary" href="<?= htmlspecialchars(base_url('lesson-materials/' . (int) $material['id'] . '/download'), ENT_QUOTES, 'UTF-8') ?>">Download</a>
                                    <?php else: ?>
                                        <span class="lesson-download-locked">Download disabled</span>
                                    <?php endif; ?>
                                </div>
                            </article>
                            <?php if ($materialType === 'pdf'): ?>
                                <div class="lesson-pdf-viewer">
                                    <iframe src="<?= htmlspecialchars(base_url('lesson-materials/' . (int) $material['id'] . '/view') . '#toolbar=0&navpanes=0', ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars((string) ($material['title'] ?? 'PDF'), ENT_QUOTES, 'UTF-8') ?>" loading="lazy"></iframe>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($blocks)): ?>
                <section class="lesson-content-blocks">
                    <div class="lesson-section-heading"><span class="eyebrow">Lesson resources</span><h2>Continue the lesson</h2><p>Work through each item in the order provided by your instructor.</p></div>
                    <div class="student-content-blocks">
                    <?php foreach ($blocks as $block): ?>
                        <?php $type=(string)($block['type']??'text'); ?>
                        <article class="student-content-block">
                            <?php if($type==='text'): ?>
                                <?php if(trim((string)($block['title']??''))!==''): ?><h3><?= htmlspecialchars((string)$block['title'],ENT_QUOTES,'UTF-8') ?></h3><?php endif; ?>
                                <div class="student-block-text"><?= nl2br(htmlspecialchars((string)($block['content']??''),ENT_QUOTES,'UTF-8')) ?></div>
                            <?php elseif($type==='youtube'): ?>
                                <?php
                                $url=(string)($block['content']??''); $youtubeId=null;
                                if(preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/',$url,$m)) $youtubeId=$m[1];
                                ?>
                                <?php if($youtubeId): ?>
                                    <?php if(trim((string)($block['title']??''))!==''): ?><h3><?= htmlspecialchars((string)$block['title'],ENT_QUOTES,'UTF-8') ?></h3><?php endif; ?>
                                    <div class="lesson-video-frame"><iframe src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($youtubeId,ENT_QUOTES,'UTF-8') ?>" title="<?= htmlspecialchars((string)($block['title']??'YouTube video'),ENT_QUOTES,'UTF-8') ?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
                                <?php endif; ?>
                            <?php elseif($type==='video'): ?>
                                <h3><?= htmlspecialchars((string)($block['title']??$block['original_name']??'Video'),ENT_QUOTES,'UTF-8') ?></h3>
                                <video controls controlsList="nodownload noplaybackrate" disablePictureInPicture preload="metadata" playsinline><source src="<?= htmlspecialchars(base_url('lesson-blocks/'.(int)$block['id'].'/video'),ENT_QUOTES,'UTF-8') ?>" type="<?= htmlspecialchars((string)($block['mime_type']??'video/mp4'),ENT_QUOTES,'UTF-8') ?>"></video>
                            <?php elseif($type==='material'): ?>
                                <div class="student-resource-row"><div><strong><?= htmlspecialchars((string)($block['title']??$block['original_name']??'Learning resource'),ENT_QUOTES,'UTF-8') ?></strong><span><?= htmlspecialchars(strtoupper((string)($block['mime_type']??'file')),ENT_QUOTES,'UTF-8') ?> · <?= number_format(((int)($block['file_size']??0))/1048576,2) ?> MB</span></div><div class="lesson-material-actions"><a class="btn secondary" target="_blank" rel="noopener" href="<?= htmlspecialchars(base_url('lesson-blocks/'.(int)$block['id'].'/view'),ENT_QUOTES,'UTF-8') ?>">View</a><?php if(!empty($block['download_allowed'])): ?><a class="btn secondary" href="<?= htmlspecialchars(base_url('lesson-blocks/'.(int)$block['id'].'/download'),ENT_QUOTES,'UTF-8') ?>">Download</a><?php else: ?><span class="lesson-download-locked">Download disabled</span><?php endif; ?></div></div>
                                <?php if(($block['mime_type']??'')==='application/pdf'): ?><div class="lesson-pdf-viewer"><iframe src="<?= htmlspecialchars(base_url('lesson-blocks/'.(int)$block['id'].'/view').'#toolbar=0&navpanes=0',ENT_QUOTES,'UTF-8') ?>" title="<?= htmlspecialchars((string)($block['title']??'PDF'),ENT_QUOTES,'UTF-8') ?>" loading="lazy"></iframe></div><?php endif; ?>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <div class="lesson-completion">
                <?php if ($isCompleted): ?>
                    <div class="lesson-completed-state">
                        <span class="lesson-completed-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>
                        </span>
                        <div>
                            <strong>Lesson completed</strong>
                            <span>Your progress has been recorded.</span>
                            <?php if ($completedAt !== ''): ?>
                                <time datetime="<?= htmlspecialchars(str_replace(' ', 'T', $completedAt), ENT_QUOTES, 'UTF-8') ?>">Completed <?= htmlspecialchars($completedAt, ENT_QUOTES, 'UTF-8') ?></time>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($nextLesson['id'])): ?>
                        <a class="btn secondary" href="<?= htmlspecialchars(base_url('lessons/' . (int) $nextLesson['id']), ENT_QUOTES, 'UTF-8') ?>">
                            Continue to <?= htmlspecialchars((string) ($nextLesson['title'] ?? 'next lesson'), ENT_QUOTES, 'UTF-8') ?>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                        </a>
                    <?php else: ?>
                        <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . $courseId . '/learn'), ENT_QUOTES, 'UTF-8') ?>">Return to course</a>
                    <?php endif; ?>
                <?php else: ?>
                    <div>
                        <span class="eyebrow">Ready to move on?</span>
                        <strong>Mark this lesson complete</strong>
                    </div>
                    <form method="POST" action="<?= htmlspecialchars(base_url('student/lessons/' . $lessonId . '/complete'), ENT_QUOTES, 'UTF-8') ?>">
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
