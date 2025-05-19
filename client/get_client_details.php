<?php
// get_client_details.php

require_once '../php_action/db_connect.php';

if (isset($_GET['client_id'])) {
    $client_id = $_GET['client_id'];

    // SQL query to fetch the client details
    $sql = "SELECT * FROM clients WHERE client_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
$client = $result->fetch_assoc();

    if ($client) {
        echo json_encode($client); // Return the data as a JSON object
    } else {
        echo json_encode(['error' => 'Client not found']);
    }
} else {
    echo json_encode(['error' => 'Client ID not provided']);
}
?>