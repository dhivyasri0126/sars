<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-white dark:bg-gray-800 shadow-lg">
    <div class="p-4">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Admin Panel</h2>
    </div>
    <nav class="mt-4">
        <a href="admin_panel.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'admin_panel.php' ? 'bg-indigo-100 dark:bg-gray-700' : ''; ?>">
            <i class="fas fa-home w-6"></i>
            <span class="ml-3">Dashboard</span>
        </a>
        <a href="admin_students.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'admin_students.php' ? 'bg-indigo-100 dark:bg-gray-700' : ''; ?>">
            <i class="fas fa-users w-6"></i>
            <span class="ml-3">Students</span>
        </a>
        <a href="admin_staffs.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'admin_staffs.php' ? 'bg-indigo-100 dark:bg-gray-700' : ''; ?>">
            <i class="fas fa-user-tie w-6"></i>
            <span class="ml-3">Staffs</span>
        </a>
        <a href="admin_activities.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'admin_activities.php' ? 'bg-indigo-100 dark:bg-gray-700' : ''; ?>">
            <i class="fas fa-tasks w-6"></i>
            <span class="ml-3">Activities</span>
        </a>
        <a href="admin_rejected_activities.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'admin_rejected_activities.php' ? 'bg-indigo-100 dark:bg-gray-700' : ''; ?>">
            <i class="fas fa-times-circle w-6"></i>
            <span class="ml-3">Rejected Activities</span>
        </a>
        <a href="admin_profile.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'admin_profile.php' ? 'bg-indigo-100 dark:bg-gray-700' : ''; ?>">
            <i class="fas fa-user w-6"></i>
            <span class="ml-3">Profile</span>
        </a>
        <a href="admin_admins.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700 <?php echo $current_page === 'admin_admins.php' ? 'bg-indigo-100 dark:bg-gray-700' : ''; ?>">
            <i class="fas fa-user-shield w-6"></i>
            <span class="ml-3">Admins</span>
        </a>
        <a href="./admin_logout.php" class="flex items-center px-4 py-3 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900">
            <i class="fas fa-sign-out-alt w-6"></i>
            <span class="ml-3">Logout</span>
        </a>
    </nav>
</aside> 