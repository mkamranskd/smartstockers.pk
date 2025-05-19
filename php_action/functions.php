<?php
require_once '../php_action/db_connect.php';

function addNotification($user_id, $message) {
    global $connect;
    $stmt = $connect->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    return $stmt->execute();
}

function getUnreadNotifications($user_id) {
    global $connect;
    $stmt = $connect->prepare("SELECT id, message, created_at FROM notifications WHERE is_read = 0 ORDER BY created_at DESC");

    $stmt->execute();
    return $stmt->get_result();
}

function markNotificationsAsRead($user_id) {
    global $connect;
    $stmt = $connect->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}