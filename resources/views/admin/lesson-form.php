<?php
use App\Support\Csrf;
?>
<section class="card admin-form-card">
    <div class="page-heading">
        <div><span class="eyebrow">Course content</span><h1>Add Lesson</h1><p>Create a lesson and optionally attach a YouTube or hosted video.</p></div>
    </div>

    <form method="POST" action="<?= htmlspecialchars(base_url('admin/modules/' . (int) $moduleId . '/lessons/store'), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group"><label for="title">Lesson title</label><input id="title" name="title" type="text" required></div>
        <div class="form-group"><label for="summary">Summary</label><textarea id="summary" name="summary" rows="3"></textarea></div>
        <div class="form-group"><label for="content">Lesson content</label><textarea id="content" name="content" rows="10" required></textarea></div>

        <div class="media-source-panel">
            <div><strong>Lesson video</strong><p>Use a YouTube URL or a URL from a video host/CDN. Do not place large video files inside the PHP application on free hosting.</p></div>
            <div class="form-group"><label for="video_url">Video URL</label><input id="video_url" name="video_url" type="url" placeholder="https://www.youtube.com/watch?v=..."></div>
            <small class="form-help">YouTube links are embedded in the lesson player. Other supported video URLs are rendered with the protected player controls.</small>
        </div>

        <div class="form-group"><label for="sort_order">Lesson order</label><input id="sort_order" name="sort_order" type="number" min="0" value="0"></div>

        <button class="btn" type="submit">Create lesson</button>
        <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/' . (int) $courseId . '/edit'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
    </form>
</section>
