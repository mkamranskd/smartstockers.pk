<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../php_action/db_connect.php'; // Database connection
require_once '../php_action/functions.php';
session_start();
$user_id = intval($_SESSION['userId'] ?? 0);
echo "Fetched From All Woocomerce Successfullly!" ;

// Fetch WooCommerce API credentials
$sql = "SELECT client_id, store_link, consumer_key, consumer_secret FROM vendor_stores";
$result = $connect->query($sql);

// Function to fetch all WooCommerce orders (handles pagination)
function get_all_woocommerce_orders($api_url, $consumer_key, $consumer_secret) {
$all_orders = [];
$page = 1;

do {
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url . "?per_page=100&page=" . $page);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, $consumer_key . ":" . $consumer_secret);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
break;
}

$orders = json_decode($response, true);
if (!empty($orders)) {
$all_orders = array_merge($all_orders, $orders);
}

$page++;
} while (!empty($orders));

return $all_orders;
}

// Function to map WooCommerce order status to custom system status
function map_order_status_to_status($order_status) {
$status_map = [
'on-hold' => 0,
'processing' => 0,
'pending' => 0,
'completed' => 4,
'refunded' => 6,
'failed' => 6,
'draft' => 6,
'cancelled' => 6
];
return $status_map[$order_status] ?? 0;
}

function escape($value, $connect) {
return mysqli_real_escape_string($connect, $value);
}

if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
$client_id = $row['client_id'];
$store_url = rtrim($row['store_link'], "/");
$consumer_key = $row['consumer_key'];
$consumer_secret = $row['consumer_secret'];

$api_url = $store_url . "/wp-json/wc/v3/orders";
$orders = get_all_woocommerce_orders($api_url, $consumer_key, $consumer_secret);

foreach ($orders as $order) {
$store_order_id = $order['id']; // WooCommerce order ID
$status = map_order_status_to_status($order['status']);
$order_status = $order['status'];

// Check if the store_order_id already exists for this client
$check_order_query = "SELECT order_id FROM orders WHERE store_order_id = '{$store_order_id}' AND client_id =
'{$client_id}'";
$check_order_result = $connect->query($check_order_query);

if ($check_order_result->num_rows === 0) {
// Generate new internal order_id
$new_order_id_result = $connect->query("SELECT MAX(order_id) AS max_id FROM orders");
$new_order_id_row = $new_order_id_result->fetch_assoc();
$new_order_id = $new_order_id_row['max_id'] + 1;

// Fetch tracking ID
$tracking_id = "N/A";
if (!empty($order['meta_data'])) {
foreach ($order['meta_data'] as $meta) {
if ($meta['key'] == '_tracking_number') {
$tracking_id = $meta['value'];
break;
}
}
}

// Prepare order items
$order_items = [];
foreach ($order['line_items'] as $item) {
$product_store_id = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];
$image_url = $item['image']['src'] ?? '';
$product_name = $item['name'];
$price = $item['price'];
$quantity = $item['quantity'];
$subtotal = $item['subtotal'];

// Check if product exists
$product_query = "SELECT product_id FROM products WHERE product_store_id = '{$product_store_id}'";
$product_result = $connect->query($product_query);

if ($product_result->num_rows > 0) {
$product_id = $product_result->fetch_assoc()['product_id'];
} else {
// Product insertion can go here if needed
$product_id = 0;
}

$order_items[] = [
'product_id' => $product_id,
'product_store_id' => $product_store_id,
'product_name' => $product_name,
'quantity' => $quantity,
'price' => $price,
'subtotal' => $subtotal,
'image_url' => $image_url
];
}

if (empty($order_items)) {
$order_items[] = [
'product_id' => 0,
'product_store_id' => 'N/A',
'product_name' => 'Unknown Product',
'quantity' => 1,
'price' => 0,
'subtotal' => 0,
'image_url' => ''
];
}

// Customer/billing data
$billing_name = escape("{$order['billing']['first_name']} {$order['billing']['last_name']}", $connect);
$billing_phone = escape($order['billing']['phone'] ?? "N/A", $connect);
$billing_email = escape($order['billing']['email'] ?? "N/A", $connect);
$billing_address = escape("{$order['billing']['address_1']}, {$order['billing']['city']},
{$order['billing']['country']}", $connect);
$shipping_address = escape("{$order['shipping']['address_1']}, {$order['shipping']['city']},
{$order['shipping']['country']}", $connect);
$delivery_address = $shipping_address;
$payment_method = escape($order['payment_method_title'] ?? "Unknown", $connect);
$order_note = escape($order['customer_note'], $connect);

// Insert new order
$insert_order_query = "INSERT INTO orders
(order_id, store_order_id, order_date, client_id, customer_name, customer_phone, customer_email, billing_address,
shipping_address, delivery_address, city, status, order_status,
order_notes, order_tracking_id, updated_at, created_at, total_amount, payment_method)
VALUES
('{$new_order_id}', '{$store_order_id}', '{$order['date_created']}', '{$client_id}',
'{$billing_name}', '{$billing_phone}', '{$billing_email}',
'{$billing_address}', '{$shipping_address}', '{$delivery_address}',
'{$order['billing']['city']}',
'{$status}', '{$order_status}', '{$order_note}', '{$tracking_id}', NOW(), NOW(),
'{$order['total']}', '{$payment_method}')";

$connect->query($insert_order_query);
if ($connect->affected_rows > 0) {
    $notification_message = "New order #{$new_order_id} has been fetched for {$client_id}.";
    addNotification(0, $notification_message);
}


// Insert order items
foreach ($order_items as $item) {
$order_item_query = "INSERT INTO order_items
(order_id, client_id, product_id, product_store_id, product_name, quantity, price, subtotal, image_url, created_at)
VALUES
('{$new_order_id}', '{$client_id}', '{$item['product_id']}', '{$item['product_store_id']}',
'{$item['product_name']}', '{$item['quantity']}', '{$item['price']}', '{$item['subtotal']}', '{$item['image_url']}',
NOW())
ON DUPLICATE KEY UPDATE
quantity = VALUES(quantity), subtotal = VALUES(subtotal), image_url = VALUES(image_url)";
$connect->query($order_item_query);
}
}
}
}
}


$connect->close();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>