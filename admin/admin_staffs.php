<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db_staff = "sats_db";
$conn = new mysqli($host, $user, $pass, $db_staff);
if ($conn->connect_error) {
    die("Staff DB Connection failed: " . $conn->connect_error);
}
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM staffs WHERE staff_id = $admin_id";
$result = $conn->query($sql);
$admin = $result->fetch_assoc();

// Get filter parameters
$department_filter = isset($_GET['department']) ? $_GET['department'] : 'all';
$designation_filter = isset($_GET['designation']) ? $_GET['designation'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="staff_export.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Department', 'Designation', 'Email', 'Role', 'Qualification']);
    
    $sql = "SELECT s.*, d.department_name 
            FROM staffs s 
            JOIN departments d ON s.department_id = d.department_id 
            WHERE 1=1";
    if ($department_filter !== 'all') {
        $sql .= " AND s.department_id = '" . $conn->real_escape_string($department_filter) . "'";
    }
    if ($designation_filter !== 'all') {
        $sql .= " AND s.designation = '" . $conn->real_escape_string($designation_filter) . "'";
    }
    if ($search) {
        $sql .= " AND (s.staff_name LIKE '%" . $conn->real_escape_string($search) . "%' 
                  OR s.email LIKE '%" . $conn->real_escape_string($search) . "%')";
    }
    
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['staff_name'],
            $row['department_name'],
            $row['designation'],
            $row['email'],
            $row['role'],
            $row['qualification']
        ]);
    }
    fclose($output);
    exit();
}

// Get departments and designations for filters
$departments = [];
$dept_result = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
while ($row = $dept_result->fetch_assoc()) {
    $departments[] = $row;
}

$designations = [];
$desig_result = $conn->query("SELECT DISTINCT designation FROM staffs ORDER BY designation");
while ($row = $desig_result->fetch_assoc()) {
    $designations[] = $row['designation'];
}

// Build query with filters
$sql = "SELECT s.*, d.department_name 
        FROM staffs s 
        JOIN departments d ON s.department_id = d.department_id 
        WHERE 1=1";
if ($department_filter !== 'all') {
    $sql .= " AND s.department_id = '" . $conn->real_escape_string($department_filter) . "'";
}
if ($designation_filter !== 'all') {
    $sql .= " AND s.designation = '" . $conn->real_escape_string($designation_filter) . "'";
}
if ($search) {
    $sql .= " AND (s.staff_name LIKE '%" . $conn->real_escape_string($search) . "%' 
              OR s.email LIKE '%" . $conn->real_escape_string($search) . "%')";
}

$sql .= " ORDER BY s.staff_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Staff</title>
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
                            <a href="admin_activities.php" class="text-gray-900 dark:text-white hover:text-gray-500 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">Activities</a>
                            <a href="admin_students.php" class="text-gray-900 dark:text-white hover:text-gray-500 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">Students</a>
                            <a href="admin_staffs.php" class="border-indigo-500 text-gray-900 dark:text-white px-3 py-2 rounded-md text-sm font-medium">Staff</a>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Designation</label>
                        <select name="designation" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="all" <?php echo $designation_filter === 'all' ? 'selected' : ''; ?>>All</option>
                            <?php foreach ($designations as $desig): ?>
                            <option value="<?php echo htmlspecialchars($desig); ?>" <?php echo $designation_filter === $desig ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($desig); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by name or email"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="md:col-span-4 flex justify-end space-x-4">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                        <a href="?export=csv<?php echo $department_filter !== 'all' ? '&department=' . urlencode($department_filter) : ''; ?><?php echo $designation_filter !== 'all' ? '&designation=' . urlencode($designation_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            <i class="fas fa-file-export mr-2"></i>Export CSV
                        </a>
                    </div>
                </form>
            </div>

            <!-- Staff Table -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Designation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($row['staff_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($row['department_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($row['designation']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($row['role']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <div class="flex space-x-2">
                                        <button onclick="editStaff(<?php echo $row['id']; ?>)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteStaff(<?php echo $row['id']; ?>)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
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

        // Edit staff function
        function editStaff(id) {
            // Implement edit functionality
            Swal.fire({
                title: 'Edit Staff',
                text: 'Edit functionality will be implemented here',
                icon: 'info',
                confirmButtonColor: '#4F46E5'
            });
        }

        // Delete staff function
        function deleteStaff(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Implement delete functionality
                    Swal.fire(
                        'Deleted!',
                        'Staff member has been deleted.',
                        'success'
                    );
                }
            });
        }
    </script>
</body>
</html> 