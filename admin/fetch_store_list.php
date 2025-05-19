<?php
require_once '../php_action/db_connect.php'; // uses $connect

$sql = "SELECT * FROM delivery_services_list ORDER BY service_name ASC";
$result = mysqli_query($connect, $sql);

$services = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($services);