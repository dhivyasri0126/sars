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
        <title>Upload File</title>
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
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Upload File</h1>
                    <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                    </button>
                </header>
                <main class="p-6">
                    <div id="successAlert" class="hidden mb-4 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700"></div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 max-w-xl mx-auto">
                        <form id="uploadForm" action="submit.php" method="post" enctype="multipart/form-data" class="space-y-6">
                            <div class="mb-4">
                                <label for="fileInput" class="block text-gray-700 dark:text-gray-300 mb-2">Choose file (PDF only):</label>
                                <input type="file" id="fileInput" name="file" accept=".pdf" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 w-full">Upload</button>
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
            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    document.getElementById('successAlert').textContent = 'File uploaded successfully!';
                    document.getElementById('successAlert').classList.remove('hidden');
                    form.reset();
                })
                .catch(error => {
                    document.getElementById('successAlert').textContent = 'An error occurred while uploading the file.';
                    document.getElementById('successAlert').classList.remove('hidden');
                });
            });
        </script>
    </body>
</html>