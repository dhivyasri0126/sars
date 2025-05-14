-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    batch VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create activities table
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    activity_date DATE NOT NULL,
    description TEXT,
    points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Create staff table
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(50) NOT NULL,
    designation VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data for testing
INSERT INTO students (roll_number, name, department, batch, email) VALUES
('CS2021001', 'John Doe', 'Computer Science', '2021', 'john.doe@example.com'),
('CS2021002', 'Jane Smith', 'Computer Science', '2021', 'jane.smith@example.com'),
('ME2021001', 'Robert Johnson', 'Mechanical Engineering', '2021', 'robert.johnson@example.com'),
('EE2021001', 'Emily Davis', 'Electrical Engineering', '2021', 'emily.davis@example.com');

INSERT INTO activities (student_id, activity_type, activity_date, description, points) VALUES
(1, 'Seminar', '2023-01-15', 'Attended AI and Machine Learning seminar', 10),
(1, 'Workshop', '2023-02-20', 'Completed Python programming workshop', 15),
(2, 'Competition', '2023-03-10', 'Won first place in coding competition', 20),
(3, 'Conference', '2023-04-05', 'Presented research paper at national conference', 25),
(4, 'Internship', '2023-05-15', 'Completed summer internship at tech company', 30);

-- Insert sample staff data
INSERT INTO staff (staff_id, name, email, password, department, designation) VALUES
('STAFF001', 'Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administration', 'Administrator');
-- Note: The password hash above is for 'password' 