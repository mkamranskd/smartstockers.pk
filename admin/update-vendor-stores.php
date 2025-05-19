<?php
require_once '../php_action/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['client_id']) || !isset($data['stores'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$client_id = $data['client_id'];
$stores = $data['stores'];

// Delete all current vendor stores for the client first
$delete_stmt = $connect->prepare("DELETE FROM vendor_stores WHERE client_id = ?");
$delete_stmt->bind_param("s", $client_id);
$delete_stmt->execute();

// Insert new vendor stores
$insert_stmt = $connect->prepare("INSERT INTO vendor_stores (client_id, store_link, store_type, consumer_key, consumer_secret, access_token) VALUES (?, ?, ?,  ?, ?, ?)");

foreach ($stores as $store) {
    $insert_stmt->bind_param("ssssss", $client_id, $store['store_link'], $store['store_type'], $store['consumer_key'], $store['consumer_secret'], $store['access_token']);
    $insert_stmt->execute();
}

echo json_encode(['success' => true]);
?>