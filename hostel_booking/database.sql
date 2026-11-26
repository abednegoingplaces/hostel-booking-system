-- Hostel Booking System (plain PHP version): database schema
-- Import this in phpMyAdmin, then import seed.sql for demo data.

CREATE DATABASE IF NOT EXISTS hostel_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hostel_booking;

DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS hostels;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,              -- password_hash() output, never plain text
    role       ENUM('warden', 'student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE hostels (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    location    VARCHAR(150) NOT NULL,
    gender      ENUM('male', 'female', 'mixed') NOT NULL,
    description TEXT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rooms (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hostel_id          INT UNSIGNED NOT NULL,
    room_number        VARCHAR(20) NOT NULL,
    capacity           TINYINT UNSIGNED NOT NULL,
    price_per_semester DECIMAL(10, 2) NOT NULL,
    photo_path         VARCHAR(255) NULL,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_room_per_hostel (hostel_id, room_number),
    CONSTRAINT fk_rooms_hostel FOREIGN KEY (hostel_id) REFERENCES hostels (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    room_id    INT UNSIGNED NOT NULL,
    semester   VARCHAR(20) NOT NULL,
    status     ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_room_semester_status (room_id, semester, status),
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES rooms (id) ON DELETE CASCADE
) ENGINE=InnoDB;
