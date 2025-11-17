-- Pre-filled ESRMS SQL Dump
-- Database: esrms_db
CREATE DATABASE IF NOT EXISTS esrms_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE esrms_db;

-- departments
CREATE TABLE IF NOT EXISTS departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_code VARCHAR(20) UNIQUE NOT NULL,
    department_name VARCHAR(150) NOT NULL
);

INSERT INTO departments (department_code, department_name) VALUES
('CSC', 'Computer Science'),
('MTH', 'Mathematics');

-- users
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150),
    role ENUM('admin','secretary','hod') DEFAULT 'secretary',
    department_code VARCHAR(20),
    status ENUM('active','inactive') DEFAULT 'active',
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- DEFAULT ADMIN USER (password NOT hashed here)
-- To set a real password hash, run the included PHP helper script set_admin_password.php after importing this SQL.
INSERT INTO users (full_name, username, password_hash, email, role, department_code)
VALUES
('System Administrator', 'admin', '<REPLACE_WITH_HASH_BY_RUNNING_set_admin_password.php>', 'admin@example.com', 'admin', 'CSC');

-- courses (optional)
CREATE TABLE IF NOT EXISTS courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_title VARCHAR(150) NOT NULL,
    department_code VARCHAR(20) NOT NULL,
    credit_unit INT DEFAULT 3,
    semester ENUM('First','Second') NOT NULL
);

INSERT INTO courses (course_code, course_title, department_code, credit_unit, semester) VALUES
('CSC301', 'Algorithms', 'CSC', 3, 'First'),
('MTH201', 'Calculus II', 'MTH', 3, 'Second');

-- uploads
CREATE TABLE IF NOT EXISTS uploads (
    upload_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL,
    course_title VARCHAR(150) NOT NULL,
    lecturer_name VARCHAR(150) NOT NULL,
    department_code VARCHAR(20) NOT NULL,
    semester ENUM('First','Second') NOT NULL,
    session VARCHAR(20) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(10) NOT NULL,
    uploaded_by INT NOT NULL,
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- activity_log
CREATE TABLE IF NOT EXISTS activity_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type ENUM('UPLOAD','DOWNLOAD','VIEW','DELETE') NOT NULL,
    upload_id INT,
    action_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45)
);

-- sessions table for custom session handler
CREATE TABLE IF NOT EXISTS sessions (
    session_id VARCHAR(128) NOT NULL PRIMARY KEY,
    user_id INT DEFAULT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    session_data TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- sample upload (no actual file)
INSERT INTO uploads (course_code, course_title, lecturer_name, department_code, semester, session, file_name, file_path, file_type, uploaded_by)
VALUES ('CSC301', 'Algorithms', 'Dr. U. N. Obi', 'CSC', 'First', '2024/2025', 'sample_results.pdf', 'uploads/files/sample_results.pdf', 'PDF', 1);

-- End of dump
