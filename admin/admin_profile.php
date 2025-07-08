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
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM staffs WHERE staff_id = $admin_id";
$result = $conn->query($sql);
$admin = $result->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
<div class="flex h-screen">
<?php include 'includes/admin_sidebar.php'; ?>
    <div class="flex-1 overflow-auto">
        <header class="bg-white dark:bg-gray-800 shadow flex items-center justify-between px-6 py-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Profile</h1>
        </header>
        <main class="p-6">
            <div class="max-w-xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Admin Profile</h2>
                <form method="post">
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Name</label>
                        <input type="text" name="staff_name" value="<?php echo htmlspecialchars($admin['staff_name']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($admin['email']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Role</label>
                        <input type="text" value="<?php echo htmlspecialchars($admin['role']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Designation</label>
                        <input type="text" name="designation" value="<?php echo htmlspecialchars($admin['designation']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Qualification</label>
                        <input type="text" name="qualification" value="<?php echo htmlspecialchars($admin['qualification']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Department ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($admin['department_id']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                    </div>
                    <div class="mt-6 text-right">
                        <button type="submit" name="update_profile" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Save Changes</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
</body>
</html> 