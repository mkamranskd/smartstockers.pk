<?php
require_once '../php_action/db_connect.php'; 

if (isset($_POST['order_id'])) {
$id = intval($_POST['order_id']);
$stmt = $conn->prepare("DELETE FROM print_list WHERE order_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
echo "Deleted";
}
?>