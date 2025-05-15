<?php
session_start();

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

// Get unique departments and years for filters
$departments = [
    'CSE' => 'Computer Science Engineering',
    'ECE' => 'Electronics and Communication Engineering',
    'EEE' => 'Electrical and Electronics Engineering',
    'IT' => 'Information Technology',
    'MECH' => 'Mechanical Engineering',
    'CSE(CS)' => 'Computer Science Engineering (CS)',
    'CSBS' => 'Computer Science and Business Systems',
    'BME' => 'Biomedical Engineering'
];

$batches = [
    '2025-2029' => 2025,
    '2024-2028' => 2024,
    '2023-2027' => 2023,
    '2022-2026' => 2022,
    '2021-2025' => 2021
];

$event_types = ['Technical', 'Non-Technical'];

// Build search and filter query
$where_conditions = [];
$params = [];
$types = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $conn_students->real_escape_string($_GET['search']) . "%";
    $where_conditions[] = "(a.title LIKE ? OR s.name LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}

if (isset($_GET['department']) && !empty($_GET['department'])) {
    $where_conditions[] = "s.department = ?";
    $params[] = $conn_students->real_escape_string($_GET['department']);
    $types .= "s";
}

if (isset($_GET['batch']) && !empty($_GET['batch'])) {
    $batch_year = (int)$_GET['batch'];
    $where_conditions[] = "s.year = ?";
    $params[] = $batch_year;
    $types .= "i";
}

if (isset($_GET['event_type']) && !empty($_GET['event_type'])) {
    $where_conditions[] = "a.event_type = ?";
    $params[] = $conn_students->real_escape_string($_GET['event_type']);
    $types .= "s";
}

$sql = "SELECT a.*, s.name as student_name, s.reg_number, s.department, s.academic_year, 
        COALESCE(a.event_type, 'Not Specified') as event_type 
        FROM activities a 
        JOIN students s ON a.id = s.id";

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY a.date_from DESC";

$stmt = $conn_students->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$activities = [];
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities - Staff Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Activities</h1>
                    <div class="flex items-center space-x-4">
                        <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                            <i class="fas fa-moon dark:hidden"></i>
                            <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                        </button>
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
                <!-- Export Buttons -->
                <div class="flex space-x-4 mb-4">
                    <button type="button" onclick="openExportModal('pdf')" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"><i class="fas fa-file-pdf mr-2"></i>Export to PDF</button>
                    <button type="button" onclick="openExportModal('excel')" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"><i class="fas fa-file-excel mr-2"></i>Export to Excel</button>
                </div>
                <!-- Export Modal -->
                <div id="exportModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md">
                        <h2 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Select columns to export</h2>
                        <form id="exportForm" method="post" action="export_activities.php" target="_blank">
                            <input type="hidden" name="export_type" id="exportTypeInput" value="">
                            <div class="grid grid-cols-1 gap-2 mb-4">
                                <label><input type="checkbox" name="columns[]" value="student_name" checked> Student Name</label>
                                <label><input type="checkbox" name="columns[]" value="reg_number" checked> Register Number</label>
                                <label><input type="checkbox" name="columns[]" value="department" checked> Department</label>
                                <label><input type="checkbox" name="columns[]" value="academic_year" checked> Academic Year</label>
                                <label><input type="checkbox" name="columns[]" value="title" checked> Activity</label>
                                <label><input type="checkbox" name="columns[]" value="event_type" checked> Event Type</label>
                                <label><input type="checkbox" name="columns[]" value="date" checked> Date</label>
                                <label><input type="checkbox" name="columns[]" value="status" checked> Status</label>
                            </div>
                            <div class="flex justify-end space-x-2">
                                <button type="button" onclick="closeExportModal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 text-gray-800">Cancel</button>
                                <button type="submit" class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">Export</button>
                            </div>
                        </form>
                    </div>
                </div>
                <script>
                function openExportModal(type) {
                    document.getElementById('exportTypeInput').value = type;
                    document.getElementById('exportModal').classList.remove('hidden');
                }
                function closeExportModal() {
                    document.getElementById('exportModal').classList.add('hidden');
                }
                // Close modal on outside click
                window.onclick = function(event) {
                    var modal = document.getElementById('exportModal');
                    if (event.target === modal) {
                        closeExportModal();
                    }
                }
                </script>
                <!-- Search and Filter Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                    <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="search">Search</label>
                            <input type="text" id="search" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                                   class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                   placeholder="Activity title or Student name">
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="department">Department</label>
                            <select id="department" name="department" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $code => $name): ?>
                                <option value="<?php echo $code; ?>" <?php echo (isset($_GET['department']) && $_GET['department'] === $code) ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="batch">Batch</label>
                            <select id="batch" name="batch" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">All Batches</option>
                                <?php foreach ($batches as $batch_name => $batch_year): ?>
                                <option value="<?php echo $batch_year; ?>" <?php echo (isset($_GET['batch']) && $_GET['batch'] == $batch_year) ? 'selected' : ''; ?>>
                                    <?php echo $batch_name; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="event_type">Event Type</label>
                            <select id="event_type" name="event_type" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">All Events</option>
                                <?php foreach ($event_types as $event): ?>
                                <option value="<?php echo $event; ?>" <?php echo (isset($_GET['event_type']) && $_GET['event_type'] === $event) ? 'selected' : ''; ?>>
                                    <?php echo $event; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 w-full">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Activities Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Activity List</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Batch</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Activity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($activities as $activity): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($activity['student_name']); ?></div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($activity['reg_number']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($activity['department']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo $activity['academic_year'] . '-' . ($activity['academic_year'] + 4); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($activity['event_name']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($activity['event_type']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo date('d M Y', strtotime($activity['date_from'])); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $activity['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                    ($activity['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                                <?php echo ucfirst($activity['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <a href="edit_activity.php?id=<?php echo htmlspecialchars($activity['id']); ?>" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                                <a href="delete_activity.php?id=<?php echo htmlspecialchars($activity['id']); ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this activity?');">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
    </script>
</body>
</html> 
<?php $conn_staff->close(); $conn_students->close(); ?> 