<?php
require_once '../php_action/db_connect.php';

if (isset($_GET['client_id'])) {
    $client_id = mysqli_real_escape_string($connect, $_GET['client_id']);

    $query = "SELECT store_link, consumer_key, consumer_secret, store_type, access_token FROM vendor_stores WHERE client_id = '$client_id' LIMIT 1";
    $result = mysqli_query($connect, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "No store found"]);
    }
}
?>
