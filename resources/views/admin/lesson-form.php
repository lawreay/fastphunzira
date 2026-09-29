<?php
use App\Support\Csrf;

$moduleId = (int) ($moduleId ?? 0);
$courseId = (int) ($courseId ?? 0);
$lesson = $lesson ?? null;
$isEditing = $lesson !== null;
$lessonId = (int) ($lesson['id'] ?? 0);
$materials = $materials ?? [];
$hasLocalVideo = !empty($lesson['file_path']);
$hasExternalVideo = trim((string) ($lesson['video_url'] ?? '')) !== '';
?>

<section class="admin-form-page">
    <div class="admin-form-heading">
        <div>
            <span class="eyebrow">Course content</span>
            <h1><?= $isEditing ? 'Edit lesson' : 'Create lesson' ?></h1>
            <p><?= $isEditing ? 'Manage lesson content, video, and study materials from one workspace.' : 'Create the lesson and attach the resources students will use.' ?></p>
        </div>
        <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/' . $courseId . '/edit?tab=modules'), ENT_QUOTES, 'UTF-8') ?>">← Course</a>
    </div>

    <form class="admin-editor-card lesson-media-editor" method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars(base_url($isEditing ? 'admin/lessons/' . $lessonId . '/save-media' : 'admin/modules/' . $moduleId . '/lessons/store-media'), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="admin-editor-section">
            <span class="eyebrow">01 · Lesson information</span>
            <div class="form-group">
                <label for="title">Lesson title</label>
                <input id="title" name="title" type="text" maxlength="255" value="<?= htmlspecialchars((string) ($lesson['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="form-group">
                <label for="summary">Summary</label>
                <textarea id="summary" name="summary" rows="4"><?= htmlspecialchars((string) ($lesson['summary'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="form-group">
                <label for="content">Lesson content</label>
                <textarea id="content" name="content" rows="14" required><?= htmlspecialchars((string) ($lesson['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                <small>Lesson text stays in the database. Files are stored separately from the PHP source.</small>
            </div>
        </div>

        <div class="admin-editor-section">
            <span class="eyebrow">02 · Video</span>
            <div class="admin-media-choice">
                <div>
                    <strong>External video</strong>
                    <span>YouTube or another supported hosted video URL.</span>
                </div>
                <input id="video_url" name="video_url" type="url" value="<?= htmlspecialchars((string) ($lesson['video_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="https://www.youtube.com/watch?v=...">
            </div>

            <div class="admin-media-upload">
                <div>
                    <strong>Local video upload</strong>
                    <span>Upload a video to server-side media storage. It is not committed to GitHub.</span>
                </div>
                <input id="video_file" name="video_file" type="file" accept="video/mp4,video/webm,video/ogg">
            </div>

            <?php if ($hasLocalVideo): ?>
                <div class="admin-current-media">
                    <strong>Current local video</strong>
                    <span><?= htmlspecialchars((string) ($lesson['video_original_name'] ?? basename((string) $lesson['file_path'])), ENT_QUOTES, 'UTF-8') ?></span>
                    <label><input type="checkbox" name="remove_video" value="1"> Remove local video</label>
                </div>
            <?php elseif ($hasExternalVideo): ?>
                <div class="admin-current-media"><strong>Current video</strong><span>External video URL is configured.</span></div>
            <?php endif; ?>

            <small>Allowed local formats: MP4, WebM, OGG. The configured server upload limit still applies. Browser limits are not magic, because computers remain tragically literal.</small>
        </div>

        <div class="admin-editor-section">
            <div class="admin-section-heading">
                <div>
                    <span class="eyebrow">03 · Study materials</span>
                    <h2>Upload learning resources</h2>
                    <p>Add PDFs, audio, documents, HTML files, or supplementary video. Each material can allow or deny downloads.</p>
                </div>
            </div>

            <div class="admin-material-upload-grid">
                <div class="form-group">
                    <label for="material_title">Material title</label>
                    <input id="material_title" name="material_title" type="text" maxlength="180" placeholder="e.g. Lesson notes">
                </div>
                <div class="form-group">
                    <label for="material_file">Choose file</label>
                    <input id="material_file" name="material_file" type="file" accept=".pdf,.mp3,.wav,.ogg,.m4a,.mp4,.webm,.html,.htm,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt">
                </div>
                <div class="form-group">
                    <label for="material_sort_order">Order</label>
                    <input id="material_sort_order" name="material_sort_order" type="number" min="0" value="<?= count($materials) ?>">
                </div>
                <label class="admin-checkbox">
                    <input type="checkbox" name="download_allowed" value="1" checked>
                    <span><strong>Allow student download</strong><small>Turn this off when the material should only be viewed through FastPhunzira.</small></span>
                </label>
            </div>
            <p class="admin-form-hint">Upload one new material per save. Existing materials can be renamed, reordered, download-restricted, or deleted below.</p>

            <?php if (empty($materials)): ?>
                <div class="admin-module-empty">No study materials attached yet.</div>
            <?php else: ?>
                <div class="admin-material-list">
                    <?php foreach ($materials as $material): ?>
                        <div class="admin-material-item">
                            <form method="post" action="<?= htmlspecialchars(base_url('admin/materials/' . (int) $material['id'] . '/update'), ENT_QUOTES, 'UTF-8') ?>">
                                <?= Csrf::input() ?>
                                <div class="admin-material-main">
                                    <span class="admin-material-type"><?= htmlspecialchars(strtoupper((string) ($material['type'] ?? 'file')), ENT_QUOTES, 'UTF-8') ?></span>
                                    <input name="title" value="<?= htmlspecialchars((string) ($material['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                    <span class="admin-material-file"><?= htmlspecialchars((string) ($material['original_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="admin-material-controls">
                                    <input name="sort_order" type="number" min="0" value="<?= (int) ($material['sort_order'] ?? 0) ?>" aria-label="Material order">
                                    <label class="admin-checkbox compact"><input type="checkbox" name="download_allowed" value="1" <?= !empty($material['download_allowed']) ? 'checked' : '' ?>><span>Download</span></label>
                                    <a class="btn secondary" target="_blank" rel="noopener" href="<?= htmlspecialchars(base_url('lesson-materials/' . (int) $material['id'] . '/view'), ENT_QUOTES, 'UTF-8') ?>">View</a>
                                    <?php if (!empty($material['download_allowed'])): ?><a class="btn secondary" href="<?= htmlspecialchars(base_url('lesson-materials/' . (int) $material['id'] . '/download'), ENT_QUOTES, 'UTF-8') ?>">Download</a><?php endif; ?>
                                    <button class="btn secondary" type="submit">Save</button>
                                </div>
                            </form>
                            <form method="post" action="<?= htmlspecialchars(base_url('admin/materials/' . (int) $material['id'] . '/delete'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Delete this study material? This cannot be undone.');">
                                <?= Csrf::input() ?><button class="btn danger" type="submit">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-editor-section">
            <span class="eyebrow">04 · Ordering</span>
            <div class="form-group">
                <label for="sort_order">Lesson order</label>
                <input id="sort_order" name="sort_order" type="number" min="0" value="<?= (int) ($lesson['sort_order'] ?? 0) ?>">
            </div>
        </div>

        <div class="admin-editor-actions">
            <button class="btn" type="submit"><?= $isEditing ? 'Save lesson' : 'Create lesson' ?></button>
            <a class="btn secondary" href="<?= htmlspecialchars(base_url('admin/courses/' . $courseId . '/edit?tab=modules'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
        </div>
    </form>
</section>
