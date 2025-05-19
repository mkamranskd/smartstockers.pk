<?php
require_once '../php_action/db_connect.php';

if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
    echo json_encode(['error' => 'Invalid order ID.']);
    exit;
}

$orderId = intval($_POST['order_id']);
$response = [];

// Fetch order
$orderQuery = mysqli_query($connect, "SELECT * FROM orders WHERE order_id = $orderId");

if (!$orderQuery || mysqli_num_rows($orderQuery) == 0) {
    echo json_encode(['error' => 'Order not found.']);
    exit;
}

$order = mysqli_fetch_assoc($orderQuery);

// Check if order is confirmed
if ($order['status'] == 0) {
    echo json_encode(['error' => 'Order has not been confirmed yet.']);
    exit;
}

// Check if order is confirmed
if ($order['status'] == 6) {
    echo json_encode(['error' => 'Order has been already returned or Cancelled.']);
    exit;
}
// Prepare order data
$response = [
    'client_id' => $order['client_id'],
    'order_id' => $order['order_id'],
    'customer_name' => $order['customer_name'],
    'customer_phone' => $order['customer_phone'],
    'delivery_address' => $order['delivery_address'],
    'city' => $order['city'],
    'order_date' => $order['order_date'],
    'order_tracking_id' => $order['order_tracking_id'],
    'total_amount' => $order['total_amount'],
    'items' => []
];

// Fetch order items with product names
$itemsQuery = mysqli_query($connect, "
    SELECT oi.*, p.product_name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.product_id 
    WHERE oi.order_id = $orderId
");

while ($item = mysqli_fetch_assoc($itemsQuery)) {
    $response['items'][] = $item;
}

echo json_encode($response);