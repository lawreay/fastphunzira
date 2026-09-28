<?php

use App\Core\Auth;
?>
<section class="catalogue-page">
    <div class="catalogue-hero">
        <div>
            <span class="eyebrow">Learning catalogue</span>
            <h1>Build skills. One course at a time.</h1>
            <p>Explore available courses, choose a learning track, and move from lessons to practice and assessment.</p>
        </div>
        <div class="catalogue-count">
            <strong><?= count($courses) ?></strong>
            <span><?= count($courses) === 1 ? 'course available' : 'courses available' ?></span>
        </div>
    </div>

    <?php if (empty($courses)): ?>
        <div class="catalogue-empty">
            <div class="catalogue-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M5 4h12a2 2 0 0 1 2 2v14H7a2 2 0 0 0-2 2V4Zm0 16a2 2 0 0 1 2-2h12M8 8h7M8 12h5"/></svg>
            </div>
            <h2>No courses published yet</h2>
            <p>There are no courses available in the catalogue right now.</p>
            <?php if (Auth::userCan('courses.manage')): ?>
                <a class="btn" href="<?= htmlspecialchars(base_url('admin/courses'), ENT_QUOTES, 'UTF-8') ?>">Create a course</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="course-grid catalogue-grid">
            <?php foreach ($courses as $course): ?>
                <?php
                    $status = strtolower((string) ($course['status'] ?? 'draft'));
                    $title = (string) ($course['title'] ?? 'Untitled course');
                    $description = trim((string) ($course['description'] ?? ''));
                    $description = $description !== '' ? $description : 'Learn through structured lessons and practical assessment.';
                    $isPremium = !empty($course['is_premium']);
                ?>
                <article class="course-card catalogue-card">
                    <div class="catalogue-card-top">
                        <span class="course-type <?= $isPremium ? 'premium' : 'standard' ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="<?= $isPremium ? 'm12 3 2.1 5.2L20 10l-5.9 1.8L12 17l-2.1-5.2L4 10l5.9-1.8L12 3Z' : 'M5 4h12a2 2 0 0 1 2 2v14H7a2 2 0 0 0-2 2V4Zm0 16a2 2 0 0 1 2-2h12' ?>"/>
                            </svg>
                            <?= $isPremium ? 'Premium' : 'Standard' ?>
                        </span>
                        <span class="status-badge status-<?= htmlspecialchars(preg_replace('/[^a-z0-9_-]/', '', $status), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <div class="catalogue-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 5h16v14H4zM8 9h8M8 13h5"/></svg>
                    </div>

                    <h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>

                    <div class="catalogue-card-footer">
                        <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . (int) ($course['id'] ?? 0)), ENT_QUOTES, 'UTF-8') ?>">
                            View course
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (Auth::userCan('courses.manage')): ?>
            <div class="catalogue-admin">
                <div>
                    <strong>Course administration</strong>
                    <span>Manage course content and publishing from the admin area.</span>
                </div>
                <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses'), ENT_QUOTES, 'UTF-8') ?>">Manage courses</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
