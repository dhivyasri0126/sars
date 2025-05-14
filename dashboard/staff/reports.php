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
$db = "staff_signup";

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

// Fetch statistics
$stats_sql = "SELECT 
    COUNT(*) as total_students,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_activities,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_activities,
    SUM(points) as total_points
    FROM activities";
$stats = $conn->query($stats_sql)->fetch_assoc();

// Fetch department-wise activity distribution
$dept_sql = "SELECT d.name as department, COUNT(a.id) as activity_count 
            FROM departments d 
            LEFT JOIN activities a ON d.id = a.department_id 
            GROUP BY d.id";
$dept_stats = $conn->query($dept_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .stat-title {
            color: var(--secondary-color);
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--dark-color);
        }

        .chart-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .chart-title {
            margin-bottom: 20px;
            color: var(--dark-color);
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
            <li>
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
            <li class="active">
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
            <h2>Reports</h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($staff['designation']); ?></span>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Total Students</div>
                <div class="stat-value"><?php echo $stats['total_students']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Approved Activities</div>
                <div class="stat-value"><?php echo $stats['approved_activities']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Pending Activities</div>
                <div class="stat-value"><?php echo $stats['pending_activities']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Total Points</div>
                <div class="stat-value"><?php echo $stats['total_points']; ?></div>
            </div>
        </div>

        <div class="chart-container">
            <h3 class="chart-title">Department-wise Activity Distribution</h3>
            <canvas id="deptChart"></canvas>
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

            // Department Chart
            const deptCtx = document.getElementById('deptChart').getContext('2d');
            const deptData = {
                labels: [
                    <?php 
                    while ($dept = $dept_stats->fetch_assoc()) {
                        echo "'" . htmlspecialchars($dept['department']) . "',";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Number of Activities',
                    data: [
                        <?php 
                        $dept_stats->data_seek(0);
                        while ($dept = $dept_stats->fetch_assoc()) {
                            echo $dept['activity_count'] . ",";
                        }
                        ?>
                    ],
                    backgroundColor: [
                        '#007bff',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#17a2b8',
                        '#6c757d'
                    ]
                }]
            };

            new Chart(deptCtx, {
                type: 'pie',
                data: deptData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?> 