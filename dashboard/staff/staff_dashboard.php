<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['staff_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../../auth/staff_login.php");
    exit;
}

// Get staff information from session
$staff_name = $_SESSION['staff_name'];
$staff_email = $_SESSION['staff_email'];
$staff_department = $_SESSION['staff_department'];
$staff_designation = $_SESSION['staff_designation'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        
        .sidebar-header {
            padding: 15px 20px;
            border-bottom: 1px solid #4b545c;
            text-align: center;
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            padding: 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: #c2c7d0;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover {
            background-color: #494e53;
            color: white;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
        }
        
        .header {
            background-color: white;
            padding: 15px 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .user-info span {
            font-weight: bold;
        }
        
        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background-color: #c82333;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0;
        }
        
        .card-icon {
            font-size: 2rem;
            color: #007bff;
        }
        
        .card-body {
            font-size: 1.5rem;
            font-weight: bold;
            color: #343a40;
        }
        
        .recent-activity {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-time {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Staff Portal</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#"><i class="fas fa-calendar-alt"></i> Activities</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Students</a></li>
                <li><a href="#"><i class="fas fa-file-alt"></i> Reports</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="user-info">
                    <img src="../../assets/images/logo.png" alt="User Avatar">
                    <span><?php echo htmlspecialchars($staff_name); ?></span>
                </div>
                <a href="../../php/staff_logout.php" class="logout-btn">Logout</a>
            </div>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Department</h3>
                        <i class="fas fa-building card-icon"></i>
                    </div>
                    <div class="card-body">
                        <?php echo htmlspecialchars($staff_department); ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Designation</h3>
                        <i class="fas fa-user-tie card-icon"></i>
                    </div>
                    <div class="card-body">
                        <?php echo htmlspecialchars($staff_designation); ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Email</h3>
                        <i class="fas fa-envelope card-icon"></i>
                    </div>
                    <div class="card-body">
                        <?php echo htmlspecialchars($staff_email); ?>
                    </div>
                </div>
            </div>
            
            <div class="recent-activity">
                <h3>Welcome to Your Dashboard</h3>
                <p>This is your staff dashboard. You can manage student activities and view reports from here.</p>
                <p>Your role: <?php echo htmlspecialchars($staff_designation); ?> in <?php echo htmlspecialchars($staff_department); ?> department</p>
            </div>
        </div>
    </div>
</body>
</html> 