-- Create Database
CREATE DATABASE IF NOT EXISTS image_module;
USE image_module;

-- Create Images Table
CREATE TABLE IF NOT EXISTS images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    width INT DEFAULT NULL,
    height INT DEFAULT NULL,
    upload_type ENUM('single', 'multiple') NOT NULL DEFAULT 'single',
    batch_id VARCHAR(50) DEFAULT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_upload_type (upload_type),
    INDEX idx_batch_id (batch_id),
    INDEX idx_uploaded_at (uploaded_at)
);

-- Create Upload Batches Table (for multiple uploads)
CREATE TABLE IF NOT EXISTS upload_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id VARCHAR(50) UNIQUE NOT NULL,
    total_images INT NOT NULL DEFAULT 0,
    total_size BIGINT NOT NULL DEFAULT 0,
    upload_type ENUM('single', 'multiple') NOT NULL DEFAULT 'multiple',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_batch_id (batch_id),
    INDEX idx_created_at (created_at)
);

-- Create Image Upload Log Table (for tracking)
CREATE TABLE IF NOT EXISTS upload_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action ENUM('upload', 'delete') NOT NULL,
    image_id INT DEFAULT NULL,
    batch_id VARCHAR(50) DEFAULT NULL,
    file_name VARCHAR(255),
    file_size INT DEFAULT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE SET NULL,
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Insert some sample data for testing (optional)
INSERT INTO images (original_name, file_name, file_path, file_size, mime_type, width, height, upload_type) VALUES
('sample1.jpg', 'sample1_' . UNIX_TIMESTAMP() . '.jpg', 'uploads/sample1_' . UNIX_TIMESTAMP() . '.jpg', 150000, 'image/jpeg', 800, 600, 'single'),
('sample2.png', 'sample2_' . UNIX_TIMESTAMP() . '.png', 'uploads/sample2_' . UNIX_TIMESTAMP() . '.png', 200000, 'image/png', 1024, 768, 'single');

-- Create indexes for better performance
CREATE INDEX idx_file_name ON images(file_name);
CREATE INDEX idx_mime_type ON images(mime_type);
CREATE INDEX idx_file_size ON images(file_size);