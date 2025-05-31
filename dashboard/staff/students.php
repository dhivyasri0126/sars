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

// Handle student update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $student_id = (int)$_POST['student_id'];
    $name = $conn_students->real_escape_string($_POST['name']);
    $department = $conn_students->real_escape_string($_POST['department']);
    $academic_year = (int)$_POST['academic_year'];
    
    $sql = "UPDATE students SET name = ?, department = ?, academic_year = ? WHERE id = ?";
    $stmt = $conn_students->prepare($sql);
    $stmt->bind_param("ssii", $name, $department, $academic_year, $student_id);
    
    if ($stmt->execute()) {
        header("Location: students.php?success=1");
        exit();
    }
}

// Handle student deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    $student_id = (int)$_POST['student_id'];
    
    // First delete related activities
    $sql = "DELETE FROM activities WHERE student_id = ?";
    $stmt = $conn_students->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    
    // Then delete the student
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $conn_students->prepare($sql);
    $stmt->bind_param("i", $student_id);
    
    if ($stmt->execute()) {
        header("Location: students.php?success=2");
        exit();
    }
}

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

// Build search and filter query
$where_conditions = [];
$params = [];
$types = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $conn_students->real_escape_string($_GET['search']) . "%";
    $where_conditions[] = "name LIKE ?";
    $params[] = $search;
    $types .= "s";
}

if (isset($_GET['department']) && !empty($_GET['department'])) {
    $where_conditions[] = "department = ?";
    $params[] = $conn_students->real_escape_string($_GET['department']);
    $types .= "s";
}

if (isset($_GET['batch']) && !empty($_GET['batch'])) {
    $batch_year = (int)$_GET['batch'];
    $where_conditions[] = "year = ?";
    $params[] = $batch_year;
    $types .= "i";
}

$sql = "SELECT s.*, (SELECT COUNT(*) FROM activities a WHERE a.id = s.id) as activities FROM students s";
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY s.name";

$stmt = $conn_students->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Staff Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/images/logo.png">
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
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Students</h1>
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
                <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Student information updated successfully.</span>
                </div>
                <?php endif; ?>

                <!-- Add Student Button -->
                <div class="flex justify-between items-center mb-4">
                    <button onclick="openAddStudentModal()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 flex items-center">
                        <i class="fas fa-user-plus mr-2"></i> Add Student
                    </button>
                    <div class="flex space-x-4">
                        <button type="button" onclick="openExportModal('pdf')" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"><i class="fas fa-file-pdf mr-2"></i>Export to PDF</button>
                        <button type="button" onclick="openExportModal('excel')" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"><i class="fas fa-file-excel mr-2"></i>Export to Excel</button>
                    </div>
                </div>

                <!-- Add Student Modal -->
                <div id="addStudentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
                    <div class="min-h-screen px-4 text-center">
                        <div class="fixed inset-0" aria-hidden="true">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
                        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
                        <div class="inline-block w-full max-w-4xl p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Add New Student</h2>
                                <button onclick="closeAddStudentModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            <form id="addStudentForm" method="POST" action="add_student.php" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="name">Name</label>
                                        <input type="text" id="name" name="name" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="regno">Register Number</label>
                                        <input type="text" id="regno" name="regno" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="department">Department</label>
                                        <select id="department" name="department" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <?php foreach ($departments as $code => $name): ?>
                                            <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="academic_year">Academic Year</label>
                                        <select id="academic_year" name="academic_year" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <?php foreach ($batches as $batch_name => $batch_year): ?>
                                            <option value="<?php echo $batch_year; ?>"><?php echo $batch_name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="section">Section</label>
                                        <input type="text" id="section" name="section" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="dob">Date of Birth</label>
                                        <input type="date" id="dob" name="dob" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="gender">Gender</label>
                                        <select id="gender" name="gender" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="mobile">Mobile Number</label>
                                        <input type="text" id="mobile" name="mobile" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="hostel_day">Hosteller/Day Scholar</label>
                                        <select id="hostel_day" name="hostel_day" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="hosteller">Hosteller</option>
                                            <option value="dayscholar">Day Scholar</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="email">Email</label>
                                        <input type="email" id="email" name="email" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="password">Password</label>
                                        <input type="password" id="password" name="password" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="confirm_password">Confirm Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Address Information</h3>
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="street">Street Address</label>
                                        <input type="text" id="street" name="street" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="city">City</label>
                                            <input type="text" id="city" name="city" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="state">State</label>
                                            <input type="text" id="state" name="state" required value="Tamil Nadu" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="pincode">Pincode</label>
                                            <input type="text" id="pincode" name="pincode" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="country">Country</label>
                                            <input type="text" id="country" name="country" required value="India" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                    </div>
                                    <input type="hidden" name="address" id="complete_address">
                                </div>
                                <div class="flex justify-end space-x-3 mt-6">
                                    <button type="button" onclick="closeAddStudentModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                        Add Student
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Export Modal -->
                <div id="exportModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md">
                        <h2 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Select columns to export</h2>
                        <form id="exportForm" method="post" action="export_students.php" target="_blank">
                            <input type="hidden" name="export_type" id="exportTypeInput" value="">
                            <div class="grid grid-cols-1 gap-2 mb-4">
                                <label class="text-gray-700 dark:text-gray-300"><input type="checkbox" name="columns[]" value="reg_number" checked> Register Number</label>
                                <label class="text-gray-700 dark:text-gray-300"><input type="checkbox" name="columns[]" value="name" checked> Name</label>
                                <label class="text-gray-700 dark:text-gray-300"><input type="checkbox" name="columns[]" value="department" checked> Department</label>
                                <label class="text-gray-700 dark:text-gray-300"><input type="checkbox" name="columns[]" value="academic_year" checked> Academic Year</label>
                                <label class="text-gray-700 dark:text-gray-300"><input type="checkbox" name="columns[]" value="activities" checked> Activities</label>
                            </div>
                            <div class="flex justify-end space-x-2">
                                <button type="button" onclick="closeExportModal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 text-gray-800">Cancel</button>
                                <button type="submit" class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">Export</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                    <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="search">Search Student Name</label>
                            <input type="text" id="search" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                                   class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                   placeholder="Enter student name">
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
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex-1">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Students Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Student List</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Register No.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Batch</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Activities</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($student['reg_number']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($student['department']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($student['academic_year']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($student['activities']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($student)); ?>)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button onclick="confirmDelete(<?php echo $student['id']; ?>)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
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

    <!-- Edit Student Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">Edit Student</h3>
                <form method="post" id="editForm">
                    <input type="hidden" name="student_id" id="edit_student_id">
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="edit_name">Name</label>
                        <input type="text" id="edit_name" name="name" required
                               class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="edit_department">Department</label>
                        <select id="edit_department" name="department" required
                                class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <?php foreach ($departments as $code => $name): ?>
                            <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2" for="edit_year">Academic Year</label>
                        <select id="edit_year" name="academic_year" required
                                class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <?php foreach ($batches as $batch_name => $batch_year): ?>
                            <option value="<?php echo $batch_year; ?>"><?php echo $batch_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit" name="update_student"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">Confirm Delete</h3>
                <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to delete this student? This action cannot be undone.</p>
                <form method="post" id="deleteForm">
                    <input type="hidden" name="student_id" id="delete_student_id">
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeDeleteModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit" name="delete_student"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Delete
                        </button>
                    </div>
                </form>
            </div>
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

        // Edit Modal Functions
        function openEditModal(student) {
            document.getElementById('edit_student_id').value = student.id;
            document.getElementById('edit_name').value = student.name;
            document.getElementById('edit_department').value = student.department;
            document.getElementById('edit_year').value = student.academic_year;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Delete Modal Functions
        function confirmDelete(studentId) {
            document.getElementById('delete_student_id').value = studentId;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        }

        // Export Modal Functions
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

        function openAddStudentModal() {
            document.getElementById('addStudentModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // Scroll to top of modal
            document.getElementById('addStudentModal').scrollTop = 0;
        }

        function closeAddStudentModal() {
            document.getElementById('addStudentModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('addStudentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddStudentModal();
            }
        });

        // Prevent modal from closing when clicking inside the modal content
        document.querySelector('#addStudentModal > div > div').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Listen for form submission in iframe
        window.addEventListener('message', function(e) {
            if (e.data === 'studentAdded') {
                closeAddStudentModal();
                window.location.reload();
            }
        });

        // Add Student Form Validation
        document.getElementById('addStudentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if passwords match
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }
            
            // Combine address fields
            const street = document.getElementById('street').value;
            const city = document.getElementById('city').value;
            const state = document.getElementById('state').value;
            const pincode = document.getElementById('pincode').value;
            const country = document.getElementById('country').value;
            
            // Create a complete address string
            const completeAddress = `${street}, ${city}, ${state}, ${country} - ${pincode}`;
            
            // Set the complete address in the hidden input
            document.getElementById('complete_address').value = completeAddress;
            
            // Submit the form
            this.submit();
        });
    </script>
</body>
</html>
<?php $conn_staff->close(); $conn_students->close(); ?>