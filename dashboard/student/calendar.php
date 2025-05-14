<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "student_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch activities
$sql = "SELECT date_from, event_name, event_type, college, award FROM activities ORDER BY date_from DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Portal</title>
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
            <h2 class="text-center text-xl font-bold text-gray-800 dark:text-white">Student Portal</h2>
        </div>
        <nav class="flex-1 mt-6">
            <a href="index.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                <i class="fas fa-home w-6"></i>
                <span class="ml-3">Dashboard</span>
            </a>
            <a href="activity.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                <i class="fas fa-edit w-6"></i>
                <span class="ml-3">Activity Participation</span>
            </a>
            <a href="upload.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                <i class="fas fa-upload w-6"></i>
                <span class="ml-3">Upload</span>
            </a>
            <a href="calendar.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                <i class="fas fa-calendar-alt w-6"></i>
                <span class="ml-3">Activity Calendar</span>
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
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Activity Calendar</h1>
            <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
            </button>
        </header>
        <main class="p-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-4xl mx-auto">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Activity Calendar</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">College</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Awards/Prizes Won</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>" . htmlspecialchars($row['date_from']) . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>" . htmlspecialchars($row['event_name']) . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>" . htmlspecialchars($row['event_type']) . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>" . htmlspecialchars($row['college']) . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>" . ($row['award'] ? htmlspecialchars($row['award']) : 'No') . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='px-6 py-4 text-center text-gray-500 dark:text-gray-300'>No activity records found.</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
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
$conn->close();
?>
