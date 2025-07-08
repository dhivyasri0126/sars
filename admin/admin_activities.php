<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db_staff = "staff_signup";
$conn_staff = new mysqli($host, $user, $pass, $db_staff);
$db_students = "student_portal";
$conn = new mysqli($host, $user, $pass, $db_students);
if ($conn_staff->connect_error) {
    die("Staff DB Connection failed: " . $conn_staff->connect_error);
}
if ($conn->connect_error) {
    die("Student DB Connection failed: " . $conn->connect_error);
}
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM staffs WHERE staff_id = $admin_id";
$result = $conn_staff->query($sql);
$admin = $result->fetch_assoc();

// Handle status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activity_id']) && isset($_POST['status'])) {
    $activity_id = (int)$_POST['activity_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $sql = "UPDATE activities SET status = '$status' WHERE activity_id = $activity_id";
    $conn->query($sql);
    header("Location: admin_activities.php");
    exit();
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$department_filter = isset($_GET['department']) ? $_GET['department'] : 'all';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="activities_export.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student Name', 'Department', 'Activity Name', 'Start Date', 'End Date', 'Status', 'Certificate URL']);
    
    $sql = "SELECT a.*, s.student_name, d.department_name 
            FROM activities a 
            JOIN students s ON a.student_id = s.student_id 
            JOIN departments d ON s.department_id = d.department_id 
            WHERE 1=1";
    
    if ($status_filter !== 'all') {
        $sql .= " AND a.status = '" . $conn->real_escape_string($status_filter) . "'";
    }
    if ($department_filter !== 'all') {
        $sql .= " AND d.department_id = '" . $conn->real_escape_string($department_filter) . "'";
    }
    if ($date_from) {
        $sql .= " AND a.start_date >= '" . $conn->real_escape_string($date_from) . "'";
    }
    if ($date_to) {
        $sql .= " AND a.end_date <= '" . $conn->real_escape_string($date_to) . "'";
    }
    
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['student_name'],
            $row['department_name'],
            $row['activity_name'],
            $row['start_date'],
            $row['end_date'],
            $row['status'],
            $row['certificate_url']
        ]);
    }
    fclose($output);
    exit();
}

// Get departments for filter
$departments = [];
$dept_result = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
while ($row = $dept_result->fetch_assoc()) {
    $departments[] = $row;
}

// Build query with filters
$sql = "SELECT a.*, s.student_name, d.department_name 
        FROM activities a 
        JOIN students s ON a.student_id = s.student_id 
        JOIN departments d ON s.department_id = d.department_id 
        WHERE 1=1";

if ($status_filter !== 'all') {
    $sql .= " AND a.status = '" . $conn->real_escape_string($status_filter) . "'";
}
if ($department_filter !== 'all') {
    $sql .= " AND d.department_id = '" . $conn->real_escape_string($department_filter) . "'";
}
if ($date_from) {
    $sql .= " AND a.start_date >= '" . $conn->real_escape_string($date_from) . "'";
}
if ($date_to) {
    $sql .= " AND a.end_date <= '" . $conn->real_escape_string($date_to) . "'";
}

$sql .= " ORDER BY a.start_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Activities</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <img class="h-8 w-auto" src="../assets/images/logo.png" alt="Logo">
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="admin_panel.php" class="text-gray-900 dark:text-white hover:text-gray-500 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="admin_activities.php" class="border-indigo-500 text-gray-900 dark:text-white px-3 py-2 rounded-md text-sm font-medium">Activities</a>
                            <a href="admin_students.php" class="text-gray-900 dark:text-white hover:text-gray-500 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">Students</a>
                            <a href="admin_staffs.php" class="text-gray-900 dark:text-white hover:text-gray-500 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">Staff</a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <button id="darkModeToggle" class="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white focus:outline-none">
                            <i class="fas fa-moon"></i>
                        </button>
                        <a href="../auth/admin_logout.php" class="ml-4 text-gray-900 dark:text-white hover:text-gray-500 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                        <select name="department" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="all" <?php echo $department_filter === 'all' ? 'selected' : ''; ?>>All</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept['department_id']); ?>" <?php echo $department_filter == $dept['department_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['department_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date From</label>
                        <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date To</label>
                        <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="md:col-span-4 flex justify-end space-x-4">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                        <a href="?export=csv<?php echo $status_filter !== 'all' ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $department_filter !== 'all' ? '&department=' . urlencode($department_filter) : ''; ?><?php echo $date_from ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo $date_to ? '&date_to=' . urlencode($date_to) : ''; ?>" 
                           class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            <i class="fas fa-file-export mr-2"></i>Export CSV
                        </a>
                    </div>
                </form>
            </div>

            <!-- Activities Table -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Activity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($row['student_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($row['department_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($row['activity_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo date('M d, Y', strtotime($row['start_date'])); ?> - 
                                    <?php echo date('M d, Y', strtotime($row['end_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        switch($row['status']) {
                                            case 'approved':
                                                echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                                break;
                                            case 'rejected':
                                                echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                                break;
                                            default:
                                                echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                        }
                                        ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <div class="flex space-x-2">
                                        <?php if ($row['status'] === 'pending'): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="activity_id" value="<?php echo $row['activity_id']; ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="activity_id" value="<?php echo $row['activity_id']; ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <a href="<?php echo htmlspecialchars($row['certificate_url']); ?>" target="_blank" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <i class="fas fa-file-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        
        if (localStorage.getItem('darkMode') === 'true') {
            html.classList.add('dark');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        
        darkModeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('darkMode', html.classList.contains('dark'));
            darkModeToggle.innerHTML = html.classList.contains('dark') ? 
                '<i class="fas fa-sun"></i>' : 
                '<i class="fas fa-moon"></i>';
        });
    </script>
</body>
</html> 