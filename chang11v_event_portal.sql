-- Create database if not exists (optional)
CREATE DATABASE IF NOT EXISTS chang11v_event_portal;
USE chang11v_event_portal;

-- USERS TABLE
-- All users: clients, staff, admins
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'client') NOT NULL DEFAULT 'client',
    full_name VARCHAR(100),
    status ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- EVENTS TABLE
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    guest_allowed BOOLEAN DEFAULT FALSE,
    max_guests_per_client INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- GUESTS TABLE
CREATE TABLE guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    event_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    checked_in BOOLEAN DEFAULT FALSE,
    qr_code VARCHAR(255),
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- CLIENT EVENT REGISTRATION TABLE
CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    event_id INT NOT NULL,
    num_guests INT DEFAULT 0,
    qr_code VARCHAR(255),
    checked_in BOOLEAN DEFAULT FALSE,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (client_id, event_id)
);

-- ATTENDANCE TABLE (both guests + clients)
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,             -- for clients
    guest_id INT,            -- for guests
    event_id INT NOT NULL,
    checked_in BOOLEAN DEFAULT FALSE,
    checkin_time DATETIME,
    method ENUM('manual', 'qr', 'other') DEFAULT 'qr',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE SET NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- QR CODE SCAN LOG
CREATE TABLE qr_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    guest_id INT,
    event_id INT,
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scan_result ENUM('success', 'fail'),
    device_info TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE SET NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- THEME SETTINGS TABLE
CREATE TABLE themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    theme_name VARCHAR(50) NOT NULL,     -- e.g., spring-dark
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- SYSTEM SETTINGS TABLE
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL
);

-- Insert default system settings
INSERT INTO settings (setting_key, setting_value) VALUES
('allow_guest_registration', '1'),
('default_theme', 'spring');

-- Insert example admin user
INSERT INTO users (username, email, password_hash, role, full_name, status)
VALUES ('admin', 'admin@uwindsor.ca', SHA2('admin123', 256), 'admin', 'Administrator', 'active');
