CREATE TABLE IF NOT EXISTS customer_profiles (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    company_name VARCHAR(190) NULL,
    phone VARCHAR(30) NOT NULL,
    lgpd_consent_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_customer_profiles_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_customer_profiles_phone(phone)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    last_used_at DATETIME NOT NULL,
    user_agent VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    CONSTRAINT fk_user_sessions_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_sessions_lookup(token_hash,expires_at,revoked_at),
    INDEX idx_user_sessions_user(user_id,expires_at)
) ENGINE=InnoDB;

INSERT INTO roles (name, slug, status)
SELECT 'Cliente', 'customer', 'active'
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE slug = 'customer');
