<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Activity Participation</title>
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
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Activity Participation</h1>
                    <div class="flex flex-col items-end">
                        <?php
                        session_start();
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
                    <div id="successAlert" class="hidden mb-4 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700"></div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-3xl mx-auto">
                        <form id="activityForm" action="form.php" method="post" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="mb-4">
                                        <label for="activity" class="block text-gray-700 dark:text-gray-300 mb-2">Activity</label>
                                        <select id="activity" name="activity" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="ex-curricular">Extra-curricular</option>
                                            <option value="co-curricular">Co-curricular</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label for="date-from" class="block text-gray-700 dark:text-gray-300 mb-2">Date of Participation - From</label>
                                        <input type="date" id="date-from" name="date-from" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-4">
                                        <label for="date-to" class="block text-gray-700 dark:text-gray-300 mb-2">To</label>
                                        <input type="date" id="date-to" name="date-to" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div class="mb-4">
                                        <label for="college" class="block text-gray-700 dark:text-gray-300 mb-2">College of Participation</label>
                                        <input type="text" id="college" name="college" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div class="mb-4">
                                        <label for="activity-type" class="block text-gray-700 dark:text-gray-300 mb-2">Event</label>
                                        <select id="activity-type" name="activity-type" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="technical">Technical</option>
                                            <option value="non-technical">Non-Technical</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label for="event-name" class="block text-gray-700 dark:text-gray-300 mb-2">Event Name</label>
                                        <input type="text" id="event-name" name="event-name" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div class="mb-4">
                                        <label for="award" class="block text-gray-700 dark:text-gray-300 mb-2">Awards/Prizes won</label>
                                        <select id="award" name="award" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 w-full">Submit</button>
                        </form>
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
            // AJAX form submission with success alert
            document.getElementById('activityForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    document.getElementById('successAlert').textContent = 'Form submitted successfully!';
                    document.getElementById('successAlert').classList.remove('hidden');
                    form.reset();
                })
                .catch(error => {
                    document.getElementById('successAlert').textContent = 'An error occurred while submitting the form.';
                    document.getElementById('successAlert').classList.remove('hidden');
                });
            });
        </script>
    </body>
</html>
            
