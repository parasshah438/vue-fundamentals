-- Vue Advanced CRUD Database Schema
-- MySQL Database Setup

CREATE DATABASE IF NOT EXISTS vue_advanced_crud;
USE vue_advanced_crud;

-- Users table with all the fields from our form
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other', 'prefer-not-to-say'),
    age INT,
    marital_status ENUM('single', 'married', 'divorced', 'widowed'),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(20),
    country VARCHAR(100),
    job_title VARCHAR(150),
    company VARCHAR(150),
    department ENUM('engineering', 'marketing', 'sales', 'hr', 'finance', 'operations', 'design', 'support'),
    salary DECIMAL(10,2),
    hire_date DATE,
    employment_type ENUM('full-time', 'part-time', 'contract', 'freelance', 'internship'),
    status ENUM('active', 'pending', 'suspended', 'inactive') DEFAULT 'active',
    date_joined DATE NOT NULL,
    avatar VARCHAR(500),
    website VARCHAR(500),
    linkedin_profile VARCHAR(500),
    favorite_color VARCHAR(7) DEFAULT '#667eea',
    experience_level INT DEFAULT 0,
    preferred_work_time TIME,
    last_login DATETIME,
    skills JSON,
    work_mode ENUM('remote', 'office', 'hybrid'),
    email_notifications BOOLEAN DEFAULT TRUE,
    sms_notifications BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO users (
    first_name, last_name, email, phone, date_of_birth, gender, age, marital_status,
    address, city, state, zip_code, country, job_title, company, department, salary,
    hire_date, employment_type, status, date_joined, avatar, website, linkedin_profile,
    favorite_color, experience_level, preferred_work_time, last_login, skills, work_mode,
    email_notifications, sms_notifications, notes
) VALUES 
(
    'John', 'Doe', 'john.doe@example.com', '+1 (555) 123-4567', '1990-05-15', 'male', 33, 'married',
    '123 Main St, Apartment 4B', 'New York', 'NY', '10001', 'US', 'Senior Developer', 'Tech Corp', 'engineering', 95000.00,
    '2020-01-15', 'full-time', 'active', '2020-01-15', 'https://via.placeholder.com/50/667eea/ffffff?text=JD', 
    'https://johndoe.dev', 'https://linkedin.com/in/johndoe', '#667eea', 8, '09:00:00', '2023-12-01 14:30:00',
    '["javascript", "react", "nodejs"]', 'hybrid', TRUE, FALSE, 'Excellent performer, team lead for mobile projects.'
),
(
    'Sarah', 'Wilson', 'sarah.wilson@example.com', '+1 (555) 987-6543', '1988-08-22', 'female', 35, 'single',
    '456 Oak Avenue', 'San Francisco', 'CA', '94102', 'US', 'UX Designer', 'Design Studio', 'design', 85000.00,
    '2019-03-10', 'full-time', 'active', '2019-03-10', 'https://via.placeholder.com/50/ff6b6b/ffffff?text=SW',
    'https://sarahwilson.design', 'https://linkedin.com/in/sarahwilson', '#ff6b6b', 10, '10:00:00', '2023-12-01 09:15:00',
    '["vue", "python"]', 'remote', TRUE, TRUE, 'Creative and innovative designer with great attention to detail.'
),
(
    'Michael', 'Johnson', 'michael.johnson@example.com', '+1 (555) 456-7890', '1985-12-03', 'male', 38, 'married',
    '789 Pine Street', 'Austin', 'TX', '73301', 'US', 'Project Manager', 'Innovation Inc', 'operations', 75000.00,
    '2021-06-01', 'full-time', 'pending', '2021-06-01', 'https://via.placeholder.com/50/4ecdc4/ffffff?text=MJ',
    '', 'https://linkedin.com/in/michaeljohnson', '#4ecdc4', 12, '08:30:00', '2023-11-30 16:45:00',
    '["javascript"]', 'office', FALSE, FALSE, 'Experienced project manager with strong leadership skills.'
),
(
    'Emily', 'Chen', 'emily.chen@example.com', '+1 (555) 234-5678', '1992-03-18', 'female', 31, 'single',
    '321 Elm Street, Suite 5', 'Seattle', 'WA', '98101', 'US', 'Data Scientist', 'Analytics Pro', 'engineering', 110000.00,
    '2022-02-01', 'full-time', 'active', '2022-02-01', 'https://via.placeholder.com/50/9b59b6/ffffff?text=EC',
    'https://emilychen.ai', 'https://linkedin.com/in/emilychen', '#9b59b6', 6, '09:30:00', '2023-12-02 11:20:00',
    '["python", "javascript", "react"]', 'hybrid', TRUE, FALSE, 'Brilliant data scientist with expertise in machine learning.'
),
(
    'David', 'Rodriguez', 'david.rodriguez@example.com', '+1 (555) 345-6789', '1987-11-25', 'male', 36, 'married',
    '654 Maple Avenue', 'Denver', 'CO', '80201', 'US', 'DevOps Engineer', 'Cloud Systems', 'engineering', 98000.00,
    '2020-09-15', 'full-time', 'active', '2020-09-15', 'https://via.placeholder.com/50/f39c12/ffffff?text=DR',
    'https://davidops.com', 'https://linkedin.com/in/davidrodriguez', '#f39c12', 9, '08:00:00', '2023-12-02 08:45:00',
    '["nodejs", "python"]', 'remote', TRUE, TRUE, 'Expert in cloud infrastructure and automation.'
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_department ON users(department);
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_users_name ON users(first_name, last_name);

-- Create a view for user statistics
CREATE VIEW user_stats AS
SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_users,
    COUNT(CASE WHEN status = 'suspended' THEN 1 END) as suspended_users,
    COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_users,
    AVG(age) as average_age,
    AVG(salary) as average_salary
FROM users;
