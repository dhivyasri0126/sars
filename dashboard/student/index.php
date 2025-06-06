<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection
$conn = new mysqli("localhost", "root", "", "student_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get reg_number from session (or fallback for testing)
$reg_number = $_SESSION['reg_number'] ?? '';

// Initialize values
$student_name = "";
$last_participation = "None";
$total_participated = 0;
$total_prizes = 0;

// Get student ID first
$sql = "SELECT id FROM students WHERE reg_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reg_number);
$stmt->execute();
$stmt->bind_result($student_id);
$stmt->fetch();
$stmt->close();

// 1. Fetch student name
$sql = "SELECT name FROM students WHERE reg_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reg_number);
$stmt->execute();
$stmt->bind_result($name_result);
if ($stmt->fetch()) {
    $student_name = $name_result ?? $student_name;
}
$stmt->close();

// 2. Last participated event
$sql = "SELECT event_name FROM activities WHERE student_id = ? ORDER BY date_to DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($event_result);
if ($stmt->fetch()) {
    $last_participation = $event_result ?? $last_participation;
}
$stmt->close();

// 3. Total events participated
$sql = "SELECT COUNT(*) FROM activities WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($total_result);
if ($stmt->fetch()) {
    $total_participated = $total_result ?? 0;
}
$stmt->close();

// 4. Prizes won
$sql = "SELECT COUNT(*) FROM activities WHERE student_id = ? AND award IS NOT NULL AND award != ''";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($prize_result);
if ($stmt->fetch()) {
    $total_prizes = $prize_result ?? 0;
}
$stmt->close();

// Monthly participation (number of activities participated in each month)
$monthly_participation = array_fill(1, 12, 0); // Months 1-12 (Jan to Dec)

$sql = "SELECT MONTH(date_to) AS month, COUNT(*) AS count
        FROM activities
        WHERE student_id = ?
        GROUP BY MONTH(date_to)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($month, $count);

while ($stmt->fetch()) {
    $monthly_participation[(int)$month] = (int)$count;
}
$stmt->close();

$chart_data = array_values($monthly_participation); // Ensure 0-indexed array for JS


$conn->close();


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
    </style>
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
            <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                <i class="fas fa-user w-6"></i>
                <span class="ml-3">Profile</span>
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
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
            <div class="flex flex-col items-end">
                <?php
                $student_name = $_SESSION['name'] ?? '';
                $reg_number = $_SESSION['reg_number'] ?? '';
                ?>
                <span class="font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($student_name); ?></span>
                <span class="text-xs text-gray-500 dark:text-gray-300"><?php echo htmlspecialchars($reg_number); ?></span>
            </div>
            <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
            </button>
        </header>
        <main class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 flex flex-col items-center">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-user text-green-500 text-2xl mr-2"></i>
                        <span class="text-gray-700 dark:text-gray-300 font-semibold">Name</span>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($student_name ?? "Unknown"); ?></span>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 flex flex-col items-center">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-edit text-blue-500 text-2xl mr-2"></i>
                        <span class="text-gray-700 dark:text-gray-300 font-semibold">Last Participation</span>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($last_participation ?? "None"); ?></span>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 flex flex-col items-center">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-calendar-check text-orange-500 text-2xl mr-2"></i>
                        <span class="text-gray-700 dark:text-gray-300 font-semibold">Total Events Participated</span>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white"><?php echo (int)$total_participated; ?></span>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 flex flex-col items-center">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-trophy text-red-500 text-2xl mr-2"></i>
                        <span class="text-gray-700 dark:text-gray-300 font-semibold">Prizes Won</span>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white"><?php echo (int)$total_prizes; ?></span>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Monthly Participation</h2>
                <canvas id="myLineChart" height="80"></canvas>
            </div>
        </main>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mt-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">My Activities</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date From</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php
                        $conn = new mysqli("localhost", "root", "", "student_portal");
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }
                        $sql = "SELECT activity_type, event_name, date_from, date_to, college, status, file_path FROM activities WHERE student_id = ? ORDER BY date_to DESC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $student_id);
                        $stmt->execute();
                        $stmt->bind_result($activity_type, $event_name, $date_from, $date_to, $college, $status, $file_path);
                        while ($stmt->fetch()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($activity_type ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($event_name ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($date_from ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($date_to ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($college ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    <?php
                                    if ($file_path) {
                                        echo 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100';
                                    } elseif ($status == 'pending') {
                                        echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100';
                                    } else {
                                        echo 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';
                                    }
                                    ?>">
                                    <?php
                                    if ($file_path) {
                                        echo 'Uploaded';
                                    } elseif ($status == 'pending') {
                                        echo 'Pending';
                                    } else {
                                        echo 'Not Uploaded';
                                    }
                                    ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; $stmt->close(); $conn->close(); ?>
                    </tbody>
                </table>
            </div>
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
    // Chart.js
    let labels = ['January', 'February', 'March', 'April', 'May','June','July','August','September','October','November','December'];
    let dataset1Data = <?php echo json_encode($chart_data); ?>;
    let ctx = document.getElementById('myLineChart').getContext('2d');
    let myLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'No. of Events Participated',
                    data: dataset1Data,
                    borderColor: 'blue',
                    borderWidth: 2,
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Months',
                        font: { padding: 4, size: 20, weight: 'bold', family: 'Arial' },
                        color: 'darkblue'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Number of Events',
                        font: { size: 20, weight: 'bold', family: 'Arial' },
                        color: 'darkblue'
                    },
                    beginAtZero: true,
                    scaleLabel: { display: true, labelString: 'Values' }
                }
            }
        }
    });
</script>
</body>
</html>
