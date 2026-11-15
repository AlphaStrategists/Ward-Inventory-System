-- ============================================================================
-- WARD DRUG INVENTORY SYSTEM - BULLETPROOF FINAL SCHEMA
-- ============================================================================

DROP DATABASE IF EXISTS ward_48_inventory;

CREATE DATABASE ward_48_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ward_48_inventory;

-- ============================================================================
-- 1. Roles & Permissions 
-- ============================================================================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================================
-- 2. Master / Lookup Tables 
-- ============================================================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE medicine_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE wards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ward_number VARCHAR(20) NOT NULL UNIQUE,
    ward_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(150) NOT NULL,
    contact_info VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================================
-- 3. Users Table
-- ============================================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    ward_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================================
-- 4. Patients & Admissions 
-- ============================================================================
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE admissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    bht_no VARCHAR(50) NOT NULL UNIQUE,
    ward_id INT NOT NULL,
    admit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Admitted', 'Discharged', 'Transferred') DEFAULT 'Admitted',
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT,
    FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================================
-- 5. Medicines & Batches 
-- ============================================================================
CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    category_id INT NOT NULL,
    unit_id INT NOT NULL,     
    form_id INT NOT NULL,     
    strength VARCHAR(50),     
    is_controlled TINYINT(1) DEFAULT 0, 
    min_level INT NOT NULL DEFAULT 10,
    warning_limit INT NOT NULL DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    FOREIGN KEY (form_id) REFERENCES medicine_forms(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE medicine_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    supplier_id INT,
    batch_no VARCHAR(100) NOT NULL,
    expiry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uk_medicine_batch UNIQUE (medicine_id, batch_no),
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    INDEX idx_medicine_expiry (medicine_id, expiry_date) 
) ENGINE=InnoDB;

-- ============================================================================
-- 6. Stock Receipts Table (Incoming Stock)
-- ============================================================================
CREATE TABLE stock_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    ward_id INT NOT NULL,
    quantity_received INT NOT NULL CHECK (quantity_received > 0),
    received_by INT NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES medicine_batches(id) ON DELETE RESTRICT,
    FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE RESTRICT,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================================
-- 7. Immutable Stock Ledger 
-- ============================================================================
CREATE TABLE stock_ledger (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    ward_id INT NOT NULL,
    transaction_type ENUM('RECEIPT', 'DISPENSATION', 'ADJUSTMENT') NOT NULL,
    quantity INT NOT NULL, 
    source_table VARCHAR(50) NOT NULL, 
    source_id INT NOT NULL,            
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT chk_ledger_sign CHECK (
        (transaction_type = 'RECEIPT' AND quantity > 0) OR 
        (transaction_type = 'DISPENSATION' AND quantity < 0) OR 
        (transaction_type = 'ADJUSTMENT')
    ),
    
    CONSTRAINT uk_ledger_source UNIQUE (source_table, source_id),

    FOREIGN KEY (batch_id) REFERENCES medicine_batches(id) ON DELETE RESTRICT,
    FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE RESTRICT,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_ledger_batch (batch_id),
    INDEX idx_ledger_batch_date (batch_id, created_at)
) ENGINE=InnoDB;

-- ============================================================================
-- 8. Dispensations 
-- ============================================================================
CREATE TABLE dispensations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admission_id INT NOT NULL,
    batch_id INT NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    qty_given INT NOT NULL CHECK (qty_given > 0),
    dosage VARCHAR(100) NOT NULL,
    usage_time VARCHAR(100),
    issued_by INT NOT NULL,
    witnessed_by INT DEFAULT NULL, 
    FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE RESTRICT,
    FOREIGN KEY (batch_id) REFERENCES medicine_batches(id) ON DELETE RESTRICT,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (witnessed_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================================
-- 9. Adjustments (Medicines)
-- ============================================================================
CREATE TABLE medicine_stock_adjustments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    ward_id INT NOT NULL, 
    adjustment_type ENUM('EXPIRY', 'DAMAGED', 'LOST', 'COUNT_CORRECTION', 'RETURN') NOT NULL,
    quantity INT NOT NULL, 
    reason TEXT NOT NULL,
    adjusted_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES medicine_batches(id) ON DELETE RESTRICT,
    FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE RESTRICT,
    FOREIGN KEY (adjusted_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================================
-- 10. DATABASE TRIGGERS (Auto-Updating the Ledger)
-- ============================================================================
DELIMITER $$

CREATE TRIGGER trg_receipt_ledger
AFTER INSERT ON stock_receipts
FOR EACH ROW
BEGIN
    INSERT INTO stock_ledger 
        (batch_id, ward_id, transaction_type, quantity, source_table, source_id, recorded_by)
    VALUES 
        (NEW.batch_id, NEW.ward_id, 'RECEIPT', NEW.quantity_received, 'stock_receipts', NEW.id, NEW.received_by);
END$$

CREATE TRIGGER trg_dispensation_ledger
AFTER INSERT ON dispensations
FOR EACH ROW
BEGIN
    DECLARE v_ward_id INT;
    SELECT ward_id INTO v_ward_id FROM admissions WHERE id = NEW.admission_id;

    INSERT INTO stock_ledger 
        (batch_id, ward_id, transaction_type, quantity, source_table, source_id, recorded_by)
    VALUES 
        (NEW.batch_id, v_ward_id, 'DISPENSATION', -NEW.qty_given, 'dispensations', NEW.id, NEW.issued_by);
END$$

CREATE TRIGGER trg_adjustment_ledger
AFTER INSERT ON medicine_stock_adjustments
FOR EACH ROW
BEGIN
    INSERT INTO stock_ledger 
        (batch_id, ward_id, transaction_type, quantity, source_table, source_id, recorded_by)
    VALUES 
        (NEW.batch_id, NEW.ward_id, 'ADJUSTMENT', NEW.quantity, 'medicine_stock_adjustments', NEW.id, NEW.adjusted_by);
END$$

DELIMITER ;

-- ============================================================================
-- 11. General Items & Support Tables 
-- ============================================================================
CREATE TABLE general_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    category_id INT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE general_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    ward_id INT NOT NULL,
    quantity_received INT DEFAULT 0,
    quantity_issued INT DEFAULT 0,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    recorded_by INT NOT NULL,
    FOREIGN KEY (item_id) REFERENCES general_items(id) ON DELETE RESTRICT,
    FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE RESTRICT,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE general_stock_adjustments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    ward_id INT NOT NULL, 
    adjustment_type ENUM('DAMAGED', 'LOST', 'COUNT_CORRECTION', 'RETURN') NOT NULL,
    quantity INT NOT NULL, 
    reason TEXT NOT NULL,
    adjusted_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES general_items(id) ON DELETE RESTRICT,
    FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE RESTRICT,
    FOREIGN KEY (adjusted_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ward_id INT NOT NULL,
    req_no VARCHAR(50) NOT NULL UNIQUE,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    requested_by INT NOT NULL,
    ms_approval_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    approved_by INT,
    remark TEXT,
    FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE RESTRICT,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    qty_requested INT NOT NULL,
    qty_issued INT DEFAULT 0,
    remark TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE dispensing_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_title VARCHAR(150) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    generated_by INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_performed TEXT NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
