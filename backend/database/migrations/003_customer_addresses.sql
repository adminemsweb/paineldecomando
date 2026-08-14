CREATE TABLE IF NOT EXISTS customer_addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(80) NOT NULL DEFAULT 'Principal',
    recipient_name VARCHAR(150) NOT NULL,
    postal_code CHAR(8) NOT NULL,
    street VARCHAR(190) NOT NULL,
    number VARCHAR(30) NOT NULL,
    complement VARCHAR(120) NULL,
    district VARCHAR(120) NOT NULL,
    city VARCHAR(120) NOT NULL,
    state CHAR(2) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_customer_addresses_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_customer_addresses_user_default(user_id,is_default),
    INDEX idx_customer_addresses_postal_code(postal_code)
) ENGINE=InnoDB;
