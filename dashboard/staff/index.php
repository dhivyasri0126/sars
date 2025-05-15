<?php
session_start();
if (!isset($_SESSION['staff_id'])) {
    header("Location: ../login.php");
    exit();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connections
$host = "localhost";
$user = "root";
$pass = "";
$db_students = "student_portal";
$db_staff = "staff_signup";

// Two connections: one for staff, one for students/activities
$conn_staff = new mysqli($host, $user, $pass, $db_staff);
$conn_students = new mysqli($host, $user, $pass, $db_students);

if ($conn_staff->connect_error) {
    die("Staff DB Connection failed: " . $conn_staff->connect_error);
}
if ($conn_students->connect_error) {
    die("Student DB Connection failed: " . $conn_students->connect_error);
}

// Get staff details
$staff_id = $_SESSION['staff_id'];
$sql = "SELECT * FROM staff WHERE id = $staff_id";
$result = $conn_staff->query($sql);
$staff = $result->fetch_assoc();

// Get staff name
$staff_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Debug: Print database tables
$tables = $conn_students->query("SHOW TABLES");
echo "<pre>Database Tables:\n";
while ($table = $tables->fetch_array()) {
    echo $table[0] . "\n";
}
echo "</pre>";

// Fetch total students count
$total_students = $conn_students->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];

// Fetch total activities count
$total_activities = $conn_students->query("SELECT COUNT(*) as count FROM activities")->fetch_assoc()['count'];

// Fetch activities with student names and status
$sql = "SELECT a.*, s.first_name, s.last_name, s.reg_number,
        CASE 
            WHEN a.file_path IS NOT NULL THEN 'Uploaded'
            WHEN a.status = 'pending' THEN 'Pending'
            ELSE 'Not Uploaded'
        END as upload_status
        FROM activities a 
        JOIN students s ON a.student_id = s.id 
        ORDER BY a.date_from DESC 
        LIMIT 10";
$activities = $conn_students->query($sql);

// Debug: Print activities query
echo "<pre>Activities Query:\n" . $sql . "\n";
if (!$activities) {
    echo "Error: " . $conn_students->error . "\n";
} else {
    echo "Number of activities found: " . $activities->num_rows . "\n";
}
echo "</pre>";

// Fetch recent uploads with student details
$recent_uploads = $conn_students->query("
    SELECT a.event_name, a.file_path, a.status, s.first_name, s.last_name, s.reg_number
    FROM activities a
    JOIN students s ON a.student_id = s.id
    WHERE a.file_path IS NOT NULL OR a.status = 'pending'
    ORDER BY a.id DESC
    LIMIT 5
");

// Debug: Print recent uploads query
echo "<pre>Recent Uploads Query:\n";
if (!$recent_uploads) {
    echo "Error: " . $conn_students->error . "\n";
} else {
    echo "Number of recent uploads found: " . $recent_uploads->num_rows . "\n";
}
echo "</pre>";

// Fetch activity type distribution
$activity_types = $conn_students->query("
    SELECT activity_type, COUNT(*) as count 
    FROM activities 
    GROUP BY activity_type
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Staff Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
<div class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar w-64 bg-white dark:bg-gray-800 shadow-lg flex flex-col">
        <div class="p-4 flex flex-col items-center">
            <img src="logo.png" alt="Logo" class="w-16 h-16 rounded-full mb-2">
            <h2 class="text-center text-xl font-bold text-gray-800 dark:text-white">Staff Portal</h2>
        </div>
        <nav class="flex-1 mt-6">
            <a href="index.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                <i class="fas fa-home w-6"></i>
                <span class="ml-3">Dashboard</span>
            </a>
            <a href="students.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                <i class="fas fa-users w-6"></i>
                <span class="ml-3">Students</span>
            </a>
            <a href="activities.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                <i class="fas fa-calendar-alt w-6"></i>
                <span class="ml-3">Activities</span>
            </a>
            <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                <i class="fas fa-user w-6"></i>
                <span class="ml-3">Profile</span>
            </a>
            <a href="logout.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span class="ml-3">Logout</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-auto">
        <!-- Top Navigation -->
        <header class="bg-white dark:bg-gray-800 shadow flex items-center justify-between px-6 py-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-800 dark:text-white"><?php echo htmlspecialchars($staff_name); ?></span>
                <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                </button>
            </div>
        </header>

        <main class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                            <i class="fas fa-users text-indigo-600 dark:text-indigo-300 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 dark:text-gray-300 text-sm">Total Students</h2>
                            <p class="text-2xl font-semibold text-gray-800 dark:text-white"><?php echo $total_students; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                            <i class="fas fa-calendar-check text-green-600 dark:text-green-300 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 dark:text-gray-300 text-sm">Total Activities</h2>
                            <p class="text-2xl font-semibold text-gray-800 dark:text-white"><?php echo $total_activities; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                            <i class="fas fa-file-upload text-blue-600 dark:text-blue-300 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 dark:text-gray-300 text-sm">Recent Uploads</h2>
                            <p class="text-2xl font-semibold text-gray-800 dark:text-white"><?php echo $recent_uploads->num_rows; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Recent Activities</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php while($activity = $activities->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($activity['reg_number']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($activity['event_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($activity['activity_type']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo date('M d, Y', strtotime($activity['date_from'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($activity['upload_status'] == 'Uploaded'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <i class="fas fa-check mr-1"></i> Uploaded
                                            </span>
                                        <?php elseif($activity['upload_status'] == 'Pending'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                <i class="fas fa-clock mr-1"></i> Pending
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                <i class="fas fa-times mr-1"></i> Not Uploaded
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Recent Uploads & Pending Activities</h2>
                    <div class="space-y-4">
                        <?php while($upload = $recent_uploads->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($upload['event_name']); ?></h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo htmlspecialchars($upload['first_name'] . ' ' . $upload['last_name']); ?> 
                                    (<?php echo htmlspecialchars($upload['reg_number']); ?>)
                                </p>
                            </div>
                            <?php if($upload['file_path']): ?>
                                <a href="view_file.php?file=<?php echo urlencode($upload['file_path']); ?>" 
                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                   target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            <?php else: ?>
                                <span class="text-yellow-600 dark:text-yellow-400">
                                    <i class="fas fa-clock"></i> Pending
                                </span>
                            <?php endif; ?>
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
    const html = document.documentElement;
    if (localStorage.getItem('darkMode') === 'true') {
        html.classList.add('dark');
    }
    darkModeToggle.addEventListener('click', () => {
        html.classList.toggle('dark');
        localStorage.setItem('darkMode', html.classList.contains('dark'));
    });
</script>
</body>
</html>

<?php
$conn_staff->close();
$conn_students->close();
?> 