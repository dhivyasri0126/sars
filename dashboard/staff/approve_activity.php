<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: ../../auth/staff_login.php");
    exit();
}

// Database connections
$host = "localhost";
$user = "root";
$pass = "";
$staff_db = "staff_signup";
$student_db = "student_portal";

$conn = new mysqli($host, $user, $pass, $staff_db);
$student_conn = new mysqli($host, $user, $pass, $student_db);

if ($conn->connect_error) {
    die("Staff DB Connection failed: " . $conn->connect_error);
}

if ($student_conn->connect_error) {
    die("Student DB Connection failed: " . $student_conn->connect_error);
}

// Get staff details
$staff_id = $_SESSION['staff_id'];
$sql = "SELECT * FROM staff WHERE id = $staff_id";
$result = $conn->query($sql);
$staff = $result->fetch_assoc();

// Get activity ID and action from URL
$activity_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($activity_id > 0 && ($action === 'approve' || $action === 'reject')) {
    // Get current activity status
    $check_sql = "SELECT status, file_path FROM activities WHERE id = ?";
    $check_stmt = $student_conn->prepare($check_sql);
    $check_stmt->bind_param("i", $activity_id);
    $check_stmt->execute();
    $activity = $check_stmt->get_result()->fetch_assoc();
    
    if ($action === 'reject') {
        $new_status = 'rejected';
    } else {
        // Determine new status based on current status and staff role
        switch($staff['role']) {
            case 'tutor':
                $new_status = 'tutor_approved';
                break;
            case 'advisor':
                if ($activity['status'] === 'tutor_approved') {
                    $new_status = 'advisor_approved';
                } else {
                    header("Location: activities.php?error=invalid_status");
                    exit();
                }
                break;
            case 'hod':
                if ($activity['status'] === 'hod_approved' && empty($activity['file_path'])) {
                    header("Location: activities.php?error=no_file");
                    exit();
                } else if ($activity['status'] === 'advisor_approved') {
                    $new_status = 'hod_approved';
                } else if ($activity['status'] === 'hod_approved' && !empty($activity['file_path'])) {
                    $new_status = 'approved';
                    // Update student's activity count when fully approved
                    $update_sql = "UPDATE students s 
                        JOIN activities a ON s.id = a.student_id 
                        SET s.activity_count = s.activity_count + 1 
                        WHERE a.id = ?";
                    $update_stmt = $student_conn->prepare($update_sql);
                    $update_stmt->bind_param("i", $activity_id);
                    $update_stmt->execute();
                } else {
                    header("Location: activities.php?error=invalid_status");
                    exit();
                }
                break;
            default:
                header("Location: activities.php?error=invalid_role");
                exit();
        }
    }
    
    // Update activity status
    $sql = "UPDATE activities SET status = ? WHERE id = ?";
    $stmt = $student_conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $activity_id);
    
    if ($stmt->execute()) {
        header("Location: activities.php?success=1");
        exit();
    } else {
        header("Location: activities.php?error=failed");
        exit();
    }
} else {
    header("Location: activities.php?error=invalid_request");
    exit();
}

$conn->close();
$student_conn->close();
?> 