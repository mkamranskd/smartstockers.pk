<?php
require_once '../php_action/db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['client_id']) || empty($_GET['client_id'])) {
    echo json_encode(["error" => "No Client ID provided"]);
    exit;
}

$client_id = mysqli_real_escape_string($connect, $_GET['client_id']);

// Generate new Product ID based on Client ID
$query = "SELECT MAX(product_id) AS max_id FROM products WHERE client_id = '$client_id'";
$result = mysqli_query($connect, $query);
$row = mysqli_fetch_assoc($result);

$new_product_id = $client_id . "00001"; // Default if no products exist

if ($row && $row['max_id']) {
    $num = (int)substr($row['max_id'], strlen($client_id));
    $num++;
    $new_product_id = $client_id . str_pad($num, 5, '0', STR_PAD_LEFT);
}

echo json_encode([
    'client_id' => $client_id,
    'product_id' => $new_product_id
]);
?>
