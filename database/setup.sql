-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS student_activities;
USE student_activities;

-- Create staff table
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(50) NOT NULL,
    designation VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    gender ENUM('Male', 'Female', 'Other'),
    date_of_birth DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(50) NOT NULL,
    roll_number VARCHAR(20) NOT NULL UNIQUE,
    phone VARCHAR(20),
    gender ENUM('Male', 'Female', 'Other'),
    date_of_birth DATE,
    year INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create activities table
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_name VARCHAR(200) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    points INT NOT NULL,
    department VARCHAR(50) NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES staff(id)
);

-- Create student_activities table
CREATE TABLE IF NOT EXISTS student_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    activity_id INT NOT NULL,
    status ENUM('Registered', 'Completed', 'Cancelled') DEFAULT 'Registered',
    points_earned INT DEFAULT 0,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (activity_id) REFERENCES activities(id)
);

-- Create activity_attendance table
CREATE TABLE IF NOT EXISTS activity_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present', 'Absent') NOT NULL,
    marked_by INT NOT NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (marked_by) REFERENCES staff(id)
);

-- Create OD applications table
CREATE TABLE IF NOT EXISTS od_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    reason TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Create other college events table
CREATE TABLE IF NOT EXISTS other_college_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_name VARCHAR(255) NOT NULL,
    college_name VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    description TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Insert sample staff data (password: password123)
INSERT INTO staff (name, date_of_birth, designation, department, gender, phone, email, password) VALUES
('Admin User', '1990-01-01', 'Administrator', 'Administration', 'Male', '1234567890', 'admin@example.com', '$2y$10$8KzQ8IzAF1QDMV0oV.6Y8.9X9X9X9X9X9X9X9X9X9X9X9X9X9X9X');

-- Insert sample student data (password: password123)
INSERT INTO students (name, date_of_birth, roll_number, department, year, gender, phone, email, password) VALUES
('John Doe', '2000-05-15', 'CS2020001', 'Computer Science', 3, 'Male', '9876543210', 'john.doe@example.com', '$2y$10$8KzQ8IzAF1QDMV0oV.6Y8.9X9X9X9X9X9X9X9X9X9X9X9X9X9X9X'),
('Jane Smith', '2001-03-22', 'IT2020001', 'Information Technology', 2, 'Female', '9876543211', 'jane.smith@example.com', '$2y$10$8KzQ8IzAF1QDMV0oV.6Y8.9X9X9X9X9X9X9X9X9X9X9X9X9X9X9X');

-- Insert sample activities
INSERT INTO activities (activity_name, description, start_date, end_date, points, department, created_by) VALUES
('Technical Workshop', 'A hands-on workshop on the latest programming technologies', '2024-05-01', '2024-05-03', 50, 'Computer Science', 1),
('Hackathon', 'Annual coding competition for all departments', '2024-06-15', '2024-06-17', 100, 'All', 1),
('Seminar on AI', 'Expert talk on artificial intelligence and its applications', '2024-07-10', '2024-07-10', 30, 'All', 1);

-- Add sample OD applications
INSERT INTO od_applications (student_id, reason, start_date, end_date, status) VALUES
(1, 'Participating in Technical Symposium', '2024-04-20', '2024-04-21', 'pending'),
(2, 'Attending Hackathon', '2024-04-22', '2024-04-23', 'approved');

-- Add sample other college events
INSERT INTO other_college_events (student_id, event_name, college_name, start_date, end_date, description, status) VALUES
(1, 'Tech Fest 2024', 'ABC College', '2024-04-20', '2024-04-22', 'Annual technical festival', 'approved'),
(2, 'Hackathon', 'XYZ University', '2024-04-25', '2024-04-26', '24-hour coding competition', 'pending'); 