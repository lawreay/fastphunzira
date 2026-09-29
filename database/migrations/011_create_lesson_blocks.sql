CREATE TABLE IF NOT EXISTS lesson_blocks (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    lesson_id INT UNSIGNED NOT NULL,
    type ENUM('text','youtube','video','material') NOT NULL,
    title VARCHAR(180) NULL,
    content LONGTEXT NULL,
    storage_path VARCHAR(500) NULL,
    original_name VARCHAR(255) NULL,
    mime_type VARCHAR(100) NULL,
    file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
    download_allowed TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_lesson_blocks_lesson (lesson_id),
    CONSTRAINT fk_lesson_blocks_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;