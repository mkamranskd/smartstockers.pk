<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../php_action/db_connect.php';

// Ensure $conn is defined
if (!isset($conn) && isset($connect)) {
    $conn = $connect;
}

if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

if (!isset($_GET["clientId"])) {
    echo json_encode(["error" => "Invalid request, clientId missing"]);
    exit;
}

$client_id = $_GET["clientId"];

error_log("Received clientId: " . $client_id);

$query = "SELECT api_token FROM vendor_delivery_services WHERE client_id = ? AND delivery_type = 'Leopards'";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(["error" => "SQL Prepare Failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("s", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(["api_token" => $row["api_token"]]);
} else {
    echo json_encode(["error" => "API Token not found for client_id: " . $client_id]);
}
?>