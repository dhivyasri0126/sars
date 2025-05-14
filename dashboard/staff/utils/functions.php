<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Check if autoload.php exists before requiring it
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

function exportToExcel($data, $filename) {
    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        // Fallback to CSV if PhpSpreadsheet is not available
        $fp = fopen($filename, 'w');
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        return $filename;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Add headers
    $headers = array_keys($data[0]);
    $col = 1;
    foreach ($headers as $header) {
        $sheet->setCellValueByColumnAndRow($col, 1, $header);
        $col++;
    }
    
    // Add data
    $row = 2;
    foreach ($data as $item) {
        $col = 1;
        foreach ($item as $value) {
            $sheet->setCellValueByColumnAndRow($col, $row, $value);
            $col++;
        }
        $row++;
    }
    
    // Create Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    
    return $filename;
}

function getDepartmentFilter($conn, $staff_id) {
    $sql = "SELECT department FROM staff WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();
    
    return $staff['department'];
}

function getBatchSectionFilter($conn, $staff_id) {
    // Since batch and section are not stored in staff table, return null values
    return [
        'batch' => null,
        'section' => null
    ];
}

function canApproveActivity($conn, $staff_id, $activity_id) {
    $sql = "SELECT s.role, a.status FROM staff s 
            JOIN activities a ON a.student_id IN (
                SELECT id FROM students 
                WHERE department_id = s.department_id
            )
            WHERE s.id = ? AND a.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $staff_id, $activity_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    if (!$data) return false;
    
    switch ($data['role']) {
        case 'Tutor':
            return $data['status'] === 'pending';
        case 'Advisor':
            return $data['status'] === 'tutor_approved';
        case 'HOD':
            return $data['status'] === 'advisor_approved';
        default:
            return false;
    }
}

function checkCertificateUpload($conn, $activity_id) {
    $sql = "SELECT event_date, certificate_upload_date FROM activities WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $activity_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $activity = $result->fetch_assoc();
    
    if (!$activity['certificate_upload_date']) {
        $event_date = new DateTime($activity['event_date']);
        $now = new DateTime();
        $diff = $now->diff($event_date);
        
        if ($diff->days > 7) {
            // Update status to expired
            $sql = "UPDATE activities SET status = 'expired' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $activity_id);
            $stmt->execute();
            return false;
        }
    }
    
    return true;
}

function getActivityStatus($status) {
    $statusClasses = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'tutor_approved' => 'bg-blue-100 text-blue-800',
        'advisor_approved' => 'bg-indigo-100 text-indigo-800',
        'hod_approved' => 'bg-purple-100 text-purple-800',
        'awaiting_certificate' => 'bg-orange-100 text-orange-800',
        'confirmed' => 'bg-green-100 text-green-800',
        'expired' => 'bg-red-100 text-red-800'
    ];
    
    return $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y H:i', strtotime($datetime));
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        return false;
    }
    
    return true;
}

function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

function moveUploadedFile($file, $destination) {
    return move_uploaded_file($file['tmp_name'], $destination);
}

function canApproveOD($conn, $staff_id, $od_id) {
    $sql = "SELECT s.role, od.status FROM staff s 
            JOIN od_requests od ON od.student_id IN (
                SELECT id FROM students 
                WHERE department_id = s.department_id
            )
            WHERE s.id = ? AND od.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $staff_id, $od_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    if (!$data) return false;
    
    switch ($data['role']) {
        case 'Tutor':
            return $data['status'] === 'Pending';
        case 'Advisor':
            return $data['status'] === 'Tutor Approved';
        case 'HOD':
            return $data['status'] === 'Advisor Approved';
        default:
            return false;
    }
}

function getStaffRole($conn, $staff_id) {
    $sql = "SELECT role FROM staff WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();
    
    return $staff['role'];
}

function getStatusClass($status) {
    switch ($status) {
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'Approved':
            return 'bg-green-100 text-green-800';
        case 'Rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?> 