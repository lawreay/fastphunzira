CREATE TABLE IF NOT EXISTS certificate_verification_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    certificate_number_hash CHAR(64) NOT NULL,
    successful TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_cert_verify_ip_created (ip_address, created_at),
    KEY idx_cert_verify_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
