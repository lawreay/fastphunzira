-- Membership, premium access, and payment foundation.
-- Secrets stay in environment variables; this migration stores transaction metadata only.

CREATE TABLE IF NOT EXISTS platform_settings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(120) NOT NULL,
    setting_value TEXT NULL,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_platform_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_memberships (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    plan ENUM('regular', 'premium') NOT NULL DEFAULT 'regular',
    status ENUM('active', 'expired', 'cancelled') NOT NULL DEFAULT 'active',
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_student_memberships_user (user_id),
    KEY idx_student_memberships_plan_status (plan, status),
    CONSTRAINT fk_student_memberships_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    provider VARCHAR(40) NOT NULL DEFAULT 'paychangu',
    tx_ref VARCHAR(120) NOT NULL,
    purpose VARCHAR(80) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'MWK',
    status ENUM('pending', 'successful', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    provider_transaction_id VARCHAR(120) NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_payment_transactions_tx_ref (tx_ref),
    KEY idx_payment_transactions_user_status (user_id, status),
    CONSTRAINT fk_payment_transactions_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE courses
    ADD COLUMN access_tier ENUM('regular', 'premium') NOT NULL DEFAULT 'regular' AFTER status;

INSERT INTO platform_settings (setting_key, setting_value)
VALUES
    ('premium_price', '0'),
    ('premium_currency', 'MWK'),
    ('premium_duration_days', '30')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);

INSERT INTO student_memberships (user_id, plan, status)
SELECT id, 'regular', 'active'
FROM users
WHERE NOT EXISTS (
    SELECT 1 FROM student_memberships sm WHERE sm.user_id = users.id
);
