<?php
// Allow cross-origin if needed
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Read raw input from POST
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Debug: log raw input if needed
file_put_contents("leopards-debug.json", $rawData);

// Validate required fields
if (!isset($data['api_key']) || empty($data['api_key'])) {
    echo json_encode(["status" => 0, "error" => "API Key is required"]);
    exit;
}

if (!isset($data['api_password']) || empty($data['api_password'])) {
    echo json_encode(["status" => 0, "error" => "API Password is required"]);
    exit;
}

// API endpoint
$apiUrl = 'https://merchantapi.leopardscourier.com/api/bookPacket/format/json/';

// Initialize cURL
$curl = curl_init($apiUrl);

// cURL settings
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

// Execute cURL
$response = curl_exec($curl);

// Handle errors
if (curl_errno($curl)) {
    echo json_encode(["status" => 0, "error" => "cURL Error: " . curl_error($curl)]);
    curl_close($curl);
    exit;
}

// Decode and return response
curl_close($curl);
$responseData = json_decode($response, true);

// Handle non-JSON response
if ($responseData === null) {
    echo json_encode([
        "status" => 0,
        "error" => "Invalid response from Leopards API",
        "raw_response" => $response
    ]);
    exit;
}
file_put_contents("leopards-debug.json", json_encode([
    'raw' => $rawData,
    'parsed' => $data
]));

echo json_encode($responseData);