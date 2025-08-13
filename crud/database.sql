-- Database setup for Vue.js CRUD example
-- Create database and table

CREATE DATABASE IF NOT EXISTS vue_crud;
USE vue_crud;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO users (name, email, phone) VALUES 
('John Doe', 'john@example.com', '123-456-7890'),
('Jane Smith', 'jane@example.com', '098-765-4321'),
('Mike Johnson', 'mike@example.com', '555-123-4567'),
('Mary Williams', 'mary@example.com', '444-555-6666'),
('Robert Brown', 'robert@example.com', '777-888-9999'),
('Linda Davis', 'linda@example.com', '666-777-8888'),
('David Wilson', 'david@example.com', '555-666-7777'),
('Susan Miller', 'susan@example.com', '444-555-6666'),
('James Taylor', 'james@example.com', '333-444-5555'),
('Patricia Anderson', 'patricia@example.com', '222-333-4444'),
('Christopher Thomas', 'christopher@example.com', '111-222-3333'),
('Barbara Jackson', 'barbara@example.com', '999-111-2222'),
('Daniel White', 'daniel@example.com', '888-999-1111'),
('Nancy Harris', 'nancy@example.com', '777-888-9999'),
('Matthew Martin', 'matthew@example.com', '666-777-8888'),
('Lisa Thompson', 'lisa@example.com', '555-666-7777'),
('Anthony Garcia', 'anthony@example.com', '444-555-6666'),
('Helen Martinez', 'helen@example.com', '333-444-5555'),
('Mark Robinson', 'mark@example.com', '222-333-4444'),
('Sandra Clark', 'sandra@example.com', '111-222-3333'),
('Steven Rodriguez', 'steven@example.com', '999-888-7777'),
('Donna Lewis', 'donna@example.com', '888-777-6666'),
('Paul Lee', 'paul@example.com', '777-666-5555'),
('Carol Walker', 'carol@example.com', '666-555-4444'),
('Joshua Hall', 'joshua@example.com', '555-444-3333'),
('Ruth Allen', 'ruth@example.com', '444-333-2222'),
('Kenneth Young', 'kenneth@example.com', '333-222-1111'),
('Sharon Hernandez', 'sharon@example.com', '222-111-9999'),
('Kevin King', 'kevin@example.com', '111-999-8888'),
('Betty Wright', 'betty@example.com', '999-888-7777'),
('Brian Lopez', 'brian@example.com', '888-777-6666'),
('Helen Hill', 'helen2@example.com', '777-666-5555'),
('Ronald Scott', 'ronald@example.com', '666-555-4444'),
('Dorothy Green', 'dorothy@example.com', '555-444-3333'),
('Timothy Adams', 'timothy@example.com', '444-333-2222'),
('Lisa Baker', 'lisa2@example.com', '333-222-1111'),
('Jason Gonzalez', 'jason@example.com', '222-111-9999'),
('Kimberly Nelson', 'kimberly@example.com', '111-999-8888'),
('Jeffrey Carter', 'jeffrey@example.com', '999-777-5555'),
('Amy Mitchell', 'amy@example.com', '888-666-4444'),
('Ryan Perez', 'ryan@example.com', '777-555-3333'),
('Angela Roberts', 'angela@example.com', '666-444-2222'),
('Jacob Turner', 'jacob@example.com', '555-333-1111'),
('Brenda Phillips', 'brenda@example.com', '444-222-9999'),
('Gary Campbell', 'gary@example.com', '333-111-8888'),
('Emma Parker', 'emma@example.com', '222-999-7777'),
('Nicholas Evans', 'nicholas@example.com', '111-888-6666'),
('Olivia Edwards', 'olivia@example.com', '999-777-5555'),
('Samuel Collins', 'samuel@example.com', '888-666-4444'),
('Elizabeth Stewart', 'elizabeth@example.com', '777-555-3333'),
('Alexander Sanchez', 'alexander@example.com', '666-444-2222'),
('Chloe Morris', 'chloe@example.com', '555-333-1111'),
('Jonathan Rogers', 'jonathan@example.com', '444-222-9999'),
('Megan Reed', 'megan@example.com', '333-111-8888'),
('Andrew Cook', 'andrew@example.com', '222-999-7777'),
('Stephanie Morgan', 'stephanie@example.com', '111-888-6666');



