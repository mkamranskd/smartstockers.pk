<?php
require_once '../php_action/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['client_id']) || !isset($data['delivery_services'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$client_id = $data['client_id'];
$delivery_services = $data['delivery_services'];

// Delete all current delivery services for the client first
$delete_stmt = $connect->prepare("DELETE FROM vendor_delivery_services WHERE client_id = ?");
$delete_stmt->bind_param("s", $client_id);
$delete_stmt->execute();

// Insert new delivery services
$insert_stmt = $connect->prepare("INSERT INTO vendor_delivery_services (client_id, delivery_type, api_token) VALUES (?, ?, ?)");
foreach ($delivery_services as $service) {
    $insert_stmt->bind_param("sss", $client_id, $service['delivery_type'], $service['api_token']);
    $insert_stmt->execute();
}

echo json_encode(['success' => true]);
?>