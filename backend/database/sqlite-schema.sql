PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    status TEXT NOT NULL DEFAULT 'active'
);

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    failed_login_attempts INTEGER NOT NULL DEFAULT 0,
    locked_until TEXT NULL,
    last_login_at TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TEXT NULL
);

CREATE TABLE IF NOT EXISTS user_roles (
    user_id INTEGER NOT NULL,
    role_id INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token_hash TEXT NOT NULL UNIQUE,
    expires_at TEXT NOT NULL,
    last_used_at TEXT NOT NULL,
    user_agent TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS customer_profiles (
    user_id INTEGER PRIMARY KEY,
    company_name TEXT NULL,
    phone TEXT NOT NULL,
    lgpd_consent_at TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS customer_addresses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    label TEXT NOT NULL DEFAULT 'Principal',
    recipient_name TEXT NOT NULL,
    postal_code TEXT NOT NULL,
    street TEXT NOT NULL,
    number TEXT NOT NULL,
    complement TEXT NULL,
    district TEXT NOT NULL,
    city TEXT NOT NULL,
    state TEXT NOT NULL,
    is_default INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    summary TEXT NULL,
    description TEXT NULL,
    features TEXT NULL,
    benefits TEXT NULL,
    components TEXT NULL,
    voltages TEXT NULL,
    power_range TEXT NULL,
    protection_rating TEXT NULL,
    optional_components TEXT NULL,
    featured_image TEXT NULL,
    gallery_images TEXT NULL,
    video_url TEXT NULL,
    video_urls TEXT NULL,
    category_name TEXT NULL,
    reference_code TEXT NULL,
    brand TEXT NULL,
    model TEXT NULL,
    price_cents INTEGER NULL,
    installments INTEGER NOT NULL DEFAULT 1,
    stock_status TEXT NOT NULL DEFAULT 'on_demand',
    stock_quantity INTEGER NOT NULL DEFAULT 0,
    lead_time TEXT NULL,
    sales_channel TEXT NOT NULL DEFAULT 'both',
    warranty_days INTEGER NOT NULL DEFAULT 365,
    sort_order INTEGER NOT NULL DEFAULT 0,
    featured INTEGER NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'draft',
    published_at TEXT NULL,
    seo_title TEXT NULL,
    seo_description TEXT NULL,
    created_by INTEGER NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TEXT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_products_status ON products(status, sort_order);
CREATE INDEX IF NOT EXISTS idx_sessions_token ON user_sessions(token_hash, expires_at, revoked_at);
CREATE INDEX IF NOT EXISTS idx_customer_addresses_user_default ON customer_addresses(user_id, is_default);
CREATE INDEX IF NOT EXISTS idx_customer_addresses_postal_code ON customer_addresses(postal_code);

CREATE TABLE IF NOT EXISTS password_resets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token_hash TEXT NOT NULL UNIQUE,
    expires_at TEXT NOT NULL,
    used_at TEXT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_password_resets_token ON password_resets(token_hash, status, expires_at);
