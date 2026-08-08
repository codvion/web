CREATE DATABASE IF NOT EXISTS erp_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE erp_system;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS salaries;
DROP TABLE IF EXISTS bills;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS users;

CREATE TABLE clients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    company VARCHAR(160) NULL,
    email VARCHAR(160) NULL,
    phone VARCHAR(50) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    status ENUM('planning', 'active', 'hold', 'completed', 'cancelled') NOT NULL DEFAULT 'planning',
    budget DECIMAL(14,2) NOT NULL DEFAULT 0,
    start_date DATE NULL,
    deadline DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NULL,
    invoice_number VARCHAR(60) NOT NULL UNIQUE,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'paid', 'overdue') NOT NULL DEFAULT 'pending',
    due_date DATE NULL,
    paid_at DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoices_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoices_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
);

CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT UNSIGNED NOT NULL,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    paid_on DATE NOT NULL,
    method VARCHAR(80) NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

CREATE TABLE expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    category VARCHAR(90) NULL,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    expense_date DATE NOT NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE salaries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_name VARCHAR(120) NOT NULL,
    role VARCHAR(100) NULL,
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    salary_month DATE NOT NULL,
    status ENUM('pending', 'paid') NOT NULL DEFAULT 'pending',
    paid_on DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    bill_type ENUM('rent', 'electricity', 'internet', 'software', 'other') NOT NULL DEFAULT 'other',
    amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    bill_month DATE NOT NULL,
    status ENUM('pending', 'paid', 'overdue') NOT NULL DEFAULT 'pending',
    due_date DATE NULL,
    paid_on DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'accountant', 'staff') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO clients (name, company, email, phone, status) VALUES
('Ali Khan', 'Apex Builders', 'ali@apex.test', '+92 300 1111111', 'active'),
('Sara Ahmed', 'Bright Digital', 'sara@bright.test', '+92 300 2222222', 'active'),
('Hamza Malik', 'North Traders', 'hamza@north.test', '+92 300 3333333', 'inactive');

INSERT INTO projects (client_id, name, status, budget, start_date, deadline) VALUES
(1, 'Inventory Management Portal', 'active', 850000, '2026-05-01', '2026-07-15'),
(2, 'Marketing Website', 'planning', 280000, '2026-05-10', '2026-06-20'),
(1, 'Mobile App Phase 2', 'completed', 1200000, '2026-02-01', '2026-04-30');

INSERT INTO invoices (client_id, project_id, invoice_number, amount, status, due_date, paid_at) VALUES
(1, 1, 'INV-202605-001', 350000, 'pending', '2026-05-25', NULL),
(2, 2, 'INV-202605-002', 140000, 'pending', '2026-05-30', NULL),
(1, 3, 'INV-202604-003', 1200000, 'paid', '2026-04-15', '2026-04-12');

INSERT INTO payments (invoice_id, amount, paid_on, method, notes) VALUES
(3, 1200000, '2026-04-12', 'Bank Transfer', 'Paid in full');

INSERT INTO expenses (title, category, amount, expense_date, notes) VALUES
('Office supplies', 'Operations', 25000, '2026-05-05', 'Stationery and basic supplies'),
('Team lunch', 'Staff Welfare', 18000, '2026-05-08', 'Monthly lunch'),
('Laptop repair', 'Maintenance', 35000, '2026-04-22', 'Developer machine');

INSERT INTO salaries (employee_name, role, amount, salary_month, status, paid_on) VALUES
('Usman Raza', 'Developer', 180000, '2026-05-01', 'pending', NULL),
('Maira Shah', 'Designer', 150000, '2026-05-01', 'paid', '2026-05-03'),
('Noman Ali', 'Project Manager', 220000, '2026-05-01', 'paid', '2026-05-03');

INSERT INTO bills (title, bill_type, amount, bill_month, status, due_date, paid_on) VALUES
('Office Rent', 'rent', 200000, '2026-05-01', 'pending', '2026-05-20', NULL),
('Electricity Bill', 'electricity', 42000, '2026-05-01', 'paid', '2026-05-12', '2026-05-10'),
('Internet', 'internet', 18000, '2026-05-01', 'paid', '2026-05-08', '2026-05-06');

INSERT INTO users (username, name, email, password_hash, role) VALUES
('admin', 'Admin User', 'admin@example.com', '$2y$10$xlJ/RTMm0Czx6FqI.0Aeh.M6zP1DC7If2j5IqKG2A1Y8309gcNsLO', 'admin');
