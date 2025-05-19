<?php
require_once '../php_action/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['client_id']) && !empty($data['product_id']) && !empty($data['product_name']) && !empty($data['price'])) {
    $client_id = mysqli_real_escape_string($connect, $data['client_id']);
    $product_id = mysqli_real_escape_string($connect, $data['product_id']);
    $product_name = mysqli_real_escape_string($connect, $data['product_name']);
    $price = mysqli_real_escape_string($connect, $data['price']);

    $query = "INSERT INTO products (client_id, product_id, product_name, price) VALUES ('$client_id', '$product_id', '$product_name', '$price')";
    
    if (mysqli_query($connect, $query)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => mysqli_error($connect)]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid product data."]);
}
?>
