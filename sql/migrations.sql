-- SQL schema for HR system (basic)

CREATE TABLE departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  department_id INT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(191) UNIQUE,
  phone VARCHAR(30) NULL,
  address TEXT NULL,
  job_title VARCHAR(191) NULL,
  hire_date DATE NULL,
  salary DECIMAL(12,2) NULL,
  status ENUM('active','archived','on_leave') DEFAULT 'active',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NULL,
  name VARCHAR(191) NOT NULL,
  email VARCHAR(191) UNIQUE NOT NULL,
  password VARCHAR(191) NOT NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id)
);

CREATE TABLE attendances (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  date DATE NOT NULL,
  check_in TIME NULL,
  check_out TIME NULL,
  status ENUM('present','absent','late','remote') DEFAULT 'present',
  notes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id)
);

CREATE TABLE performances (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  period_start DATE NOT NULL,
  period_end DATE NOT NULL,
  score DECIMAL(5,2) DEFAULT 0,
  notes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id)
);

CREATE TABLE rewards_penalties (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  type ENUM('reward','penalty') NOT NULL,
  amount DECIMAL(12,2) NULL,
  reason TEXT NULL,
  issued_by INT NULL,
  issued_at DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id)
);

CREATE TABLE salaries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  period_start DATE NOT NULL,
  period_end DATE NOT NULL,
  base_salary DECIMAL(12,2) NOT NULL,
  adjustments DECIMAL(12,2) DEFAULT 0,
  bonuses DECIMAL(12,2) DEFAULT 0,
  deductions DECIMAL(12,2) DEFAULT 0,
  net_pay DECIMAL(12,2) NOT NULL,
  generated_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- Spatie tables (roles/permissions) will be created by their migrations
