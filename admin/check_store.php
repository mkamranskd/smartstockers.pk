<?php
require_once '../php_action/db_connect.php';

$client_id = isset($_GET['client_id']) ? mysqli_real_escape_string($connect, $_GET['client_id']) : '';

$response = ['store_link' => null];

if (!empty($client_id)) {
    $query = "SELECT store_link,store_type FROM vendor_stores WHERE client_id = '$client_id' LIMIT 1";
    $result = mysqli_query($connect, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $response['store_link'] = $row['store_link'];
        $response['store_type'] = $row['store_type'];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
