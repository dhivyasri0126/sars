<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
$host = "localhost";
$user = "root";
$pass = "";
$db = "sats_db";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Handle add admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $staff_name = $conn->real_escape_string($_POST['staff_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $designation = $conn->real_escape_string($_POST['designation']);
    $qualification = $conn->real_escape_string($_POST['qualification']);
    $role = 'admin';
    // Get the department_id for 'Administration'
    $dept_res = $conn->query("SELECT department_id FROM departments WHERE department_name = 'Administration' LIMIT 1");
    $dept_row = $dept_res->fetch_assoc();
    $department_id = $dept_row ? (int)$dept_row['department_id'] : 1; // fallback to 1 if not found
    $default_password = password_hash($email, PASSWORD_DEFAULT);
    $conn->query("INSERT INTO staffs (staff_name, email, designation, role, qualification, department_id) VALUES ('$staff_name', '$email', '$designation', '$role', '$qualification', $department_id)");
    $new_id = $conn->insert_id;
    $conn->query("INSERT INTO logins (user_id, user_type, password_hash) VALUES ($new_id, 'staff', '$default_password')");
    header('Location: admin_admins.php');
    exit();
}
$sql = "SELECT s.*, d.department_name FROM staffs s LEFT JOIN departments d ON s.department_id = d.department_id WHERE s.role = 'admin'";
$result = $conn->query($sql);
$admins = [];
while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}
$departments = [];
$dept_result = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
while ($row = $dept_result->fetch_assoc()) {
    $departments[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admins</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
<div class="flex h-screen">
<?php include 'includes/admin_sidebar.php'; ?>
    <div class="flex-1 overflow-auto">
        <header class="bg-white dark:bg-gray-800 shadow flex items-center justify-between px-6 py-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Admins</h1>
            <button onclick="document.getElementById('addAdminModal').classList.remove('hidden')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700"><i class="fas fa-user-plus mr-2"></i>Add Admin</button>
        </header>
        <main class="p-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Admin Users</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($admin['staff_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($admin['role']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($admin['department_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="admin_profile.php?id=<?php echo $admin['staff_id']; ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="#" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"><i class="fas fa-trash"></i> Remove</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Add Admin Modal -->
            <div id="addAdminModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 w-full max-w-md">
                    <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Add New Admin</h2>
                    <form method="post">
                        <input type="hidden" name="add_admin" value="1">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2">Name</label>
                            <input type="text" name="staff_name" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2">Email</label>
                            <input type="email" name="email" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2">Designation</label>
                            <input type="text" name="designation" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2">Qualification</label>
                            <input type="text" name="qualification" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        </div>
                        <!-- Department is always Administration for admins -->
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2">Department</label>
                            <input type="text" value="Administration" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" onclick="document.getElementById('addAdminModal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 mr-2">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Add Admin</button>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                // Close modal on outside click
                document.getElementById('addAdminModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.add('hidden');
                    }
                });
            </script>
        </main>
    </div>
</div>
</body>
</html> 