<?php
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

// First get the student's ID
$sql = "SELECT id FROM students WHERE reg_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reg_number);
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();
$student_id = $student['id'];

// Fetch activities for the student
$sql = "SELECT id, activity_type, event_name, date_from, date_to, file_path, status FROM activities WHERE student_id = ? ORDER BY date_to DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
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
        <title>Upload Files</title>
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
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Upload Files</h1>
                    <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                    </button>
                </header>
                <main class="p-6">
                    <div id="successAlert" class="hidden mb-4 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700"></div>
                    <div id="errorAlert" class="hidden mb-4 p-4 rounded-lg bg-red-100 border border-red-400 text-red-700"></div>
                    
                    <!-- Preview Modal -->
                    <div id="previewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
                        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                            <div class="mt-3">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">File Preview</h3>
                                <div class="mt-2 px-7 py-3">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">File Name: <span id="previewFileName" class="text-gray-700 dark:text-gray-300"></span></p>
                                    <div class="flex justify-end space-x-3">
                                        <button id="cancelUpload" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                                            Cancel
                                        </button>
                                        <button id="confirmUpload" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                            Upload
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Your Activities</h2>
                        <?php if (empty($activities)): ?>
                            <p class="text-gray-600 dark:text-gray-400">No activities found.</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($activities as $activity): ?>
                                    <div class="border dark:border-gray-700 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="font-semibold text-gray-800 dark:text-white"><?php echo htmlspecialchars($activity['activity_type']); ?></h3>
                                                <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($activity['event_name']); ?></p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    <?php echo date('M d, Y', strtotime($activity['date_from'])); ?> - 
                                                    <?php echo date('M d, Y', strtotime($activity['date_to'])); ?>
                                                </p>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <div class="ml-4">
                                                    <span class="px-3 py-1.5 rounded-full text-sm font-semibold
                                                        <?php
                                                        if ($activity['file_path']) {
                                                            echo 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100';
                                                        } elseif ($activity['status'] == 'pending') {
                                                            echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100';
                                                        } else {
                                                            echo 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';
                                                        }
                                                        ?>">
                                                        <?php
                                                        if ($activity['file_path']) {
                                                            echo 'Uploaded';
                                                        } elseif ($activity['status'] == 'pending') {
                                                            echo 'Pending';
                                                        } else {
                                                            echo 'Not Uploaded';
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                <?php if (!empty($activity['file_path'])): ?>
                                                    <a href="view_file.php?file=<?php echo urlencode($activity['file_path']); ?>" target="_blank" 
                                                       class="text-blue-500 hover:text-blue-700">
                                                        <?php
                                                        $file_extension = strtolower(pathinfo($activity['file_path'], PATHINFO_EXTENSION));
                                                        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                            echo '<i class="fas fa-image"></i> View Image';
                                                        } else {
                                                            echo '<i class="fas fa-file-pdf"></i> View PDF';
                                                        }
                                                        ?>
                                                    </a>
                                                <?php endif; ?>
                                                <form action="process_upload.php" method="post" enctype="multipart/form-data" class="inline">
                                                    <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                                                    <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.gif" class="hidden" id="file-<?php echo $activity['id']; ?>">
                                                    <button type="button" onclick="document.getElementById('file-<?php echo $activity['id']; ?>').click()" 
                                                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                                        <?php echo empty($activity['file_path']) ? 'Upload File' : 'Change File'; ?>
                                                    </button>
                                                    <button type="submit" class="hidden" id="submit-<?php echo $activity['id']; ?>">Submit</button>
                                                </form>
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

            // File upload handling
            let currentForm = null;
            let currentFile = null;

            document.querySelectorAll('input[type="file"]').forEach(input => {
                input.addEventListener('change', function(e) {
                    if (this.files.length > 0) {
                        currentFile = this.files[0];
                        currentForm = this.closest('form');
                        
                        // Show preview modal
                        const modal = document.getElementById('previewModal');
                        const fileName = document.getElementById('previewFileName');
                        fileName.textContent = currentFile.name;
                        modal.classList.remove('hidden');
                    }
                });
            });

            // Cancel upload
            document.getElementById('cancelUpload').addEventListener('click', function() {
                const modal = document.getElementById('previewModal');
                modal.classList.add('hidden');
                if (currentForm) {
                    const fileInput = currentForm.querySelector('input[type="file"]');
                    fileInput.value = '';
                }
                currentForm = null;
                currentFile = null;
            });

            // Confirm upload
            document.getElementById('confirmUpload').addEventListener('click', function() {
                if (currentForm && currentFile) {
                    const formData = new FormData(currentForm);
                    
                    fetch('process_upload.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(result => {
                        const successAlert = document.getElementById('successAlert');
                        const errorAlert = document.getElementById('errorAlert');
                        const modal = document.getElementById('previewModal');
                        
                        if (result.includes('success')) {
                            successAlert.textContent = 'File uploaded successfully!';
                            successAlert.classList.remove('hidden');
                            errorAlert.classList.add('hidden');
                            modal.classList.add('hidden');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            errorAlert.textContent = result;
                            errorAlert.classList.remove('hidden');
                            successAlert.classList.add('hidden');
                            modal.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        const errorAlert = document.getElementById('errorAlert');
                        const modal = document.getElementById('previewModal');
                        errorAlert.textContent = 'An error occurred while uploading the file.';
                        errorAlert.classList.remove('hidden');
                        document.getElementById('successAlert').classList.add('hidden');
                        modal.classList.add('hidden');
                    });
                }
            });

            // Close modal when clicking outside
            document.getElementById('previewModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                    if (currentForm) {
                        const fileInput = currentForm.querySelector('input[type="file"]');
                        fileInput.value = '';
                    }
                    currentForm = null;
                    currentFile = null;
                }
            });
        </script>
    </body>
</html>