<?php
require_once '../php_action/db_connect.php';

// Fetching vendors from the clients table
$query = "SELECT client_id, vendor_name, email FROM clients";
$result = mysqli_query($connect, $query);

$vendors = [];
while ($row = mysqli_fetch_assoc($result)) {
    $vendors[] = $row;
}

echo json_encode($vendors);
?>
