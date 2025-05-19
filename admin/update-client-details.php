<?php
require_once '../php_action/db_connect.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['client_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$sql = "UPDATE clients SET vendor_name = ?, email = ?, phone_number = ?, cnic = ?, address = ?, rate_of_product = ? WHERE client_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param(
    "sssssss",
    $data['vendor_name'],
    $data['email'],
    $data['phone_number'],
    $data['cnic'],
    $data['address'],
    $data['rate_of_product'],
    $data['client_id']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}