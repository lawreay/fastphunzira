<?php

use App\Core\Auth;
use App\Support\Csrf;

$courseId = (int) ($course['id'] ?? 0);
$title = trim((string) ($course['title'] ?? 'Untitled course'));
$description = trim((string) ($course['description'] ?? ''));
$status = strtolower((string) ($course['status'] ?? 'draft'));
$accessTier = strtolower((string) ($course['access_tier'] ?? 'regular'));
$isPremium = $accessTier === 'premium';
$membershipIsPremium = ($membership['plan'] ?? 'regular') === 'premium'
    && ($membership['status'] ?? '') === 'active';
$canManageCourses = Auth::userCan('courses.manage');
$isEnrolled = (bool) ($isEnrolled ?? false);
$hasCourseAccess = $isEnrolled && (!$isPremium || $membershipIsPremium);
$returnToMyCourses = ($_GET['from'] ?? '') === 'my-courses';
$returnUrl = $returnToMyCourses ? base_url('my-courses') : base_url('courses');
$returnLabel = $returnToMyCourses ? 'Back to my courses' : 'Back to catalogue';
?>

<section class="course-detail-page">
    <div class="course-detail-breadcrumb">
        <a href="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            <?= htmlspecialchars($returnLabel, ENT_QUOTES, 'UTF-8') ?>
        </a>
    </div>

    <div class="course-detail-hero">
        <div class="course-detail-copy">
            <div class="course-detail-badges">
                <span class="course-type <?= $isPremium ? 'premium' : 'standard' ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="<?= $isPremium ? 'm12 3 2.1 5.2L20 10l-5.9 1.8L12 17l-2.1-5.2L4 10l5.9-1.8L12 3Z' : 'M5 4h12a2 2 0 0 1 2 2v14H7a2 2 0 0 0-2 2V4Zm0 16a2 2 0 0 1 2-2h12' ?>"/>
                    </svg>
                    <?= $isPremium ? 'Premium course' : 'Standard course' ?>
                </span>
                <span class="status-badge status-<?= htmlspecialchars(preg_replace('/[^a-z0-9_-]/', '', $status), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <span class="eyebrow">Course overview</span>
            <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>

            <?php if ($description !== ''): ?>
                <p class="course-detail-description"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <p class="course-detail-description">A structured learning course on FastPhunzira.</p>
            <?php endif; ?>
        </div>

        <aside class="course-access-card">
            <div class="course-access-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 5h16v14H4zM8 9h8M8 13h5"/></svg>
            </div>

            <span class="course-access-label">Access</span>
            <h2><?= $isPremium ? 'Premium membership required' : 'Available to enrolled students' ?></h2>

            <?php if (Auth::check()): ?>
                <?php if ($hasCourseAccess): ?>
                    <p>You are enrolled in this course. Continue learning from where you left off.</p>
                    <a class="btn btn-block" href="<?= htmlspecialchars(base_url('courses/' . $courseId . '/learn'), ENT_QUOTES, 'UTF-8') ?>">
                        Continue learning
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                    </a>
                <?php elseif ($isPremium && !$membershipIsPremium): ?>
                    <p>This course requires an active Premium membership before you can enroll or continue learning.</p>
                    <a class="btn btn-block" href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">
                        View membership
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                    </a>
                <?php else: ?>
                    <p>Enroll to unlock the course learning area and continue through its lessons.</p>
                    <form method="POST" action="<?= htmlspecialchars(base_url('courses/' . $courseId . '/enroll'), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                        <button class="btn btn-block" type="submit">
                            Enroll in this course
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <p>Create an account or log in to enroll and access the learning area.</p>
                <a class="btn btn-block" href="<?= htmlspecialchars(base_url('login'), ENT_QUOTES, 'UTF-8') ?>">
                    Log in to enroll
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                </a>
            <?php endif; ?>

            <?php if ($canManageCourses): ?>
                <a class="course-admin-link" href="<?= htmlspecialchars(base_url('admin/courses/' . $courseId . '/edit'), ENT_QUOTES, 'UTF-8') ?>">
                    Edit course
                </a>
            <?php endif; ?>
        </aside>
    </div>

    <div class="course-detail-section">
        <div class="course-detail-section-heading">
            <span class="eyebrow">Learning path</span>
            <h2>What happens after enrollment?</h2>
            <p>FastPhunzira connects course content with practice and assessment in one learning flow.</p>
        </div>

        <div class="course-flow-grid">
            <article class="course-flow-card">
                <span>01</span>
                <h3>Learn</h3>
                <p>Work through the lessons and course material at your own pace.</p>
            </article>
            <article class="course-flow-card">
                <span>02</span>
                <h3>Practice</h3>
                <p>Use the available quizzes and practice activities to reinforce your learning.</p>
            </article>
            <article class="course-flow-card">
                <span>03</span>
                <h3>Examine</h3>
                <p>Complete the relevant assessments when they are available for the course.</p>
            </article>
        </div>
    </div>

    <div class="course-detail-footer">
        <a class="btn secondary" href="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            <?= htmlspecialchars($returnLabel, ENT_QUOTES, 'UTF-8') ?>
        </a>
    </div>
</section>
