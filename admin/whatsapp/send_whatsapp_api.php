<?php
require_once 'send_whatsapp.php'; // Include the function file

// Allow cross-origin (optional for testing)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

echo json_encode(["message" => "Hello, world!"]);

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);  // Get the JSON body data

    // Extract phone and message from the POST data
    $phone = $data['phone'] ?? null;
    $message = $data['message'] ?? null;

    // Validate phone and message parameters
    if ($phone && $message) {
        $response = sendWhatsAppMessage($phone, $message); // Call function
        echo json_encode($response);  // Return response to JS
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing phone or message']);
    }
}