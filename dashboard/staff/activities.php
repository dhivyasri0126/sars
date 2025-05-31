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
    $search = "%" . $conn->real_escape_string($_GET['search']) . "%";
    $where_conditions[] = "(a.event_name LIKE ? OR s.name LIKE ? OR s.reg_number LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "sss";
}

if (isset($_GET['department']) && !empty($_GET['department'])) {
    $where_conditions[] = "s.department = ?";
    $params[] = $conn->real_escape_string($_GET['department']);
    $types .= "s";
}

if (isset($_GET['batch']) && !empty($_GET['batch'])) {
    $batch_year = (int)$_GET['batch'];
    $where_conditions[] = "s.acadamic_year = ?";
    $params[] = $batch_year;
    $types .= "i";
}

if (isset($_GET['event_type']) && !empty($_GET['event_type'])) {
    $where_conditions[] = "a.activity_type = ?";
    $params[] = $conn->real_escape_string($_GET['event_type']);
    $types .= "s";
}

// Main query to fetch activities with student details
$sql = "SELECT a.*, s.name, s.reg_number, s.department, s.academic_year,
        CASE 
            WHEN a.file_path IS NOT NULL THEN 'Uploaded'
            WHEN a.status = 'pending' THEN 'Pending'
            ELSE 'Not Uploaded'
        END as upload_status
        FROM activities a 
        JOIN students s ON a.student_id = s.id";

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY a.date_from DESC";

// Debug output
echo "<!-- Debug: SQL Query = " . htmlspecialchars($sql) . " -->";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$activities = [];
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}

// Debug output
echo "<!-- Debug: Number of activities found = " . count($activities) . " -->";
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
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($activity['name']); ?></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($activity['reg_number']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($activity['event_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($activity['college']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($activity['activity_type']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo date('M d, Y', strtotime($activity['date_from'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                            <?php
                                            if ($activity['file_path']) {
                                                echo 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100';
                                            } elseif ($activity['status'] == 'pending') {
                                                echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100';
                                            } else {
                                                echo 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';
                                            }
                                            ?>">
                                            <?php echo $activity['upload_status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if($activity['file_path']): ?>
                                            <a href="../../dashboard/student/view_file.php?file=<?php echo urlencode($activity['file_path']); ?>" 
                                               class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3"
                                               target="_blank">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        <?php endif; ?>
                                        <?php if($activity['upload_status'] == 'Pending'): ?>
                                            <a href="approve_activity.php?id=<?php echo $activity['id']; ?>&action=approve" 
                                               class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="approve_activity.php?id=<?php echo $activity['id']; ?>&action=reject" 
                                               class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if(isset($_GET['message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($_GET['message']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($_GET['error']); ?></span>
                    </div>
                <?php endif; ?>
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
<?php $conn->close(); ?> 