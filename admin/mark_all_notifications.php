<?php
session_start();
require_once '../php_action/db_connect.php';

// Update all unread notifications (for all users)
$sql = "UPDATE notifications SET is_read = 1 WHERE is_read = 0";

if ($connect->query($sql) === TRUE) {
    // Success — redirect back to the previous page
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    echo "Failed to update notifications: " . $connect->error;
}
?>