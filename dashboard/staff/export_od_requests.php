<?php
require_once 'utils/functions.php';
require_once 'config/database.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit();
}

$staff_id = $_SESSION['staff_id'];
$conn = getDBConnection();

// Get department filter
$department_id = getDepartmentFilter($conn, $staff_id);

// Get batch and section filter
$batch_section = getBatchSectionFilter($conn, $staff_id);

// Get OD requests
$sql = "SELECT od.*, s.name as student_name, s.roll_number, d.name as department_name 
        FROM od_requests od 
        JOIN students s ON od.student_id = s.id 
        JOIN departments d ON s.department_id = d.id 
        WHERE 1=1";

if ($department_id) {
    $sql .= " AND s.department_id = ?";
}

if ($batch_section['batch']) {
    $sql .= " AND s.batch = ?";
}

if ($batch_section['section']) {
    $sql .= " AND s.section = ?";
}

$sql .= " ORDER BY od.created_at DESC";

$stmt = $conn->prepare($sql);

if ($department_id) {
    $stmt->bind_param("i", $department_id);
}

if ($batch_section['batch']) {
    $stmt->bind_param("s", $batch_section['batch']);
}

if ($batch_section['section']) {
    $stmt->bind_param("s", $batch_section['section']);
}

$stmt->execute();
$result = $stmt->get_result();
$od_requests = $result->fetch_all(MYSQLI_ASSOC);

// Prepare data for export
$export_data = [];
foreach ($od_requests as $od) {
    $export_data[] = [
        'Student Name' => $od['student_name'],
        'Roll Number' => $od['roll_number'],
        'Department' => $od['department_name'],
        'Event Name' => $od['event_name'],
        'Event Type' => $od['event_type'],
        'Event Date' => formatDate($od['event_date']),
        'Tutor Approval' => $od['tutor_approval'],
        'Advisor Approval' => $od['advisor_approval'],
        'HOD Approval' => $od['hod_approval'],
        'Status' => $od['status'],
        'Certificate' => $od['certificate_path'] ? 'Yes' : 'No',
        'Created At' => formatDateTime($od['created_at']),
        'Updated At' => formatDateTime($od['updated_at'])
    ];
}

// Export to Excel
$filename = 'od_requests_' . date('Y-m-d_H-i-s') . '.xlsx';
exportToExcel($export_data, $filename);

// Download the file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
readfile($filename);
unlink($filename); // Delete the file after download 