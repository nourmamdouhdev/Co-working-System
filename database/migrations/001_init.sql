CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_clients_phone (phone),
    KEY idx_clients_full_name (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL,
    checked_in_by INT UNSIGNED NOT NULL,
    check_in_at DATETIME NOT NULL,
    check_out_at DATETIME NULL,
    status ENUM('active', 'closed') NOT NULL DEFAULT 'active',
    hourly_rate_snapshot DECIMAL(10,2) NOT NULL,
    duration_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    billable_hours INT UNSIGNED NOT NULL DEFAULT 0,
    time_charge DECIMAL(10,2) NOT NULL DEFAULT 0,
    addons_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_visits_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_visits_checked_in_by FOREIGN KEY (checked_in_by) REFERENCES users(id),
    KEY idx_visits_status_checkin (status, check_in_at),
    KEY idx_visits_client_status (client_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_products_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visit_addons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visit_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    product_name_snapshot VARCHAR(120) NOT NULL,
    unit_price_snapshot DECIMAL(10,2) NOT NULL,
    qty INT UNSIGNED NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_visit_addons_visit FOREIGN KEY (visit_id) REFERENCES visits(id),
    CONSTRAINT fk_visit_addons_product FOREIGN KEY (product_id) REFERENCES products(id),
    KEY idx_visit_addons_visit (visit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visit_id INT UNSIGNED NOT NULL,
    method ENUM('cash', 'visa') NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    paid_at DATETIME NOT NULL,
    received_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_visit FOREIGN KEY (visit_id) REFERENCES visits(id),
    CONSTRAINT fk_payments_received_by FOREIGN KEY (received_by) REFERENCES users(id),
    UNIQUE KEY uq_payments_visit (visit_id),
    KEY idx_payments_paid_at (paid_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` VARCHAR(255) NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity VARCHAR(100) NOT NULL,
    entity_id INT UNSIGNED NULL,
    payload JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id),
    KEY idx_audit_entity (entity, entity_id),
    KEY idx_audit_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (`key`, `value`, updated_at)
VALUES
    ('hourly_rate', '10.00', NOW()),
    ('currency', 'USD', NOW()),
    ('timezone', 'UTC', NOW())
ON DUPLICATE KEY UPDATE
    `value` = VALUES(`value`),
    updated_at = NOW();

INSERT INTO users (name, username, password_hash, role, is_active, created_at, updated_at)
VALUES ('System Admin', 'admin', '$2y$12$Dsxm0BM9QxUS63Vj.1S2jOPZ67Q3wLpV/.26lDxmnR.lAPxT/tlD2', 'admin', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    role = VALUES(role),
    is_active = VALUES(is_active),
    updated_at = NOW();
