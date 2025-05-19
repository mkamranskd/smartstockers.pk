<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (isset($_GET['store_link']) && isset($_GET['access_token'])) {
    $store_link = rtrim($_GET['store_link'], '/');
    $access_token = $_GET['access_token'];

    $api_url = "$store_link/admin/api/2023-07/products.json"; // Shopify API endpoint for products

    // Function to fetch data from Shopify API
    function fetch_data($url, $access_token) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Shopify-Access-Token: $access_token",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for local dev

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if(curl_errno($ch)) {
            // Log cURL error
            error_log('cURL Error: ' . curl_error($ch));
        }

        curl_close($ch);

        // Check if the request was successful
        if ($http_code !== 200) {
            error_log("Shopify API returned status code $http_code");
            return null;
        }

        return json_decode($response, true);
    }

    // Fetch the products from Shopify
    $products_response = fetch_data($api_url, $access_token);
    $all_products = [];

    if ($products_response && isset($products_response['products']) && is_array($products_response['products'])) {
        foreach ($products_response['products'] as $product) {
            $product_data = [
                "id" => $product['id'] ?? '',
                "name" => $product['title'] ?? '',
                "sku" => $product['variants'][0]['sku'] ?? '',
                "price" => $product['variants'][0]['price'] ?? '',
                "stock_status" => $product['variants'][0]['inventory_quantity'] > 0 ? 'instock' : 'outofstock',
                "categories" => isset($product['product_type']) ? [$product['product_type']] : [],
                "image" => isset($product['images'][0]['src']) ? $product['images'][0]['src'] : '',
                "description" => strip_tags($product['body_html'] ?? ''),
            ];

            $all_products[] = $product_data;

            // Fetch variations if available
            if (count($product['variants']) > 1) {
                foreach ($product['variants'] as $variant) {
                    if ($variant['id'] != $product['variants'][0]['id']) { // Skip the main product variant
                        $variation_data = [
                            "id" => $variant['id'],
                            "name" => $product['title'] . " - " . implode(", ", array_filter([$variant['title']])),
                            "sku" => $variant['sku'] ?? '',
                            "price" => $variant['price'] ?? '',
                            "stock_status" => $variant['inventory_quantity'] > 0 ? 'instock' : 'outofstock',
                            "categories" => $product_data['categories'],
                            "image" => $product_data['image'],
                            "description" => "(Variation of " . $product['title'] . ")"
                        ];
                        $all_products[] = $variation_data;
                    }
                }
            }
        }

        echo json_encode(["products" => $all_products]);
    } else {
        // Log any response issues
        error_log('Shopify API Response Error: ' . json_encode($products_response));
        echo json_encode(["error" => "Failed to fetch products"]);
    }
} else {
    echo json_encode(["error" => "Invalid parameters"]);
}
?>
