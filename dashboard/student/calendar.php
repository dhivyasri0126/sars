<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Database connection
$conn = new mysqli("localhost", "root", "", "student_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Get reg_number from session (or fallback for testing)
$reg_number = $_SESSION['reg_number'] ?? '';

// First get the student's ID
$sql = "SELECT id FROM students WHERE reg_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reg_number);
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();
$student_id = $student['id'];

// Fetch activities for this student
$sql = "SELECT date_from, date_to, event_name, activity_type, file_path, status FROM activities WHERE student_id = ? ORDER BY date_from DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
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
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Activity Calendar</h1>
            <div class="flex flex-col items-end">
                <?php
                $student_name = $_SESSION['first_name'] ?? '';
                $student_lname = $_SESSION['last_name'] ?? '';
                $reg_number = $_SESSION['reg_number'] ?? '';
                ?>
                <span class="font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($student_name . ' ' . $student_lname); ?></span>
                <span class="text-xs text-gray-500 dark:text-gray-300"><?php echo htmlspecialchars($reg_number); ?></span>
            </div>
            <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
            </button>
        </header>
        <main class="p-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 w-full">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Activity Calendar</h2>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Start Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">End Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Upload Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Approval Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Activity Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Certificate</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>" . date('M d, Y', strtotime($row['date_from'])) . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>" . date('M d, Y', strtotime($row['date_to'])) . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>" . htmlspecialchars($row['event_name']) . "</td>";
                                // Upload Status
                                echo "<td class='px-6 py-4 whitespace-nowrap'>";
                                if ($row['file_path']) {
                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'>
                                            <i class='fas fa-check mr-1'></i> Uploaded
                                          </span>";
                                } else {
                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'>
                                            <i class='fas fa-times mr-1'></i> Not Uploaded
                                          </span>";
                                }
                                echo "</td>";
                                // Approval Status
                                echo "<td class='px-6 py-4 whitespace-nowrap'>";
                                if ($row['status'] == 'approved') {
                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'>
                                            <i class='fas fa-check-circle mr-1'></i> Approved
                                          </span>";
                                } elseif ($row['status'] == 'pending') {
                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'>
                                            <i class='fas fa-clock mr-1'></i> Pending
                                          </span>";
                                } elseif ($row['status'] == 'rejected') {
                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'>
                                            <i class='fas fa-times-circle mr-1'></i> Rejected
                                          </span>";
                                } elseif ($row['status'] == 'tutor_approved') {
                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'>
                                            <i class='fas fa-check-circle mr-1'></i> Tutor Approved
                                          </span>";
                                } else {
                                    echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'>
                                            <i class='fas fa-minus-circle mr-1'></i> Not Submitted
                                          </span>";
                                }
                                echo "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300'>" . htmlspecialchars($row['activity_type']) . "</td>";
                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm'>";
                                if (!empty($row['file_path'])) {
                                    $file_extension = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));
                                    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        echo "<span class='text-green-600 dark:text-green-400'><i class='fas fa-image'></i> Uploaded</span>";
                                    } else {
                                        echo "<span class='text-green-600 dark:text-green-400'><i class='fas fa-file-pdf'></i> Uploaded</span>";
                                    }
                                    echo " <button onclick='previewFile(\"" . htmlspecialchars($row['file_path']) . "\", \"" . $file_extension . "\")' class='ml-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300'><i class='fas fa-eye'></i> Preview</button>";
                                } else {
                                    echo "<a href='upload.php' class='text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300'><i class='fas fa-upload'></i> Upload Certificate</a>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='px-6 py-4 text-center text-gray-500 dark:text-gray-300'>No activity records found.</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative mx-auto p-3 border w-11/12 max-w-7xl shadow-2xl rounded-md bg-white dark:bg-gray-800">
        <div class="mt-2">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-xl font-medium text-gray-900 dark:text-white">File Preview</h3>
                <button onclick="closePreview()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-1">
                <div id="pdfPreview" class="hidden">
                    <iframe id="pdfFrame" class="w-full h-[85vh]" frameborder="0"></iframe>
                </div>
                <div id="imagePreview" class="hidden">
                    <img id="previewImage" src="" alt="Preview" class="max-w-full h-auto mx-auto max-h-[85vh] object-contain">
                </div>
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

    // File preview functions
    function previewFile(filePath, fileType) {
        const modal = document.getElementById('previewModal');
        const pdfPreview = document.getElementById('pdfPreview');
        const imagePreview = document.getElementById('imagePreview');
        const pdfFrame = document.getElementById('pdfFrame');
        const previewImage = document.getElementById('previewImage');

        modal.classList.remove('hidden');
        
        if (fileType === 'pdf') {
            pdfPreview.classList.remove('hidden');
            imagePreview.classList.add('hidden');
            pdfFrame.src = 'view_file.php?file=' + encodeURIComponent(filePath);
        } else {
            pdfPreview.classList.add('hidden');
            imagePreview.classList.remove('hidden');
            previewImage.src = 'view_file.php?file=' + encodeURIComponent(filePath);
        }
    }

    function closePreview() {
        const modal = document.getElementById('previewModal');
        const pdfFrame = document.getElementById('pdfFrame');
        const previewImage = document.getElementById('previewImage');
        
        modal.classList.add('hidden');
        pdfFrame.src = '';
        previewImage.src = '';
    }

    // Close modal when clicking outside
    document.getElementById('previewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePreview();
        }
    });
</script>
</body>
</html>

<?php
$conn->close();
?>
