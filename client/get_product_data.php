<?php
require_once '../php_action/db_connect.php';

// Check if product_id is set
if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
    $product_id = mysqli_real_escape_string($connect, $_GET['product_id']);

    // Fetch product stock quantity
    $query = "SELECT stock_quantity FROM products WHERE product_id = '$product_id'";
    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(["stock_quantity" => $row['stock_quantity']]);
    } else {
        echo json_encode(["error" => "Product not found."]);
    }
} else {
    echo json_encode(["error" => "Invalid product ID."]);
}

mysqli_close($connect);
?>
