-- Blood Management System Database Schema
-- Created for BMS Final Project

-- Create database
CREATE DATABASE IF NOT EXISTS bms_db;
USE bms_db;

-- Users table (for authentication)
CREATE TABLE users (55
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'donor', 'hospital', 'blood_bank') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Donors table
CREATE TABLE donors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    date_of_birth DATE,
    last_donation_date DATE,
    eligibility_status ENUM('eligible', 'ineligible', 'pending') DEFAULT 'eligible',
    health_conditions TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Hospitals table
CREATE TABLE hospitals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    hospital_name VARCHAR(200) NOT NULL,
    address TEXT,
    phone VARCHAR(15),
    license_number VARCHAR(50),
    region VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Blood Banks table
CREATE TABLE blood_banks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    bank_name VARCHAR(200) NOT NULL,
    address TEXT,
    phone VARCHAR(15),
    license_number VARCHAR(50),
    region VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Blood Inventory table
CREATE TABLE blood_inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    blood_bank_id INT NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units_available INT DEFAULT 0,
    units_reserved INT DEFAULT 0,
    status ENUM('low', 'normal', 'urgent') DEFAULT 'normal',
    expiry_date DATE,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (blood_bank_id) REFERENCES blood_banks(id) ON DELETE CASCADE
);

-- Blood Requests table
CREATE TABLE blood_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hospital_id INT NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units_required INT NOT NULL,
    urgency ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
    patient_name VARCHAR(100),
    patient_age INT,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    requested_by INT,
    approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Donations table
CREATE TABLE donations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT NOT NULL,
    blood_bank_id INT NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units_donated INT NOT NULL,
    donation_date DATE NOT NULL,
    hemoglobin_level DECIMAL(4,2),
    blood_pressure VARCHAR(20),
    pulse_rate INT,
    temperature DECIMAL(4,2),
    notes TEXT,
    status ENUM('scheduled', 'completed', 'cancelled', 'deferred') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE,
    FOREIGN KEY (blood_bank_id) REFERENCES blood_banks(id) ON DELETE CASCADE
);

-- Appointments table
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT NOT NULL,
    blood_bank_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE,
    FOREIGN KEY (blood_bank_id) REFERENCES blood_banks(id) ON DELETE CASCADE
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reports table
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_type ENUM('inventory', 'donations', 'requests', 'donors', 'analytics') NOT NULL,
    generated_by INT NOT NULL,
    parameters JSON,
    file_path VARCHAR(255),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user only
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@bms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample data will be created by users through registration 