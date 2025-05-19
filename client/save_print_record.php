<?php
require_once '../php_action/db_connect.php'; 

if (isset($_POST['order_id'])) {
    $id = intval($_POST['order_id']);
    $stmt = $conn->prepare("INSERT IGNORE INTO print_list (order_id) VALUES (?)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "OK";
}
?>