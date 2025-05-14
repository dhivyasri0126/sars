CREATE TABLE IF NOT EXISTS od_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    event_name VARCHAR(255) NOT NULL,
    event_type ENUM('Technical', 'Non-Technical', 'Both') NOT NULL,
    event_date DATE NOT NULL,
    tutor_approval ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    advisor_approval ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    hod_approval ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    certificate_path VARCHAR(255),
    certificate_upload_date DATETIME,
    status ENUM('Pending', 'Tutor Approved', 'Advisor Approved', 'HOD Approved', 'Awaiting Certificate', 'Confirmed', 'Expired') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
); 