<?php
require_once '../php_action/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $orderId = intval($_POST['order_id']);
    $newStatus = intval($_POST['new_status']);

    // Fetch order details
    $orderQuery = "SELECT client_id, order_id FROM orders WHERE order_id = $orderId";
    $orderResult = mysqli_query($connect, $orderQuery);
    $order = mysqli_fetch_assoc($orderResult);
   
    if (!$order) {
        echo "Order not found.";
        exit;
    }   

    $clientId = $order['client_id'];
    $woocommerceOrderId = $order['order_id']; // WooCommerce order ID
   
    
    // Fetch all products in this order
    $orderItemsQuery = "SELECT product_id, quantity FROM order_items WHERE order_id = $orderId";
    $orderItemsResult = mysqli_query($connect, $orderItemsQuery);
    
    

    if (!$orderItemsResult || mysqli_num_rows($orderItemsResult) == 0) {
        echo "No products found in this order.";
        exit;
    }
    $productStockCheck = true; // Flag to check stock availability
    $orderItems = [];

    while ($row = mysqli_fetch_assoc($orderItemsResult)) {
        $productId = $row['product_id'];
        $quantity = $row['quantity'];

        // Fetch current stock for this product_id
        $stockQuery = "SELECT stock_quantity FROM products WHERE product_id = '$productId'";
        $stockResult = mysqli_query($connect, $stockQuery);
        $stockRow = mysqli_fetch_assoc($stockResult);
        $currentStock = $stockRow ? intval($stockRow['stock_quantity']) : 0;

        if ($newStatus == 1 && $currentStock < $quantity) {
            $productStockCheck = false;
        }

        $orderItems[] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'current_stock' => $currentStock
        ];
    }

    // If marking as shipped, ensure all products have enough stock
    if ($newStatus == 1 && !$productStockCheck) {
        echo "One or more products are out of stock. Update failed.";
        exit;
    }

   

    // Fetch WooCommerce API credentials
    $storeQuery = "SELECT store_link, consumer_key, consumer_secret FROM vendor_stores WHERE client_id = '$clientId'";
    $storeResult = mysqli_query($connect, $storeQuery);
    $storeRow = mysqli_fetch_assoc($storeResult);

    if ($storeRow) {
        $storeLink = rtrim($storeRow['store_link'], '/');
        $consumerKey = $storeRow['consumer_key'];
        $consumerSecret = $storeRow['consumer_secret'];

        // WooCommerce API URL
        $woo_url = "{$storeLink}/wp-json/wc/v3/orders/{$woocommerceOrderId}";

        // Map newStatus to WooCommerce status
        $wooStatus = "";
        if ($newStatus == 1) {
            $wooStatus = "processing"; // Order processing
        } elseif ($newStatus == 2) {
            $wooStatus = "completed"; // Order completed
        }
        elseif ($newStatus == 6) {
            $wooStatus = "cancelled"; // Order cancelled
        }

        if ($wooStatus) {
            // Update WooCommerce Order Status
            $data = json_encode(["status" => $wooStatus]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $woo_url);
            curl_setopt($ch, CURLOPT_USERPWD, "$consumerKey:$consumerSecret");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200) {
                echo "success";
                 // Deduct stock if order is marked as shipped
    if ($newStatus == 1) {
        foreach ($orderItems as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];

            $updateStock = "UPDATE products SET stock_quantity = stock_quantity - $quantity WHERE product_id = '$productId'";
            mysqli_query($connect, $updateStock);
        }
    }

    // Restore stock if order is cancelled
    if ($newStatus == 6) {
        foreach ($orderItems as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];

            $updateStock = "UPDATE products SET stock_quantity = stock_quantity + $quantity WHERE product_id = '$productId'";
            mysqli_query($connect, $updateStock);
        }
    }

    // Update order status in local database
    $updateQuery = "UPDATE orders SET status = $newStatus WHERE order_id = '$orderId'";
    if (!mysqli_query($connect, $updateQuery)) {
        echo "Error updating order: " . mysqli_error($connect);
        exit;
    }
            } else {
                echo "WooCommerce update failed: " . $response;
            }
        } else {
            echo "Order status not mapped to WooCommerce.";
        }
    } else {
        echo "WooCommerce credentials not found.";
    }
}

?>