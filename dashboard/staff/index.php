<?php
session_start();
require_once 'config/database.php';
require_once 'utils/functions.php';

// Check if user is logged in
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit();
}

$staff_id = $_SESSION['staff_id'];
$department_id = getDepartmentFilter($conn, $staff_id);
$batch_section = getBatchSectionFilter($conn, $staff_id);

// Get staff details
$sql = "SELECT s.*, d.name as department_name 
        FROM staff s 
        JOIN departments d ON s.department_id = d.id 
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

// Get pending activities count
$sql = "SELECT COUNT(*) as count FROM activities a 
        JOIN students s ON a.student_id = s.id 
        WHERE s.department_id = ? AND a.status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_count = $result->fetch_assoc()['count'];

// Get total students count
$sql = "SELECT COUNT(*) as count FROM students WHERE department_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();
$students_count = $result->fetch_assoc()['count'];

// Get recent activities
$sql = "SELECT a.*, s.name as student_name 
        FROM activities a 
        JOIN students s ON a.student_id = s.id 
        WHERE s.department_id = ? 
        ORDER BY a.created_at DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();
$recent_activities = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 bg-white shadow-lg max-h-screen w-60">
            <div class="flex flex-col justify-between h-full">
                <div class="flex-grow">
                    <div class="px-4 py-6 text-center border-b">
                        <h1 class="text-xl font-bold leading-none"><span class="text-yellow-700">Staff</span> Dashboard</h1>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-1">
                            <li>
                                <a href="index.php" class="flex items-center bg-yellow-100 rounded-xl font-bold text-sm text-yellow-900 py-3 px-4">
                                    <i class="fas fa-home mr-4"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="students.php" class="flex bg-white hover:bg-yellow-50 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                    <i class="fas fa-users mr-4"></i>
                                    Students
                                </a>
                            </li>
                            <li>
                                <a href="activities.php" class="flex bg-white hover:bg-yellow-50 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                    <i class="fas fa-tasks mr-4"></i>
                                    Activities
                                </a>
                            </li>
                            <li>
                                <a href="od_requests.php" class="flex bg-white hover:bg-yellow-50 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                    <i class="fas fa-calendar-check mr-4"></i>
                                    OD Requests
                                </a>
                            </li>
                            <li>
                                <a href="certificates.php" class="flex bg-white hover:bg-yellow-50 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                    <i class="fas fa-certificate mr-4"></i>
                                    Certificates
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($staff['name']) ?>&background=random" alt="Profile">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                <?= htmlspecialchars($staff['name']) ?>
                            </p>
                            <p class="text-sm text-gray-500 truncate">
                                <?= htmlspecialchars($staff['role']) ?>
                            </p>
                        </div>
                        <a href="logout.php" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="ml-60 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">Welcome back, <?= htmlspecialchars($staff['name']) ?>!</h2>
                    <p class="text-gray-600">Here's what's happening in your department.</p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Students</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= $students_count ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-tasks text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Pending Activities</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= $pending_count ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-certificate text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Department</p>
                                <p class="text-2xl font-semibold text-gray-900"><?= htmlspecialchars($staff['department_name']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-my-5 divide-y divide-gray-200">
                                <?php foreach ($recent_activities as $activity): ?>
                                <li class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full <?= getActivityStatus($activity['status']) ?>">
                                                <i class="fas fa-<?= $activity['event_type'] === 'od' ? 'calendar-check' : 'certificate' ?>"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                <?= htmlspecialchars($activity['title']) ?>
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                <?= htmlspecialchars($activity['student_name']) ?> • <?= formatDate($activity['event_date']) ?>
                                            </p>
                                        </div>
                                        <div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getActivityStatus($activity['status']) ?>">
                                                <?= ucwords(str_replace('_', ' ', $activity['status'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="mt-6">
                            <a href="activities.php" class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                View all activities
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 