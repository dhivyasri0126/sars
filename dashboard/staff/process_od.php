<?php
session_start();
require_once '../../php/config.php';

// Check if user is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: ../../auth/staff_login.php");
    exit();
}

// Check if action and ID are provided
if (!isset($_GET['action']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$action = $_GET['action'];
$id = $_GET['id'];

// Validate action
if (!in_array($action, ['approve', 'reject'])) {
    header("Location: dashboard.php");
    exit();
}

// Update OD application status
$status = $action === 'approve' ? 'approved' : 'rejected';
$sql = "UPDATE od_applications SET status = ? WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) {
        // If approved, update student activity points
        if ($action === 'approve') {
            // Get OD application details
            $sql = "SELECT student_id, start_date, end_date FROM od_applications WHERE id = ?";
            if ($stmt2 = $conn->prepare($sql)) {
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                $result = $stmt2->get_result();
                $od = $result->fetch_assoc();
                
                // Calculate points (example: 10 points per day)
                $start = new DateTime($od['start_date']);
                $end = new DateTime($od['end_date']);
                $days = $end->diff($start)->days + 1;
                $points = $days * 10;
                
                // Insert into student_activities
                $sql = "INSERT INTO student_activities (student_id, activity_id, registered_at, points_earned, status) 
                        VALUES (?, 0, NOW(), ?, 'completed')";
                if ($stmt3 = $conn->prepare($sql)) {
                    $stmt3->bind_param("ii", $od['student_id'], $points);
                    $stmt3->execute();
                    $stmt3->close();
                }
                $stmt2->close();
            }
        }
    }
    $stmt->close();
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
?> 