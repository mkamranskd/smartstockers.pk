<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $delivery_type = $_POST["delivery_type"];
    $api_token = $_POST["api_token"];

    // API URLs for different delivery services
    $api_urls = [
        "TCS" => "https://api.tcs.com/validate", // Replace with actual TCS API
        "PostEx" => "https://api.postex.pk/services/integration/api/",
        "M&P" => "https://api.mnpcourier.com/validate", // Replace with actual M&P API
        "Leopards" => "https://merchantapistaging.leopardscourier.com/api/" // Replace with actual Leopards API
    ];

    if (!isset($api_urls[$delivery_type])) {
        echo json_encode(["success" => false, "message" => "Invalid Delivery Type"]);
        exit;
    }

    $api_url = $api_urls[$delivery_type];

    // Send request to the respective API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $api_token,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check response
    if ($http_code == 200) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
}
?>
