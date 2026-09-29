<?php

use App\Support\Csrf;

$isEditing = !empty($course);
$courseTitle = trim((string) ($course['title'] ?? ''));
$description = trim((string) ($course['description'] ?? ''));
$status = strtolower((string) ($course['status'] ?? 'draft'));
$accessTier = strtolower((string) ($course['access_tier'] ?? 'regular'));
?>

<section class="admin-form-page">
    <div class="admin-form-heading">
        <div>
            <span class="eyebrow">Course management</span>
            <h1><?= $isEditing ? 'Edit course' : 'Create a course' ?></h1>
            <p><?= $isEditing ? 'Update the course details and control how students access it.' : 'Set up the course information before adding lessons and assessments.' ?></p>
        </div>
        <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses'), ENT_QUOTES, 'UTF-8') ?>">← Courses</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="admin-alert error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="admin-form-layout">
        <form class="admin-editor-card" method="POST" action="<?= htmlspecialchars(base_url($isEditing ? 'admin/courses/update' : 'admin/courses/store'), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?= (int) ($course['id'] ?? 0) ?>">
            <?php endif; ?>

            <div class="admin-editor-section">
                <span class="eyebrow">01 · Course information</span>

                <div class="form-group">
                    <label for="title">Course title</label>
                    <input id="title" type="text" name="title" value="<?= htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8') ?>" maxlength="255" autocomplete="off" required>
                    <small>Use a clear title students can understand at a glance.</small>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="7" required><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <small>Explain what students will learn and what the course is for.</small>
                </div>
            </div>

            <div class="admin-editor-section">
                <span class="eyebrow">02 · Publishing</span>

                <div class="form-group">
                    <label for="status">Course status</label>
                    <select id="status" name="status">
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                    <small>Draft courses are kept out of the public catalogue.</small>
                </div>

                <div class="form-group">
                    <label for="access_tier">Student access</label>
                    <select id="access_tier" name="access_tier">
                        <option value="regular" <?= $accessTier === 'regular' ? 'selected' : '' ?>>Regular</option>
                        <option value="premium" <?= $accessTier === 'premium' ? 'selected' : '' ?>>Premium</option>
                    </select>
                    <small>Premium courses require an active Premium membership.</small>
                </div>
            </div>

            <div class="admin-editor-actions">
                <button class="btn" type="submit"><?= $isEditing ? 'Save changes' : 'Create course' ?></button>
                <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
            </div>
        </form>

        <aside class="admin-editor-sidebar">
            <div class="admin-editor-note">
                <span class="eyebrow">Access model</span>
                <h2>Keep access simple</h2>
                <p>Regular courses are available to enrolled students. Premium courses require an active Premium membership.</p>
            </div>

            <div class="admin-editor-note">
                <span class="eyebrow">Next step</span>
                <h2>Build the course</h2>
                <p>After creating the course, add modules, lessons and assessments through the existing administration workflows.</p>
            </div>
        </aside>
    </div>
</section>
