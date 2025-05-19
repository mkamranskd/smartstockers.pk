<?php
require_once '../php_action/db_connect.php';

if (isset($_GET['product_store_id'])) {
    $product_store_id = $_GET['product_store_id'];
    $query = "SELECT * FROM products WHERE product_store_id = '$product_store_id'";
    $result = mysqli_query($connect, $query);

    echo json_encode(["exists" => mysqli_num_rows($result) > 0]);
}
?>
