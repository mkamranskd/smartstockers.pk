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

// Step 1: Get API token for this client
$query = "SELECT api_token FROM vendor_delivery_services WHERE client_id = ? AND delivery_type = 'PostEx'";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(["error" => "SQL Prepare Failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("s", $client_id); // "s" stands for string

$stmt->execute();
$result = $stmt->get_result();  

if (!$row = $result->fetch_assoc()) {
    echo json_encode(["error" => "API Token not found for client_id: " . $client_id]);
    exit;
}

$api_token = $row["api_token"];

// Step 2: Call PostEx API to get operational cities
$url = "https://api.postex.pk/services/integration/api/order/v2/get-operational-city";
$headers = [
    "token: $api_token"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Step 3: Parse response
if ($httpCode === 200 && $response) {
    $data = json_decode($response, true);

    if ($data && isset($data["dist"])) {
        $cities = array_map(function ($city) {
            return $city["operationalCityName"];
        }, $data["dist"]);

        echo json_encode([
            "status" => "success",
            "cities" => $cities
        ]);
    } else {
        echo json_encode(["error" => "Invalid response format from PostEx"]);
    }
} else {
    echo json_encode(["error" => "Failed to fetch cities from PostEx", "http_code" => $httpCode]);
}
?>