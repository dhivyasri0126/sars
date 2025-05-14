<?php
session_start();             // Start the session
session_unset();             // Remove all session variables
session_destroy();           // Destroy the session

header("Location: ../../auth/student_login.php");  // Redirect to the correct login file
exit();
?>
