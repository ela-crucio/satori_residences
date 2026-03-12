-- ============================================================
-- SATORI RESIDENCES BOOKING SYSTEM - DATABASE SCHEMA
-- ============================================================

CREATE DATABASE IF NOT EXISTS satori_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE satori_booking;

-- ============================================================
-- TABLE: admins
-- ============================================================
CREATE TABLE admins (
    admin_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    role           ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    is_active      TINYINT(1) NOT NULL DEFAULT 1,
    last_login     DATETIME NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: unit_types
-- ============================================================
CREATE TABLE unit_types (
    unit_type_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(50) NOT NULL,          -- e.g. '1 Bedroom', '2 Bedroom'
    slug           VARCHAR(50) NOT NULL UNIQUE,   -- e.g. '1br', '2br', '3br'
    description    TEXT NOT NULL,
    amenities      TEXT NOT NULL,                 -- JSON array stored as text
    max_guests     TINYINT UNSIGNED NOT NULL DEFAULT 2,
    price_per_night DECIMAL(10,2) NOT NULL,
    is_active      TINYINT(1) NOT NULL DEFAULT 1,
    sort_order     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: unit_images
-- ============================================================
CREATE TABLE unit_images (
    image_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unit_type_id   INT UNSIGNED NOT NULL,
    image_path     VARCHAR(255) NOT NULL,
    alt_text       VARCHAR(150) NULL,
    is_primary     TINYINT(1) NOT NULL DEFAULT 0,
    sort_order     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (unit_type_id) REFERENCES unit_types(unit_type_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: guests
-- ============================================================
CREATE TABLE guests (
    guest_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    phone          VARCHAR(30) NOT NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: bookings
-- ============================================================
CREATE TABLE bookings (
    booking_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(20) NOT NULL UNIQUE,  -- SRB-2026-000145
    guest_id          INT UNSIGNED NOT NULL,
    unit_type_id      INT UNSIGNED NOT NULL,
    check_in_date     DATE NOT NULL,
    check_out_date    DATE NOT NULL,
    number_of_guests  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    price_per_night   DECIMAL(10,2) NOT NULL,
    total_nights      TINYINT UNSIGNED NOT NULL,
    total_price       DECIMAL(10,2) NOT NULL,
    special_requests  TEXT NULL,
    booking_status    ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (guest_id) REFERENCES guests(guest_id),
    FOREIGN KEY (unit_type_id) REFERENCES unit_types(unit_type_id),
    INDEX idx_checkin  (check_in_date),
    INDEX idx_checkout (check_out_date),
    INDEX idx_unit_type (unit_type_id),
    INDEX idx_status (booking_status)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: payments
-- ============================================================
CREATE TABLE payments (
    payment_id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id            INT UNSIGNED NOT NULL UNIQUE,
    payment_method        ENUM('gcash','online_payment','cash_on_arrival') NOT NULL,
    payment_status        ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
    transaction_reference VARCHAR(50) NULL,
    amount                DECIMAL(10,2) NOT NULL,
    payment_date          DATETIME NULL,
    created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: booking_logs
-- ============================================================
CREATE TABLE booking_logs (
    log_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id    INT UNSIGNED NOT NULL,
    action        VARCHAR(100) NOT NULL,
    performed_by  VARCHAR(100) NOT NULL,
    notes         TEXT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default admin (password: Admin@1234)
INSERT INTO admins (full_name, email, password_hash, role) VALUES
('Satori Admin', 'admin', '$2y$12$bRt39rFmuJat6q0AXMB6XOu99YZcNzhi3uTxGIW3Q6s1YD6jmkpEq', 'admin');

-- Unit Types
INSERT INTO unit_types (name, slug, description, amenities, max_guests, price_per_night, sort_order) VALUES
(
    '1 Bedroom',
    '1br',
    'Our cozy 1-Bedroom unit is perfect for solo travelers or couples seeking a luxurious retreat in the heart of the city. Featuring a fully-equipped kitchen, elegant furnishings, and breathtaking city views, it offers the perfect blend of comfort and sophistication.',
    '["Free Wi-Fi","Smart TV","Fully-equipped Kitchen","Air Conditioning","Private Balcony","Washer/Dryer","City Views","24/7 Security","Swimming Pool Access","Gym Access","Parking Available","Daily Housekeeping"]',
    2,
    4500.00,
    1
),
(
    '2 Bedroom',
    '2br',
    'Our spacious 2-Bedroom unit is ideal for small families or groups of friends. Enjoy two separate bedrooms, a stylish living area, and a full kitchen — all designed with modern elegance in mind. Panoramic views and premium amenities complete the experience.',
    '["Free Wi-Fi","Smart TV (2)","Fully-equipped Kitchen","Air Conditioning (2 units)","Private Balcony","Washer/Dryer","Panoramic City Views","24/7 Security","Swimming Pool Access","Gym Access","Parking Available","Daily Housekeeping","Dining Area"]',
    4,
    7500.00,
    2
),
(
    '3 Bedroom',
    '3br',
    'Our premium 3-Bedroom unit is the ultimate choice for large families and groups. Featuring three elegantly designed bedrooms, multiple bathrooms, a gourmet kitchen, and a spacious living and dining area — this is luxury condo living at its finest.',
    '["Free Wi-Fi","Smart TV (3)","Gourmet Kitchen","Air Conditioning (3 units)","Wrap-around Balcony","Washer/Dryer","Panoramic Views","24/7 Security","Swimming Pool Access","Gym Access","2 Parking Slots","Daily Housekeeping","Dedicated Dining Room","Entertainment Area"]',
    8,
    11500.00,
    3
);

-- Placeholder unit images (use real images in production)
INSERT INTO unit_images (unit_type_id, image_path, alt_text, is_primary, sort_order) VALUES
(1, '/public/images/1br-main.jpg', '1 Bedroom Unit', 1, 0),
(1, '/public/images/1br-kitchen.jpg', '1BR Kitchen', 0, 1),
(1, '/public/images/1br-bathroom.jpg', '1BR Bathroom', 0, 2),
(2, '/public/images/2br-main.jpg', '2 Bedroom Unit', 1, 0),
(2, '/public/images/2br-living.jpg', '2BR Living Area', 0, 1),
(2, '/public/images/2br-bedroom.jpg', '2BR Bedroom', 0, 2),
(3, '/public/images/3br-main.jpg', '3 Bedroom Unit', 1, 0),
(3, '/public/images/3br-living.jpg', '3BR Living Area', 0, 1),
(3, '/public/images/3br-master.jpg', '3BR Master Bedroom', 0, 2);
