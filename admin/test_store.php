<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $store_type = $_POST["store_type"];
    $store_link = rtrim($_POST["store_link"], "/"); // Remove trailing slash if any
    $consumer_key = $_POST["consumer_key"];
    $consumer_secret = $_POST["consumer_secret"];

    // Define API URLs based on store type
    if ($store_type == "WooCommerce") {
        $api_url = "$store_link/wp-json/wc/v3/orders?consumer_key=$consumer_key&consumer_secret=$consumer_secret";
    } elseif ($store_type == "Shopify") {
        $api_url = "$store_link/admin/api/2023-01/orders.json";
    } else {
        echo json_encode(["success" => false, "message" => "Invalid Store Type"]);
        exit;
    }

    // Make API request to validate credentials
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$consumer_key:$consumer_secret"); // WooCommerce authentication
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check API response
    if ($http_code == 200) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
}
?>
