<?php
session_start();

// Simple autoloader for PhpSpreadsheet and TCPDF
spl_autoload_register(function ($class) {
    $prefixes = [
        'PhpOffice\\PhpSpreadsheet\\' => __DIR__ . '/../../vendor/PhpSpreadsheet/',
        'Psr\\SimpleCache\\' => __DIR__ . '/../../vendor/Psr/SimpleCache/',
    ];
    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
});
require_once __DIR__ . '/../../vendor/tcpdf/tcpdf.php';

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: ../../auth/staff_login.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db_students = "student_portal";

// Create connection
$conn = new mysqli($host, $user, $pass, $db_students);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Build search and filter query
$where_conditions = [];
$params = [];
$types = "";

if (isset($_POST['search']) && !empty($_POST['search'])) {
    $search = "%" . $conn->real_escape_string($_POST['search']) . "%";
    $where_conditions[] = "name LIKE ?";
    $params[] = $search;
    $types .= "s";
}

if (isset($_POST['department']) && !empty($_POST['department'])) {
    $where_conditions[] = "department = ?";
    $params[] = $conn->real_escape_string($_POST['department']);
    $types .= "s";
}

if (isset($_POST['batch']) && !empty($_POST['batch'])) {
    $batch_year = (int)$_POST['batch'];
    $where_conditions[] = "year = ?";
    $params[] = $batch_year;
    $types .= "i";
}

$sql = "SELECT s.*, (SELECT COUNT(*) FROM activities a WHERE a.id = s.id) as activities FROM students s";
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY s.name";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Get selected columns
$selected_columns = isset($_POST['columns']) ? $_POST['columns'] : ['reg_number', 'name', 'department', 'academic_year', 'activities'];

// Map column names to display names
$column_names = [
    'reg_number' => 'Register No.',
    'name' => 'Name',
    'department' => 'Department',
    'academic_year' => 'Academic Year',
    'activities' => 'Activities'
];

// Get the export format
$format = isset($_POST['export_type']) ? strtolower($_POST['export_type']) : 'excel';

// Export based on type
switch ($format) {
    case 'pdf':
        exportToPDF($selected_columns, $column_names, $students);
        break;
    case 'excel':
        exportToExcel($selected_columns, $column_names, $students);
        break;
    default:
        die("Unsupported export type");
}

// Close connection
$conn->close();

/**
 * Export data to PDF
 */
function exportToPDF($selected_columns, $column_names, $students) {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Staff Portal');
    $pdf->SetTitle('Students List');
    
    // Set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Students List', 'Generated on ' . date('Y-m-d H:i:s'));
    
    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 10);
    
    // Create the table
    $html = '<table border="1" cellpadding="4">
        <thead>
            <tr>';
    
    // Add headers
    foreach ($selected_columns as $column) {
        $html .= '<th style="background-color: #f2f2f2; font-weight: bold;">' . $column_names[$column] . '</th>';
    }
    
    $html .= '</tr></thead><tbody>';
    
    // Add data rows
    foreach ($students as $student) {
        $html .= '<tr>';
        foreach ($selected_columns as $column) {
            $html .= '<td>' . htmlspecialchars($student[$column]) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    
    // Output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Close and output PDF document
    $pdf->Output('students.pdf', 'D');
    exit;
}

/**
 * Export data to Excel
 */
function exportToExcel($selected_columns, $column_names, $students) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="students_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for proper Excel encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add headers
    $headers = array(
        'Student ID',
        'Name',
        'Email',
        'Course',
        'Year Level',
        'Status'
    );
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($students as $student) {
        fputcsv($output, array(
            $student['reg_number'],
            $student['name'],
            $student['email'],
            $student['department'],
            $student['academic_year'],
            $student['activities']
        ));
    }
    
    fclose($output);
    exit();
} 