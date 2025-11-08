<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    // User not logged in — redirect to login page
    header("Location: ../login.html");
    exit();
}
?>
