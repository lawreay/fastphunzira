CREATE TABLE IF NOT EXISTS courses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_courses_slug (slug),
    KEY idx_courses_status (status),
    KEY idx_courses_created_by (created_by),
    CONSTRAINT fk_courses_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_modules (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    course_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_course_modules_course_id (course_id),
    CONSTRAINT fk_course_modules_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lessons (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    module_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    summary TEXT NULL,
    content LONGTEXT NULL,
    video_url VARCHAR(255) NULL,
    file_path VARCHAR(255) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_lessons_module_id (module_id),
    CONSTRAINT fk_lessons_module FOREIGN KEY (module_id) REFERENCES course_modules (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
