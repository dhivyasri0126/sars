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

// Fetch downloadable resources
$downloads_sql = "SELECT * FROM downloads ORDER BY created_at DESC";
$downloads = $conn->query($downloads_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Downloads</title>
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

        .download-list {
            list-style: none;
            padding: 0;
        }

        .download-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s;
        }

        .download-item:hover {
            background-color: #f8f9fa;
        }

        .download-icon {
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .download-info {
            flex-grow: 1;
        }

        .download-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .download-meta {
            color: var(--secondary-color);
            font-size: 0.9em;
        }

        .download-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .download-btn i {
            margin-right: 5px;
        }

        .download-btn:hover {
            background-color: #0056b3;
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
            <li class="active">
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
            <h2>Downloads</h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($staff['designation']); ?></span>
            </div>
        </div>

        <div class="content-card">
            <ul class="download-list">
                <?php while ($download = $downloads->fetch_assoc()): ?>
                <li class="download-item">
                    <div class="download-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="download-info">
                        <div class="download-title"><?php echo htmlspecialchars($download['title']); ?></div>
                        <div class="download-meta">
                            Uploaded on <?php echo date('M d, Y', strtotime($download['created_at'])); ?>
                        </div>
                    </div>
                    <a href="<?php echo htmlspecialchars($download['file_path']); ?>" class="download-btn" download>
                        <i class="fas fa-download"></i>
                        Download
                    </a>
                </li>
                <?php endwhile; ?>
            </ul>
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