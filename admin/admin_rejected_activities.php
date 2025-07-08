<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    // header("Location: auth/admin_login.php");
    // exit();
}
$host = "localhost";
$user = "root";
$pass = "";
$db = "student_portal";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Handle status change
if (isset($_POST['set_pending']) && isset($_POST['activity_id'])) {
    $activity_id = (int)$_POST['activity_id'];
    $conn->query("UPDATE activities SET status = 'pending' WHERE id = $activity_id");
    // Redirect to refresh the page
    header("Location: admin_rejected_activities.php");
    exit();
}

// Fetch rejected activities with student details
$sql = "SELECT a.*, s.name, s.reg_number 
        FROM activities a 
        JOIN students s ON a.student_id = s.id 
        WHERE a.status = 'rejected' 
        ORDER BY a.date_from DESC";
$result = $conn->query($sql);
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
    <title>Rejected Activities - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="flex items-center justify-between px-6 py-4">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Rejected Activities</h1>
                </div>
            </header>
            <main class="p-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Rejected Activities List</h2>
                        <?php if (empty($activities)): ?>
                        <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                            No rejected activities found.
                        </div>
                        <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($activities as $activity): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            <?php echo htmlspecialchars($activity['name']); ?> 
                                            <span class="text-gray-500">(<?php echo htmlspecialchars($activity['reg_number']); ?>)</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($activity['event_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300"><?php echo date('M d, Y', strtotime($activity['date_from'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Rejected
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                                                <button type="submit" name="set_pending" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                                    <i class="fas fa-undo"></i> Set to Pending
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 