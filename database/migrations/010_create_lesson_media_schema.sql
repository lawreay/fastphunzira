ALTER TABLE lessons
    ADD COLUMN video_original_name VARCHAR(255) NULL AFTER video_url,
    ADD COLUMN video_mime_type VARCHAR(100) NULL AFTER video_original_name,
    ADD COLUMN video_file_size BIGINT UNSIGNED NULL AFTER video_mime_type;

CREATE TABLE IF NOT EXISTS lesson_materials (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    lesson_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    type ENUM('pdf', 'audio', 'video', 'html', 'document', 'other') NOT NULL DEFAULT 'other',
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
    storage_path VARCHAR(500) NOT NULL,
    download_allowed TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_lesson_materials_lesson_id (lesson_id),
    KEY idx_lesson_materials_type (type),
    CONSTRAINT fk_lesson_materials_lesson FOREIGN KEY (lesson_id) REFERENCES lessons (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
