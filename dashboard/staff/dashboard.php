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
$staff_id = $_SESSION['staff_id'];
$sql = "SELECT * FROM staff WHERE id = $staff_id";
$result = $conn_staff->query($sql);
$staff = $result->fetch_assoc();

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
$timePeriod = isset($_GET['period']) ? $_GET['period'] : 'month';
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
    'raw_data' => $debugData
], true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4F46E5;
            --secondary-color: #818CF8;
            --dark-bg: #1F2937;
            --light-bg: #F3F4F6;
        }

        .dark-mode {
            --primary-color: #6366F1;
            --secondary-color: #A5B4FC;
            --dark-bg: #111827;
            --light-bg: #1F2937;
        }

        .sidebar {
            transition: all 0.3s ease;
        }

        .stat-card {
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .activity-item {
            transition: background-color 0.3s ease;
        }

        .activity-item:hover {
            background-color: rgba(79, 70, 229, 0.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="flex h-screen">
    <!-- Sidebar -->
        <div class="sidebar w-64 bg-white dark:bg-gray-800 shadow-lg">
            <div class="p-4">
                <img src="../../assets/images/logo.png" alt="Logo" class="w-16 h-16 mx-auto">
                <h2 class="text-center text-xl font-bold mt-2 text-gray-800 dark:text-white">Staff Portal</h2>
            </div>
            <nav class="mt-6">
                <a href="dashboard.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-home w-6"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="students.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-3">Students</span>
                </a>
                <a href="activities.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-tasks w-6"></i>
                    <span class="ml-3">Activities</span>
                </a>
                <a href="approvals.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-check-circle w-6"></i>
                    <span class="ml-3">Approvals</span>
                </a>
                <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-user w-6"></i>
                    <span class="ml-3">Profile</span>
                </a>
                <a href="../../auth/logout.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-3">Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Top Navigation -->
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="flex items-center justify-between px-6 py-4">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
                    <div class="flex items-center space-x-4">
                        <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                            <i class="fas fa-moon dark:hidden"></i>
                            <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                        </button>
                        <div class="relative">
                            <button class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                                <i class="fas fa-bell text-gray-600 dark:text-gray-300"></i>
                                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                        </div>
                        <div class="relative">
                            <button class="flex items-center space-x-2">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($staff['name']); ?>" alt="Profile" class="w-8 h-8 rounded-full">
                                <span class="text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($staff['name']); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="p-6">
                <!-- Welcome Section -->
                <div class="mb-8 fade-in">
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Welcome, <?php echo htmlspecialchars($staff['name']); ?>!</h2>
                    <p class="text-gray-600 dark:text-gray-400">Here's what's happening today.</p>
            </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                                <i class="fas fa-users text-indigo-600 dark:text-indigo-400"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-600 dark:text-gray-400">Total Students</p>
                                <h3 class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo $totalStudents; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                                <i class="fas fa-clock text-yellow-600 dark:text-yellow-400"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-600 dark:text-gray-400">Pending Approvals</p>
                                <h3 class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo $pendingApprovals; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <i class="fas fa-tasks text-green-600 dark:text-green-400"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-600 dark:text-gray-400">Activities Logged</p>
                                <h3 class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo $totalActivities; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Activity Performance</h3>
                        <div class="flex items-center space-x-4">
                            <div class="flex space-x-2">
                                <span class="flex items-center text-sm">
                                    <span class="w-3 h-3 bg-green-500 rounded-full mr-1"></span>
                                    Approved
                                </span>
                                <span class="flex items-center text-sm">
                                    <span class="w-3 h-3 bg-yellow-500 rounded-full mr-1"></span>
                                    Pending
                                </span>
                                <span class="flex items-center text-sm">
                                    <span class="w-3 h-3 bg-red-500 rounded-full mr-1"></span>
                                    Rejected
                                </span>
                            </div>
                            <div class="flex space-x-2">
                                <a href="?period=day" class="px-3 py-1 text-sm rounded-full <?php echo $timePeriod === 'day' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'; ?>">
                                    Daily
                                </a>
                                <a href="?period=week" class="px-3 py-1 text-sm rounded-full <?php echo $timePeriod === 'week' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'; ?>">
                                    Weekly
                                </a>
                                <a href="?period=month" class="px-3 py-1 text-sm rounded-full <?php echo $timePeriod === 'month' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'; ?>">
                                    Monthly
                                </a>
                                <a href="?period=year" class="px-3 py-1 text-sm rounded-full <?php echo $timePeriod === 'year' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'; ?>">
                                    Yearly
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="h-80">
                        <canvas id="performanceChart"></canvas>
            </div>
                    <?php if (empty($months)): ?>
                    <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-chart-line text-4xl mb-2"></i>
                        <p>No activity data available yet. The chart will update as activities are added.</p>
                    </div>
                    <?php endif; ?>
        </div>

        <!-- Recent Activities -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Recent Activities</h3>
                        <div class="space-y-4">
                            <?php while ($row = $recentActivities->fetch_assoc()): ?>
                            <div class="activity-item p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-semibold text-gray-800 dark:text-white"><?php echo htmlspecialchars($row['student_name']); ?></h4>
                                        <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($row['event_name']); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo $row['date_from']; ?></p>
                                        <span class="px-2 py-1 text-xs rounded-full <?php 
                                            echo $row['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                ($row['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                        ?>">
                                            <?php echo ucfirst($row['status']); ?>
                            </span>
                                    </div>
                                </div>
                            </div>
                    <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        darkModeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
        });

        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }

        // Performance Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Approved',
                    data: <?php echo json_encode($approved); ?>,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Pending',
                    data: <?php echo json_encode($pending); ?>,
                    borderColor: 'rgb(234, 179, 8)',
                    backgroundColor: 'rgba(234, 179, 8, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Rejected',
                    data: <?php echo json_encode($rejected); ?>,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        cornerRadius: 4
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000',
                            font: {
                                size: 12
                            },
                            padding: 10
                        },
                        grid: {
                            color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000',
                            font: {
                                size: 12
                            },
                            padding: 10
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    </script>
</body>
</html> 
<?php $conn_staff->close(); ?>
<?php $conn->close(); ?> 