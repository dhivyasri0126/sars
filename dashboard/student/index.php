<?php
session_start();
require_once '../auth/db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../auth/student_login.php");
    exit();
}

// Get student details
$student_id = $_SESSION['user_id'];
$sql = "SELECT s.*, d.department_name 
        FROM students s 
        LEFT JOIN departments d ON s.department_id = d.department_id 
        WHERE s.student_id = $student_id";
$result = $conn->query($sql);
$student = $result->fetch_assoc();

// Get student's activities
$activities_sql = "SELECT a.*, s.student_name, d.department_name 
                  FROM activities a 
                  JOIN students s ON a.student_id = s.student_id 
                  LEFT JOIN departments d ON s.department_id = d.department_id 
                  WHERE a.student_id = $student_id 
                  ORDER BY a.start_date DESC";
$activities_result = $conn->query($activities_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold">Student Dashboard</h1>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="ml-4 flex items-center md:ml-6">
                        <div class="ml-3 relative">
                            <div class="flex items-center">
                                <span class="text-gray-700 mr-4"><?php echo htmlspecialchars($student['student_name']); ?></span>
                                <a href="profile.php" class="text-gray-700 hover:text-gray-900 mr-4">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                <a href="../auth/logout.php" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Student Info Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-lg font-semibold mb-4">Student Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="font-medium"><?php echo htmlspecialchars($student['student_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Registration Number</p>
                        <p class="font-medium"><?php echo htmlspecialchars($student['reg_number']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Department</p>
                        <p class="font-medium"><?php echo htmlspecialchars($student['department_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($student['email']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities Section -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">My Activities</h2>
                    <a href="activity.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        Add New Activity
                    </a>
                </div>
                
                <?php if ($activities_result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($activity = $activities_result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($activity['activity_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($activity['start_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($activity['end_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $activity['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                            ($activity['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo ucfirst($activity['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if ($activity['certificate_url']): ?>
                                    <a href="view_file.php?id=<?php echo $activity['activity_id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        View Certificate
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-4">No activities found. Start by adding a new activity!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
