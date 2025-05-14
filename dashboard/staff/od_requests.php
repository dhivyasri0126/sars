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

// Handle approval submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_od'])) {
    $od_id = $_POST['od_id'];
    $comment = sanitizeInput($_POST['comment']);
    
    if (canApproveOD($conn, $staff_id, $od_id)) {
        $role = getStaffRole($conn, $staff_id);
        $approval_field = strtolower($role) . '_approval';
        
        $sql = "UPDATE od_requests SET $approval_field = 'Approved', status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        switch ($role) {
            case 'Tutor':
                $new_status = 'Tutor Approved';
                break;
            case 'Advisor':
                $new_status = 'Advisor Approved';
                break;
            case 'HOD':
                $new_status = 'HOD Approved';
                break;
        }
        
        $stmt->bind_param("si", $new_status, $od_id);
        $stmt->execute();
        
        // Add approval comment
        $sql = "INSERT INTO od_comments (od_id, staff_id, comment) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $od_id, $staff_id, $comment);
        $stmt->execute();
    }
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OD Requests - Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">OD Requests</h1>
            <button onclick="exportToExcel()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Export to Excel
            </button>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Advisor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HOD</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($od_requests as $od): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $od['student_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $od['roll_number']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $od['event_name']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo $od['event_type']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo formatDate($od['event_date']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusClass($od['tutor_approval']); ?>">
                                        <?php echo $od['tutor_approval']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusClass($od['advisor_approval']); ?>">
                                        <?php echo $od['advisor_approval']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusClass($od['hod_approval']); ?>">
                                        <?php echo $od['hod_approval']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getActivityStatus($od['status']); ?>">
                                        <?php echo $od['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($od['certificate_path']): ?>
                                        <a href="<?php echo $od['certificate_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900">
                                            View Certificate
                                        </a>
                                    <?php else: ?>
                                        No Certificate
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if (canApproveOD($conn, $staff_id, $od['id'])): ?>
                                        <button onclick="showApprovalModal(<?php echo $od['id']; ?>)" class="text-indigo-600 hover:text-indigo-900">
                                            Approve
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Approval Modal -->
    <div id="approvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900">Approve OD Request</h3>
                <form id="approvalForm" method="POST" class="mt-4">
                    <input type="hidden" name="od_id" id="od_id">
                    <div class="mt-2">
                        <label class="block text-sm font-medium text-gray-700">Comment</label>
                        <textarea name="comment" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" onclick="hideApprovalModal()" class="mr-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" name="approve_od" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                            Submit Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function showApprovalModal(odId) {
            document.getElementById('od_id').value = odId;
            document.getElementById('approvalModal').classList.remove('hidden');
        }
        
        function hideApprovalModal() {
            document.getElementById('approvalModal').classList.add('hidden');
        }
        
        function exportToExcel() {
            window.location.href = 'export_od_requests.php';
        }
    </script>
</body>
</html> 