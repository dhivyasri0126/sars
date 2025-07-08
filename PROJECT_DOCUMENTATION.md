# Student Activity Registration System (SARS) Documentation

## Project Overview
SARS is a web-based system for managing student activities, approvals, and tracking in an educational institution. The system facilitates the registration, approval, and monitoring of various student activities through different approval levels (Tutor, Advisor, and HOD).

## System Architecture

### Frontend Architecture
- **Framework**: Pure PHP with Tailwind CSS
- **UI Components**:
  - Responsive design using Tailwind CSS
  - Dark mode support
  - Interactive charts using Chart.js
  - Font Awesome icons
  - Custom animations and transitions

### Backend Architecture
- **Language**: PHP
- **Database**: MySQL
- **Session Management**: PHP native sessions
- **File Structure**:
  ```
  /
  ├── assets/
  │   ├── images/
  │   └── css/
  ├── auth/
  │   ├── staff_login.php
  │   ├── student_login.php
  │   └── logout.php
  ├── dashboard/
  │   ├── staff/
  │   │   ├── dashboard.php
  │   │   ├── students.php
  │   │   ├── activities.php
  │   │   ├── approvals.php
  │   │   └── profile.php
  │   └── student/
  │       ├── dashboard.php
  │       ├── activities.php
  │       └── profile.php
  └── includes/
      └── config.php
  ```

## Database Architecture

### Database Names
1. `student_portal` - Main database for student activities
2. `staff_signup` - Database for staff management

### Tables and Naming Conventions

#### student_portal Database
1. `students` Table
   - `id` (Primary Key)
   - `name`
   - `reg_number`
   - `department`
   - `email`
   - `password`
   - `created_at`
   - `updated_at`

2. `activities` Table
   - `id` (Primary Key)
   - `student_id` (Foreign Key)
   - `event_name`
   - `activity_type`
   - `date_from`
   - `date_to`
   - `status` (pending, tutor approved, advisor approved, hod approved, approved, rejected)
   - `file_path`
   - `created_at`
   - `updated_at`

#### staff_signup Database
1. `staff` Table
   - `id` (Primary Key)
   - `name`
   - `email`
   - `password`
   - `role` (tutor, advisor, hod)
   - `department`
   - `created_at`
   - `updated_at`

## Status Workflow
1. Student submits activity → Status: "pending"
2. Tutor reviews → Status: "tutor approved"
3. Advisor reviews → Status: "advisor approved"
4. HOD reviews → Status: "hod approved"
5. Final approval → Status: "approved"
6. If rejected at any stage → Status: "rejected"

## Color Coding System
- Blue (`bg-blue-500 text-white`): For tutor approved, advisor approved, and hod approved statuses
- Green (`bg-green-100 text-green-800`): For approved status
- Yellow (`bg-yellow-100 text-yellow-800`): For pending status
- Red (`bg-red-100 text-red-800`): For rejected status
- Gray (`bg-gray-100 text-gray-800`): For other statuses

## Authentication System
- Session-based authentication
- Role-based access control (Student, Tutor, Advisor, HOD)
- Secure password storage
- Session timeout handling

## File Upload System
- Supported file types: PDF, DOC, DOCX
- File size limits
- Secure file storage
- Unique file naming convention

## Dashboard Features
1. Staff Dashboard:
   - Activity performance charts
   - Recent activities list
   - Statistics (total students, pending approvals, total activities)
   - Department-wise activity distribution
   - Activity type distribution

2. Student Dashboard:
   - Activity submission
   - Activity status tracking
   - Profile management

## Security Measures
1. SQL Injection Prevention
   - Prepared statements
   - Input validation
   - Parameterized queries

2. XSS Prevention
   - Output escaping
   - HTML special characters encoding

3. CSRF Protection
   - Session tokens
   - Form validation

4. File Upload Security
   - File type validation
   - Size restrictions
   - Secure file naming

## Error Handling
- Custom error pages
- Logging system
- User-friendly error messages
- Database error handling

## Performance Optimization
- Database indexing
- Query optimization
- Asset minification
- Caching strategies

## Browser Compatibility
- Modern browsers support
- Responsive design
- Progressive enhancement

## Development Guidelines
1. Code Style:
   - PSR-4 autoloading
   - Consistent indentation
   - Meaningful variable names
   - Proper commenting

2. Database:
   - Use prepared statements
   - Index frequently queried columns
   - Follow naming conventions

3. Security:
   - Always validate input
   - Escape output
   - Use secure session handling
   - Implement proper access control

## Deployment Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled
- SSL certificate for HTTPS

## Maintenance
- Regular database backups
- Log rotation
- Security updates
- Performance monitoring

## Future Enhancements
1. Planned Features:
   - Email notifications
   - Mobile app integration
   - Advanced reporting
   - API development

2. Technical Improvements:
   - Migration to modern PHP framework
   - Implementation of ORM
   - Enhanced caching system
   - Real-time updates

## Troubleshooting Guide
Common issues and their solutions:
1. Database connection issues
2. File upload problems
3. Session handling
4. Permission issues
5. Performance bottlenecks

## API Documentation
(To be implemented)
- RESTful API endpoints
- Authentication methods
- Request/Response formats
- Error codes

## Version Control
- Git repository
- Branching strategy
- Commit conventions
- Release management

## [2025-05-15] Database Schema Update

### Key Changes
- **students table** (student_portal):
  - Added/updated fields: `academic_year`, `section`, `dob`, `gender`, `mobile`, `hostel_day`, `address`, `email`, `password`, `activity_count`.
  - `reg_number` is now `varchar(50)`.
- **activities table** (student_portal):
  - Added/updated fields: `reg_number`, `activity_type`, `date_from`, `date_to`, `college`, `event_type`, `event_name`, `award`, `status`, `file_path`.
  - Foreign key: `student_id` references `students.id`.
- **staff table** (staff_signup):
  - Added/updated fields: `designation`, `role` (default 'none'), `phone`, `gender`, `date_of_birth`, `created_at`, `updated_at`.

### Codebase Updates
- All PHP files and forms now use the new/renamed fields and types.
- Validation and logic updated to match DB constraints (required fields, varchar limits, enums).
- All foreign key relationships respected in logic and queries.

This documentation is a living document and should be updated as the project evolves.