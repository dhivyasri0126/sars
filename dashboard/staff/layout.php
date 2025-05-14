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

// Get current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .sidebar {
            transition: all 0.3s ease;
        }

        .nav-link {
            transition: all 0.3s ease;
        }

        .nav-link.active {
            background-color: rgba(79, 70, 229, 0.1);
            border-left: 4px solid var(--primary-color);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
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
                <a href="dashboard.php" class="nav-link flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home w-6"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="students.php" class="nav-link flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'students.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-3">Students</span>
                </a>
                <a href="activities.php" class="nav-link flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'activities.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tasks w-6"></i>
                    <span class="ml-3">Activities</span>
                </a>
                <a href="od_requests.php" class="nav-link flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'od_requests.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check w-6"></i>
                    <span class="ml-3">OD Requests</span>
                </a>
                <a href="approvals.php" class="nav-link flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'approvals.php' ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle w-6"></i>
                    <span class="ml-3">Approvals</span>
                </a>
                <a href="profile.php" class="nav-link flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user w-6"></i>
                    <span class="ml-3">Profile</span>
                </a>
                <a href="../../auth/logout.php" class="nav-link flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
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
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo ucfirst(str_replace('.php', '', $current_page)); ?></h1>
                    <div class="flex items-center space-x-4">
                        <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                            <i class="fas fa-moon dark:hidden"></i>
                            <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                        </button>
                        <div class="relative">
                            <button class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                                <i class="fas fa-bell text-gray-600 dark:text-gray-300"></i>
                                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                        </div>
                        <div class="relative">
                            <button class="flex items-center space-x-2">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($staff['name']); ?>" alt="Profile" class="w-8 h-8 rounded-full">
                                <span class="text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($staff['name']); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                <?php include $current_page; ?>
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