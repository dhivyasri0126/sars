# Student Activity Record System

A comprehensive web-based system for managing student activities, tracking participation, and generating reports.

## Features

- **User Authentication**: Separate login systems for staff and students
- **Staff Dashboard**: Modern interface with statistics and activity management
- **Student Dashboard**: Track activities, view points, and register for new activities
- **Activity Management**: Create, update, and manage activities
- **Attendance Tracking**: Record and monitor student attendance
- **Reporting**: Generate reports on student participation and activity statistics
- **Responsive Design**: Works on desktop and mobile devices

## Installation

1. **Prerequisites**:
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - Web server (Apache, Nginx, etc.)
   - XAMPP, WAMP, or similar local development environment

2. **Setup**:
   - Clone or download this repository to your web server's document root
   - Create a MySQL database named `student_activity_record`
   - Import the database schema from `database/setup.sql`
   - Configure database connection in `php/config.php` if needed

3. **Default Login Credentials**:
   - Staff: admin@example.com / password123
   - Student: john.doe@example.com / password123

## Directory Structure

```
student-activity-record-system/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── auth/
│   ├── staff_login.php
│   ├── staff_signup.php
│   ├── student_login.php
│   └── student_signup.php
├── dashboard/
│   ├── staff/
│   │   ├── dashboard.php
│   │   ├── activities.php
│   │   ├── students.php
│   │   ├── reports.php
│   │   └── settings.php
│   └── student/
│       ├── dashboard.php
│       ├── activities.php
│       ├── profile.php
│       └── settings.php
├── database/
│   └── setup.sql
├── php/
│   ├── config.php
│   ├── staff_logout.php
│   └── student_logout.php
└── README.md
```

## Security Features

- Password hashing using PHP's `password_hash()` function
- Prepared statements to prevent SQL injection
- Input sanitization
- Session management
- CSRF protection

## Usage

1. **Staff Login**: Access the staff dashboard to manage activities, students, and generate reports
2. **Student Login**: Access the student dashboard to view and register for activities
3. **Activity Management**: Create new activities, set points, and track participation
4. **Attendance**: Record student attendance for activities
5. **Reports**: Generate reports on student participation and activity statistics

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Font Awesome for icons
- Bootstrap for responsive design components
