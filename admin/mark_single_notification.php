<?php
require_once '../php_action/db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $connect->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Redirect back to the same page
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;