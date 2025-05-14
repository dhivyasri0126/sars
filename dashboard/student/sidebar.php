    <link rel="stylesheet" href="sidebar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
<aside id="sidebar">
    <div class="sidebar-title">
        <div class="sidebar-brand">
            <img src="logo.png" alt="Logo" style="height: 50px; width: 50px; border-radius: 5px; margin-left: 70px;">
            <p class="font-weight-bold">STUDENT PORTAL</p>
        </div>
        <span class="material-symbols-outlined" onclick="closeSidebar()">close</span>
    </div>
    <ul class="sidebar-list">
        <li class="sidebar-list-item"><a href="index.php"><span class="material-symbols-outlined">dashboard</span>Dashboard</a></li>
        <li class="sidebar-list-item"><a href="activity.php"><span class="material-symbols-outlined">edit_square</span>Activity Participation</a></li>
        <li class="sidebar-list-item"><a href="upload.php"><span class="material-symbols-outlined">upload_file</span>Upload</a></li>
        <li class="sidebar-list-item"><a href="calendar.php"><span class="material-symbols-outlined">calendar_month</span>Activity Calendar</a></li>
        <li class="sidebar-list-item"><a href="logout.php" onclick="return confirmLogout();"><span class="material-symbols-outlined">logout</span>Logout </a></li>

    </ul>
</aside>
<script>
var sidebarOpen = false;
var sidebar = document.getElementById("sidebar");
function openSidebar(){
    if(!sidebarOpen){
        sidebar.classList.add("sidebar-responsive");
        sidebarOpen = true;
    }
}
function closeSidebar(){
    if(!sidebarOpen){
        sidebar.classList.remove("sidebar-responsive");
        sidebarOpen = false;
    }
}
function confirmLogout() {
    if (confirm("Are you sure you want to log out?")) {
        window.location.href = '../auth/student_login.html';
        return true;
    }
    return false;
}
</script>
