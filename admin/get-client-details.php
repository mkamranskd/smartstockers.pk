<?php
ob_clean();
header('Content-Type: application/json');
require_once '../php_action/db_connect.php';

if (!isset($_GET['client_id'])) {
    echo json_encode(['error' => 'Missing client_id']);
    exit;
}

$client_id = intval($_GET['client_id']);
$sql = "SELECT * FROM clients WHERE client_id = $client_id";
$result = mysqli_query($connect, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Client not found']);
}
?>