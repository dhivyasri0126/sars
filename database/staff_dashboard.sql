-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create activities table
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

-- Create approvals table
CREATE TABLE IF NOT EXISTS approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    staff_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id),
    FOREIGN KEY (staff_id) REFERENCES staff(id)
);

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(id)
);

-- Create performance_metrics table
CREATE TABLE IF NOT EXISTS performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    metric_type VARCHAR(50) NOT NULL,
    value DECIMAL(5,2) NOT NULL,
    month INT NOT NULL,
    year INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

-- Insert sample data for testing
INSERT INTO students (name, email, department, year) VALUES
('John Doe', 'john.doe@example.com', 'Computer Science', 2023),
('Jane Smith', 'jane.smith@example.com', 'Electrical Engineering', 2023),
('Mike Johnson', 'mike.johnson@example.com', 'Mechanical Engineering', 2023);

INSERT INTO activities (student_id, activity_type, description, status) VALUES
(1, 'Project', 'Submitted final year project proposal', 'pending'),
(2, 'Internship', 'Completed summer internship at Tech Corp', 'approved'),
(3, 'Workshop', 'Attended AI workshop', 'pending');

INSERT INTO performance_metrics (student_id, metric_type, value, month, year) VALUES
(1, 'attendance', 95.00, 1, 2024),
(1, 'attendance', 92.00, 2, 2024),
(1, 'attendance', 98.00, 3, 2024),
(2, 'attendance', 88.00, 1, 2024),
(2, 'attendance', 90.00, 2, 2024),
(2, 'attendance', 95.00, 3, 2024),
(3, 'attendance', 85.00, 1, 2024),
(3, 'attendance', 87.00, 2, 2024),
(3, 'attendance', 90.00, 3, 2024); 