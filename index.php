<?php
// Set session cookie to persist for 30 days
session_set_cookie_params(30 * 24 * 60 * 60);
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
?>