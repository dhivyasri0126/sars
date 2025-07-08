<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['reg_number'])) {
    header('Location: ../../auth/student_login.php');
    exit();
}

// DB connection
$conn = new mysqli("localhost", "root", "", "student_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$reg_number = $_SESSION['reg_number'];

// Fetch student details
$sql = "SELECT id, name, reg_number FROM students WHERE reg_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reg_number);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activity_type = $_POST['activity_type'];
    $event_name = $_POST['event_name'];
    $date_from = $_POST['date_from'];
    $date_to = $_POST['date_to'];
    $college = $_POST['college'];
    $event_type = $_POST['event_type'];
    $award = $_POST['award'];
    $reg_number = $student['reg_number'];
    $student_id = $student['id'];
    $sql = "INSERT INTO activities (student_id, reg_number, activity_type, date_from, date_to, college, event_type, event_name, award) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $student_id, $reg_number, $activity_type, $date_from, $date_to, $college, $event_type, $event_name, $award);
    
    if ($stmt->execute()) {
        $success_message = "Activity added successfully!";
    } else {
        $error_message = "Error adding activity: " . $conn->error;
    }
    $stmt->close();
}

// Fetch existing activities
$sql = "SELECT * FROM activities WHERE student_id = ? ORDER BY date_to DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student['id']);
$stmt->execute();
$result = $stmt->get_result();
$activities = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Activity Participation</title>
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
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Activity Participation</h1>
                    <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                    </button>
                </header>
                <main class="p-6">
                    <?php if (isset($success_message)): ?>
                        <div class="mb-4 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($error_message)): ?>
                        <div class="mb-4 p-4 rounded-lg bg-red-100 border border-red-400 text-red-700">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 max-w-2xl mx-auto">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Add New Activity</h2>
                        <form method="post" class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                <input type="text" id="name" value="<?php echo htmlspecialchars($student['name']); ?>" 
                                       class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-4 text-base" 
                                       disabled>
                            </div>
                            <div>
                                <label for="activity_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Activity Type</label>
                                <select name="activity_type" id="activity_type" required
                                        class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-4 text-base">
                                    <option value="">Select Activity Type</option>
                                    <option value="Academic">Academic</option>
                                    <option value="Sports">Sports</option>
                                    <option value="Cultural">Cultural</option>
                                    <option value="Technical">Technical</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label for="event_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Name</label>
                                <input type="text" name="event_name" id="event_name" required
                                       class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-4 text-base">
                            </div>
                            <div>
                                <label for="college" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Location</label>
                                <input type="text" name="college" id="college" required
                                       class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-4 text-base"
                                       placeholder="Enter event location">
                            </div>
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                                <input type="date" name="date_from" id="date_from" required
                                       class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-4 text-base">
                            </div>
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                                <input type="date" name="date_to" id="date_to" required
                                       class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-4 text-base">
                            </div>
                            <div>
                                <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 text-base font-medium">
                                    Add Activity
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Existing Activities -->
                    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 max-w-2xl mx-auto">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-3">Your Activities</h2>
                        <?php if (empty($activities)): ?>
                            <p class="text-gray-600 dark:text-gray-400">No activities found.</p>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($activities as $activity): ?>
                                    <div class="border dark:border-gray-700 rounded-lg p-3">
                                        <div class="flex justify-between items-start">
                                        <div class="space-y-1">
                                            <h3 class="font-semibold text-gray-800 dark:text-white"><?php echo htmlspecialchars($activity['activity_type']); ?></h3>
                                            <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($activity['event_name']); ?></p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo date('M d, Y', strtotime($activity['date_from'])); ?> - 
                                                <?php echo date('M d, Y', strtotime($activity['date_to'])); ?>
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Event Location: <?php echo htmlspecialchars($activity['college'] ?? ''); ?>
                                            </p>
                                            </div>
                                            <div class="ml-4 flex flex-col gap-2">
                                                <!-- Upload Status -->
                                                <span class="px-3 py-1.5 rounded-full text-sm font-semibold
                                                    <?php
                                                    if ($activity['file_path']) {
                                                        echo 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100';
                                                    } else {
                                                        echo 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';
                                                    }
                                                    ?>">
                                                    <?php
                                                    if ($activity['file_path']) {
                                                        echo 'Uploaded';
                                                    } else {
                                                        echo 'Not Uploaded';
                                                    }
                                                    ?>
                                                </span>
                                                <!-- Approval Status -->
                                                <span class="px-3 py-1.5 rounded-full text-sm font-semibold
                                                    <?php
                                                    if ($activity['status'] == 'approved') {
                                                        echo 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100';
                                                    } elseif ($activity['status'] == 'pending') {
                                                        echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100';
                                                    } elseif ($activity['status'] == 'rejected') {
                                                        echo 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100';
                                                    } else {
                                                        echo 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';
                                                    }
                                                    ?>">
                                                    <?php
                                                    if ($activity['status'] == 'approved') {
                                                        echo 'Approved';
                                                    } elseif ($activity['status'] == 'pending') {
                                                        echo 'Pending';
                                                    } elseif ($activity['status'] == 'rejected') {
                                                        echo 'Rejected';
                                                    } else {
                                                        echo 'Not Submitted';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
            
