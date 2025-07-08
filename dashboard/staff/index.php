<?php
session_start();
require_once '../auth/db.php';

// Check if user is logged in and is a staff member
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: ../auth/staff_login.php");
    exit();
}

// Get staff details
$staff_id = $_SESSION['user_id'];
$sql = "SELECT s.*, d.department_name 
        FROM staffs s 
        LEFT JOIN departments d ON s.department_id = d.department_id 
        WHERE s.staff_id = $staff_id";
$result = $conn->query($sql);
$staff = $result->fetch_assoc();

// Get pending activities for approval
$pending_sql = "SELECT a.*, s.student_name, s.reg_number, d.department_name 
               FROM activities a 
               JOIN students s ON a.student_id = s.student_id 
               LEFT JOIN departments d ON s.department_id = d.department_id 
               WHERE a.status = 'pending' 
               ORDER BY a.start_date DESC";
$pending_result = $conn->query($pending_sql);

// Get recent activities
$recent_sql = "SELECT a.*, s.student_name, s.reg_number, d.department_name 
              FROM activities a 
              JOIN students s ON a.student_id = s.student_id 
              LEFT JOIN departments d ON s.department_id = d.department_id 
              WHERE a.status = 'approved' 
              ORDER BY a.start_date DESC 
              LIMIT 5";
$recent_result = $conn->query($recent_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold">Staff Dashboard</h1>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="ml-4 flex items-center md:ml-6">
                        <div class="ml-3 relative">
                            <div class="flex items-center">
                                <span class="text-gray-700 mr-4"><?php echo htmlspecialchars($staff['staff_name']); ?></span>
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
        <!-- Staff Info Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-lg font-semibold mb-4">Staff Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="font-medium"><?php echo htmlspecialchars($staff['staff_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Designation</p>
                        <p class="font-medium"><?php echo htmlspecialchars($staff['designation']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Department</p>
                        <p class="font-medium"><?php echo htmlspecialchars($staff['department_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Role</p>
                        <p class="font-medium"><?php echo ucfirst(htmlspecialchars($staff['role'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Activities Section -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Pending Approvals</h2>
                    <a href="approvals.php" class="text-indigo-600 hover:text-indigo-900">
                        View All
                    </a>
                </div>
                
                <?php if ($pending_result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($activity = $pending_result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($activity['student_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($activity['reg_number']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($activity['activity_name']); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($activity['start_date'])); ?> - 
                                        <?php echo date('M d, Y', strtotime($activity['end_date'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($activity['department_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="approve_activity.php?id=<?php echo $activity['activity_id']; ?>" 
                                       class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        Review
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-4">No pending approvals</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activities Section -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Recent Activities</h2>
                    <a href="activities.php" class="text-indigo-600 hover:text-indigo-900">
                        View All
                    </a>
                </div>
                
                <?php if ($recent_result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($activity = $recent_result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($activity['student_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($activity['reg_number']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($activity['activity_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($activity['department_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($activity['start_date'])); ?></div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-4">No recent activities</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 