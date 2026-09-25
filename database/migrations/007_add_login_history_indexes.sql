ALTER TABLE login_history
    ADD KEY idx_login_history_email_created (email, created_at),
    ADD KEY idx_login_history_ip_created (ip_address, created_at),
    ADD KEY idx_login_history_status_created (status, created_at);
