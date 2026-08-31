CREATE TABLE IF NOT EXISTS analytics_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type ENUM('page_view','product_view','product_click','search','whatsapp_click','quote_click') NOT NULL,
    session_id CHAR(36) NOT NULL,
    path VARCHAR(500) NOT NULL,
    product_slug VARCHAR(210) NULL,
    search_term VARCHAR(190) NULL,
    result_count INT UNSIGNED NULL,
    target_url VARCHAR(500) NULL,
    referrer VARCHAR(500) NULL,
    device_type ENUM('desktop','mobile','tablet','unknown') NOT NULL DEFAULT 'unknown',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_analytics_created(created_at),
    INDEX idx_analytics_type_created(event_type,created_at),
    INDEX idx_analytics_product(product_slug,event_type,created_at),
    INDEX idx_analytics_search(search_term,event_type,created_at),
    INDEX idx_analytics_session(session_id,created_at)
) ENGINE=InnoDB;
