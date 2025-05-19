<?php
require_once '../php_action/db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['client_id']) || empty($_GET['client_id'])) {
    echo json_encode(["error" => "Client ID is required"]);
    exit;
}

$client_id = mysqli_real_escape_string($connect, $_GET['client_id']);

// Fetch all products for the selected client
$query = "SELECT product_id, product_name, price, weight, stock_quantity FROM products WHERE client_id = '$client_id'";
$result = mysqli_query($connect, $query);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode($products);
exit;
