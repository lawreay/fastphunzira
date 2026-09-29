<?php

use App\Core\Auth;
use App\Support\Csrf;

$courses = is_array($courses ?? null) ? $courses : [];
$publishedCount = 0;
$premiumCount = 0;

foreach ($courses as $course) {
    if (strtolower((string) ($course['status'] ?? 'draft')) === 'published') {
        $publishedCount++;
    }

    if (strtolower((string) ($course['access_tier'] ?? 'regular')) === 'premium') {
        $premiumCount++;
    }
}
?>

<section class="admin-courses-page">
    <div class="admin-page-heading">
        <div>
            <span class="eyebrow">Administration</span>
            <h1>Course management</h1>
            <p>Create, review, publish and maintain the learning catalogue.</p>
        </div>

        <?php if (Auth::userCan('courses.manage')): ?>
            <a class="btn" href="<?= htmlspecialchars(base_url('admin/courses/create'), ENT_QUOTES, 'UTF-8') ?>">Create course</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($success)): ?>
        <div class="admin-alert success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="admin-alert error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="admin-stat-grid">
        <article class="admin-stat-card">
            <span>Total courses</span>
            <strong><?= count($courses) ?></strong>
        </article>
        <article class="admin-stat-card">
            <span>Published</span>
            <strong><?= $publishedCount ?></strong>
        </article>
        <article class="admin-stat-card">
            <span>Premium</span>
            <strong><?= $premiumCount ?></strong>
        </article>
    </div>

    <?php if (empty($courses)): ?>
        <section class="admin-empty-state">
            <span class="admin-empty-icon" aria-hidden="true">01</span>
            <h2>No courses yet</h2>
            <p>The catalogue is empty. Create the first course and build the learning path from there.</p>
            <?php if (Auth::userCan('courses.manage')): ?>
                <a class="btn" href="<?= htmlspecialchars(base_url('admin/courses/create'), ENT_QUOTES, 'UTF-8') ?>">Create your first course</a>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <section class="admin-table-card">
            <div class="admin-table-heading">
                <div>
                    <span class="eyebrow">Catalogue</span>
                    <h2>Courses</h2>
                </div>
                <span class="admin-table-count"><?= count($courses) ?> total</span>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Access</th>
                        <th class="admin-table-actions">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($courses as $course): ?>
                        <?php
                        $courseId = (int) ($course['id'] ?? 0);
                        $courseTitle = trim((string) ($course['title'] ?? 'Untitled course'));
                        $status = strtolower((string) ($course['status'] ?? 'draft'));
                        $accessTier = strtolower((string) ($course['access_tier'] ?? 'regular'));
                        ?>
                        <tr>
                            <td>
                                <div class="admin-course-cell">
                                    <span class="admin-course-mark" aria-hidden="true">F</span>
                                    <div>
                                        <strong><?= htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span>Course #<?= $courseId ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="admin-status <?= $status === 'published' ? 'is-published' : 'is-draft' ?>">
                                    <?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <span class="admin-access <?= $accessTier === 'premium' ? 'is-premium' : '' ?>">
                                    <?= htmlspecialchars(ucfirst($accessTier), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="admin-table-actions">
                                <div class="admin-actions">
                                    <a class="btn secondary" href="<?= htmlspecialchars(base_url('courses/' . $courseId), ENT_QUOTES, 'UTF-8') ?>">View</a>
                                    <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/' . $courseId . '/edit'), ENT_QUOTES, 'UTF-8') ?>">Edit</a>

                                    <?php if ($status !== 'published'): ?>
                                        <form method="POST" action="<?= htmlspecialchars(base_url('admin/courses/publish'), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="id" value="<?= $courseId ?>">
                                            <button class="btn light" type="submit">Publish</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <div class="admin-footer-link">
        <a href="<?= htmlspecialchars(base_url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">← Back to dashboard</a>
    </div>
</section>
