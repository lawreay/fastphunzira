<?php
use App\Support\Csrf;
$moduleId = (int) ($moduleId ?? 0);
$courseId = (int) ($courseId ?? 0);
?>

<section class="admin-form-page">
    <div class="admin-form-heading">
        <div>
            <span class="eyebrow">Course content</span>
            <h1>Create lesson</h1>
            <p>Add the learning material students will use inside this module.</p>
        </div>
        <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/' . $courseId . '/edit'), ENT_QUOTES, 'UTF-8') ?>">← Course</a>
    </div>

    <div class="admin-form-layout">
        <form class="admin-editor-card" method="POST" action="<?= htmlspecialchars(base_url('admin/modules/' . $moduleId . '/lessons/store'), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="admin-editor-section">
                <span class="eyebrow">01 · Lesson information</span>
                <div class="form-group">
                    <label for="title">Lesson title</label>
                    <input id="title" name="title" type="text" maxlength="255" autocomplete="off" required>
                    <small>Give the lesson a specific, student-friendly title.</small>
                </div>
                <div class="form-group">
                    <label for="summary">Summary</label>
                    <textarea id="summary" name="summary" rows="4"></textarea>
                    <small>A short overview shown before students begin the lesson.</small>
                </div>
                <div class="form-group">
                    <label for="content">Lesson content</label>
                    <textarea id="content" name="content" rows="14" required></textarea>
                    <small>Keep the content structured and focused on the lesson objective.</small>
                </div>
            </div>

            <div class="admin-editor-section">
                <span class="eyebrow">02 · Video</span>
                <div class="admin-media-note">
                    <strong>Optional lesson video</strong>
                    <p>You can embed a YouTube video or provide a supported hosted video URL. Keep large video files outside the PHP application.</p>
                </div>
                <div class="form-group">
                    <label for="video_url">Video URL</label>
                    <input id="video_url" name="video_url" type="url" placeholder="https://www.youtube.com/watch?v=...">
                </div>
            </div>

            <div class="admin-editor-section">
                <span class="eyebrow">03 · Ordering</span>
                <div class="form-group">
                    <label for="sort_order">Lesson order</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" value="0">
                    <small>Use the order to control how lessons appear within the module.</small>
                </div>
            </div>

            <div class="admin-editor-actions">
                <button class="btn" type="submit">Create lesson</button>
                <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/' . $courseId . '/edit'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
            </div>
        </form>

        <aside class="admin-editor-sidebar">
            <div class="admin-editor-note">
                <span class="eyebrow">Publishing</span>
                <h2>Build before publishing</h2>
                <p>Create the lesson first, then review the course structure and publish only when the learning path is ready.</p>
            </div>
            <div class="admin-editor-note">
                <span class="eyebrow">Media</span>
                <h2>Use hosted video</h2>
                <p>YouTube and external video hosting keep the application lightweight and avoid turning cheap hosting into a very expensive video server.</p>
            </div>
        </aside>
    </div>
</section>
