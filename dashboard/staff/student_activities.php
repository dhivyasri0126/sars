<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: ../../auth/staff_login.php");
    exit();
}

// Database connection parameters
$host = "localhost";
$user = "root";
$pass = "";
$db = "student_portal";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch staff details
$staff_id = $_SESSION['staff_id'];
$sql = "SELECT * FROM staff WHERE id = '$staff_id'";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $staff = $result->fetch_assoc();
} else {
    session_destroy();
    header("Location: ../../auth/staff_login.php");
    exit();
}

// Fetch student activities
$activities_sql = "SELECT a.*, s.name as student_name, s.roll_number 
                  FROM activities a 
                  JOIN students s ON a.student_id = s.id 
                  ORDER BY a.created_at DESC";
$activities = $conn->query($activities_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Activities</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
            --sidebar-width: 250px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--dark-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .sidebar-menu li {
            padding: 10px 20px;
        }

        .sidebar-menu li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu li a:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .sidebar-menu li.active a {
            background-color: var(--primary-color);
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            flex: 1;
        }

        .header {
            background-color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--light-color);
            font-weight: bold;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-pending {
            background-color: #ffc107;
            color: white;
        }

        .status-approved {
            background-color: #28a745;
            color: white;
        }

        .status-rejected {
            background-color: #dc3545;
            color: white;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
        }

        .approve-btn {
            background-color: #28a745;
            color: white;
        }

        .reject-btn {
            background-color: #dc3545;
            color: white;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                position: fixed;
                z-index: 1000;
                transition: width 0.3s;
            }

            .sidebar.active {
                width: var(--sidebar-width);
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.active {
                margin-left: var(--sidebar-width);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Staff Portal</h2>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
            </li>
            <li class="active">
                <a href="student_activities.php">
                    <i class="fas fa-tasks"></i>
                    Student Activities
                </a>
            </li>
            <li>
                <a href="downloads.php">
                    <i class="fas fa-download"></i>
                    Downloads
                </a>
            </li>
            <li>
                <a href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </li>
            <li>
                <a href="../../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Student Activities</h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($staff['designation']); ?></span>
            </div>
        </div>

        <div class="content-card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Roll Number</th>
                            <th>Activity</th>
                            <th>Date</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($activity = $activities->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($activity['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($activity['roll_number']); ?></td>
                            <td><?php echo htmlspecialchars($activity['activity_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($activity['activity_date'])); ?></td>
                            <td><?php echo $activity['points']; ?></td>
                            <td>
                                <span class="status status-<?php echo strtolower($activity['status']); ?>">
                                    <?php echo $activity['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($activity['status'] == 'Pending'): ?>
                                <button class="action-btn approve-btn">Approve</button>
                                <button class="action-btn reject-btn">Reject</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('active');
            }
            
            document.querySelectorAll('.sidebar-menu li').forEach(item => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.sidebar-menu li').forEach(i => {
                        i.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?> 