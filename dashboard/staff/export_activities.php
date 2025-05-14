<?php
session_start();

// Simple autoloader for PhpSpreadsheet and PhpWord
spl_autoload_register(function ($class) {
    $prefixes = [
        'PhpOffice\\PhpSpreadsheet\\' => __DIR__ . '/../../vendor/PhpSpreadsheet/',
        'PhpOffice\\PhpWord\\' => __DIR__ . '/../../vendor/PhpWord/',
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

// Check if user is logged in
if (!isset($_SESSION['staff_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../../auth/staff_login.php");
    exit;
}

// Database connection parameters
$host = "localhost";
$user = "root";
$pass = "";
$db = "student_portal"; // You may need to adjust this to your actual database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process filter parameters
$where_clause = "1=1"; // Default to show all records

if (!empty($_POST['department'])) {
    $dept = $conn->real_escape_string($_POST['department']);
    $where_clause .= " AND department = '$dept'";
}

if (!empty($_POST['batch'])) {
    $batch = $conn->real_escape_string($_POST['batch']);
    $where_clause .= " AND batch = '$batch'";
}

if (!empty($_POST['roll_number'])) {
    $roll = $conn->real_escape_string($_POST['roll_number']);
    $where_clause .= " AND roll_number LIKE '%$roll%'";
}

if (!empty($_POST['activity_type'])) {
    $activity = $conn->real_escape_string($_POST['activity_type']);
    $where_clause .= " AND activity_type = '$activity'";
}

if (!empty($_POST['date_from'])) {
    $date_from = $conn->real_escape_string($_POST['date_from']);
    $where_clause .= " AND activity_date >= '$date_from'";
}

if (!empty($_POST['date_to'])) {
    $date_to = $conn->real_escape_string($_POST['date_to']);
    $where_clause .= " AND activity_date <= '$date_to'";
}

// Query to get student activities with filters
$query = "SELECT s.reg_number, s.name, s.department, s.academic_year, 
          a.activity_type, a.date_from
          FROM students s
          JOIN activities a ON s.id = a.id
          WHERE $where_clause
          ORDER BY a.date_from DESC";

$result = $conn->query($query);

// Check if export type is specified
if (!isset($_POST['export_type'])) {
    die("Export type not specified");
}

$export_type = $_POST['export_type'];

// Prepare data for export
$data = [];
$headers = ['Roll Number', 'Name', 'Department', 'Batch', 'Activity Type', 'Date', 'Description', 'Points'];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['reg_number'],
            $row['name'],
            $row['department'],
            $row['academic_year'],
            $row['activity_type'],
            $row['activity_date'],
            $row['description'],
            $row['points']
        ];
    }
}

// Export based on type
switch ($export_type) {
    case 'pdf':
        exportToPDF($headers, $data);
        break;
    case 'excel':
        exportToExcel($headers, $data);
        break;
    case 'word':
        exportToWord($headers, $data);
        break;
    default:
        die("Unsupported export type");
}

// Close connection
$conn->close();

/**
 * Export data to PDF
 */
function exportToPDF($headers, $data) {
    // TCPDF is already required above
    // Create new PDF document
    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Staff Portal');
    $pdf->SetTitle('Student Activities Report');
    
    // Set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Student Activities Report', 'Generated on ' . date('Y-m-d H:i:s'));
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 10);
    
    // Create the table
    $html = '<table border="1" cellpadding="4">
                <tr>';
    
    // Add headers
    foreach ($headers as $header) {
        $html .= '<th style="background-color: #f2f2f2; font-weight: bold;">' . $header . '</th>';
    }
    
    $html .= '</tr>';
    
    // Add data rows
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . $cell . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    
    // Output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Close and output PDF document
    $pdf->Output('student_activities.pdf', 'D');
    exit;
}

/**
 * Export data to Excel
 */
function exportToExcel($headers, $data) {
    // PhpSpreadsheet is already required above
    // Create new Spreadsheet object
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Add headers
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->getFont()->setBold(true);
        $sheet->getStyle($col . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
        $col++;
    }
    
    // Add data
    $row = 2;
    foreach ($data as $rowData) {
        $col = 'A';
        foreach ($rowData as $cell) {
            $sheet->setCellValue($col . $row, $cell);
            $col++;
        }
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="student_activities.xlsx"');
    header('Cache-Control: max-age=0');
    
    // Create Excel file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

/**
 * Export data to Word
 */
function exportToWord($headers, $data) {
    // PhpWord is already required above
    // Create new Word document
    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();
    
    // Add title
    $section->addText('Student Activities Report', ['bold' => true, 'size' => 16]);
    $section->addText('Generated on ' . date('Y-m-d H:i:s'), ['italic' => true]);
    $section->addTextBreak(1);
    
    // Create table
    $table = $section->addTable(['borderSize' => 1, 'borderColor' => '000000']);
    
    // Add header row
    $table->addRow();
    foreach ($headers as $header) {
        $table->addCell(2000, ['bgColor' => 'F2F2F2'])->addText($header, ['bold' => true]);
    }
    
    // Add data rows
    foreach ($data as $row) {
        $table->addRow();
        foreach ($row as $cell) {
            $table->addCell(2000)->addText($cell);
        }
    }
    
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment;filename="student_activities.docx"');
    header('Cache-Control: max-age=0');
    
    // Save file
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');
    exit;
}
?> 