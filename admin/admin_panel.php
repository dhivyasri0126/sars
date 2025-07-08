<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    // header("Location: auth/admin_login.php");
    // exit();
}

// Database connections
$host = "localhost";
$user = "root";
$pass = "";

// Connect to staff_signup database for staff details
$staff_db = "staff_signup";
$conn_staff = new mysqli($host, $user, $pass, $staff_db);

if ($conn_staff->connect_error) {
    die("Staff DB Connection failed: " . $conn_staff->connect_error);
}

// Connect to student_portal database for activities and students
$student_db = "student_portal";
$conn = new mysqli($host, $user, $pass, $student_db);

if ($conn->connect_error) {
    die("Student DB Connection failed: " . $conn->connect_error);
}

// Get staff details from staff_signup database
$sql = "SELECT * FROM staff";
$result = $conn_staff->query($sql);
$staff = $result->fetch_all(MYSQLI_ASSOC);

// Get student details from student_portal database
$sql = "SELECT * FROM students";
$result = $conn->query($sql);
$students = $result->fetch_all(MYSQLI_ASSOC);

// Get total activities count from student_portal database
$sql = "SELECT COUNT(*) as total FROM activities";
$result = $conn->query($sql);
$total_activities = $result->fetch_assoc()['total'];

// Get pending activities count
$sql = "SELECT COUNT(*) as pending FROM activities WHERE status = 'pending'";
$result = $conn->query($sql);
$pending_activities = $result->fetch_assoc()['pending'];

// Get uploaded activities count
$sql = "SELECT COUNT(*) as uploaded FROM activities WHERE file_path IS NOT NULL";
$result = $conn->query($sql);
$uploaded_activities = $result->fetch_assoc()['uploaded'];

// Get recent activities
$sql = "SELECT a.*, s.name, s.reg_number 
        FROM activities a 
        JOIN students s ON a.student_id = s.id 
        ORDER BY a.date_from DESC 
        LIMIT 5";
$result = $conn->query($sql);
$recent_activities = [];
while ($row = $result->fetch_assoc()) {
    $recent_activities[] = $row;
}

// Get department-wise activity distribution
$sql = "SELECT s.department, COUNT(*) as count 
        FROM activities a 
        JOIN students s ON a.student_id = s.id 
        GROUP BY s.department";
$result = $conn->query($sql);
$department_stats = [];
while ($row = $result->fetch_assoc()) {
    $department_stats[] = $row;
}

// Get activity type distribution
$sql = "SELECT activity_type, COUNT(*) as count 
        FROM activities 
        GROUP BY activity_type";
$result = $conn->query($sql);
$activity_type_stats = [];
while ($row = $result->fetch_assoc()) {
    $activity_type_stats[] = $row;
}

// Get statistics
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$pendingApprovals = $conn->query("SELECT COUNT(*) as count FROM activities WHERE status = 'pending'")->fetch_assoc()['count'];
// $inProgressApprovals = $conn->query("SELECT COUNT(*) as count FROM activities WHERE status != 'pending' AND status != 'rejected' AND status != 'approved'")->fetch_assoc()['count'];
$totalActivities = $conn->query("SELECT COUNT(*) as count FROM activities")->fetch_assoc()['count'];

// Get recent activities with student names
$recentActivities = $conn->query("
    SELECT a.*, s.name as student_name 
    FROM activities a 
    JOIN students s ON a.student_id = s.id 
    ORDER BY a.date_from DESC 
    LIMIT 5
");

// Get activity data for chart with dynamic time period
$timePeriod = isset($_GET['period']) ? $_GET['period'] : 'day';
$dateFormat = '%Y-%m';
$interval = '6 MONTH';
$groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';

switch ($timePeriod) {
    case 'week':
        $dateFormat = '%Y-%u';
        $interval = '12 WEEK';
        $groupBy = 'DATE_FORMAT(created_at, "%Y-%u")';
        break;
    case 'day':
        $dateFormat = '%Y-%m-%d';
        $interval = '30 DAY';
        $groupBy = 'DATE_FORMAT(created_at, "%Y-%m-%d")';
        break;
    case 'year':
        $dateFormat = '%Y';
        $interval = '5 YEAR';
        $groupBy = 'DATE_FORMAT(created_at, "%Y")';
        break;
}

$activityData = $conn->query("
    SELECT 
        $groupBy as time_period,
        COUNT(*) as count,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM activities
    WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL $interval)
    GROUP BY $groupBy
    ORDER BY time_period
");

$months = [];
$approved = [];
$pending = [];
$rejected = [];

while ($row = $activityData->fetch_assoc()) {
    $date = $row['time_period'];
    switch ($timePeriod) {
        case 'week':
            $months[] = 'Week ' . substr($date, -2) . ' ' . substr($date, 0, 4);
            break;
        case 'day':
            $months[] = date('M d, Y', strtotime($date));
            break;
        case 'year':
            $months[] = $date;
            break;
        default:
            $months[] = date('M Y', strtotime($date . '-01'));
    }
    $approved[] = (int)$row['approved'];
    $pending[] = (int)$row['pending'];
    $rejected[] = (int)$row['rejected'];
}

// If no data exists, create default data
if (empty($months)) {
    $currentDate = new DateTime();
    for ($i = 5; $i >= 0; $i--) {
        $date = clone $currentDate;
        switch ($timePeriod) {
            case 'week':
                $date->modify("-$i weeks");
                $months[] = 'Week ' . $date->format('W') . ' ' . $date->format('Y');
                break;
            case 'day':
                $date->modify("-$i days");
                $months[] = $date->format('M d, Y');
                break;
            case 'year':
                $date->modify("-$i years");
                $months[] = $date->format('Y');
                break;
            default:
                $date->modify("-$i months");
                $months[] = $date->format('M Y');
        }
        $approved[] = 0;
        $pending[] = 0;
        $rejected[] = 0;
    }
}

// Debug information
$debugData = [];
while ($row = $activityData->fetch_assoc()) {
    $debugData[] = $row;
}

// Debug output (remove this in production)
error_log("Chart Data: " . print_r([
    'months' => $months,
    'approved' => $approved,
    'pending' => $pending,
    'rejected' => $rejected,
], true));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - SARS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="flex items-center justify-between px-6 py-4">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
                    <div class="flex items-center space-x-4">
                        <button class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white">
                            <i class="fas fa-bell"></i>
                        </button>
                        <button class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Students</p>
                                <p class="text-2xl font-semibold text-gray-800 dark:text-white"><?php echo $totalStudents; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-300">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Pending Approvals</p>
                                <p class="text-2xl font-semibold text-gray-800 dark:text-white"><?php echo $pendingApprovals; ?></p>
                            </div>
                        </div>
                    </div>
<!--                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-300">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">In Progress Approvals</p>
                                <p class="text-2xl font-semibold text-gray-800 dark:text-white"><?php echo $inProgressApprovals; ?></p>
                            </div>
                        </div>
                    </div>
-->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Activities</p>
                                <p class="text-2xl font-semibold text-gray-800 dark:text-white"><?php echo $totalActivities; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300">
                                <i class="fas fa-chart-line text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Performance</p>
                                <p class="text-2xl font-semibold text-gray-800 dark:text-white">98%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Activity Trends</h2>
                        <canvas id="activityChart"></canvas>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Department Distribution</h2>
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Recent Activities</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Activity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php while ($activity = $recentActivities->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($activity['student_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($activity['event_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo date('M d, Y', strtotime($activity['date_from'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $activity['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                    ($activity['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                                <?php echo ucfirst($activity['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Approved',
                    data: <?php echo json_encode($approved); ?>,
                    borderColor: 'rgb(34, 197, 94)',
                    tension: 0.1
                }, {
                    label: 'Pending',
                    data: <?php echo json_encode($pending); ?>,
                    borderColor: 'rgb(234, 179, 8)',
                    tension: 0.1
                }, {
                    label: 'Rejected',
                    data: <?php echo json_encode($rejected); ?>,
                    borderColor: 'rgb(239, 68, 68)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Department Chart
        const departmentCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(departmentCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($department_stats, 'department')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($department_stats, 'count')); ?>,
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    </script>
</body>
</html> 