<?php
require_once '../php_action/db_connect.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$response = ['success' => false];

if (!isset($data['client_id']) || !isset($data['username']) || !isset($data['email'])) {
    $response['error'] = 'Missing required fields';
    echo json_encode($response);
    exit;
}

$client_id = $data['client_id'];
$username = $data['username'];
$email = $data['email'];
$password = isset($data['password']) && $data['password'] !== '' ? md5($data['password']) : null;

// Check if user exists
$stmt = $connect->prepare("SELECT user_id FROM users WHERE client_id = ?");
$stmt->bind_param("s", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['user_id'];

    if ($password) {
        $update = $connect->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE user_id = ?");
        $update->bind_param("sssi", $username, $email, $password, $user_id);
    } else {
        $update = $connect->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
        $update->bind_param("ssi", $username, $email, $user_id);
    }

    if ($update->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = 'Database update failed';
    }
} else {
    $response['error'] = 'User not found';
}

echo json_encode($response);