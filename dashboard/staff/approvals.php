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
$db = "staff_signup";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get staff details
$staff_id = $_SESSION['staff_id'];
$sql = "SELECT * FROM staff WHERE id = $staff_id";
$result = $conn->query($sql);
$staff = $result->fetch_assoc();

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['activity_id'])) {
    $activity_id = (int)$_POST['activity_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $sql = "UPDATE activities SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $activity_id);
        
        if ($stmt->execute()) {
            // Update student's activity count if approved
            if ($action === 'approve') {
                $sql = "UPDATE students s 
                        JOIN activities a ON s.id = a.student_id 
                        SET s.activity_count = s.activity_count + 1 
                        WHERE a.id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $activity_id);
                $stmt->execute();
            }
            
            header("Location: approvals.php?success=1");
            exit();
        }
    }
}

// Get pending activities with student names
$sql = "SELECT a.*, s.name as student_name 
        FROM activities a 
        JOIN students s ON a.student_id = s.id 
        WHERE a.status = 'pending' 
        ORDER BY a.date DESC";
$result = $conn->query($sql);
$pending_activities = [];
while ($row = $result->fetch_assoc()) {
    $pending_activities[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approvals - Staff Dashboard</title>
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
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Approvals</h1>
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
                    <span class="block sm:inline">Activity status updated successfully.</span>
                </div>
                <?php endif; ?>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Pending Approvals</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($pending_activities as $activity): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($activity['student_name']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($activity['title']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo $activity['date']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form method="post" class="flex space-x-2">
                                                <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                                                <button type="submit" name="action" value="approve" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                                                    Approve
                                                </button>
                                                <button type="submit" name="action" value="reject" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                                                    Reject
                                                </button>
                                            </form>
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
<?php $conn->close(); ?> 