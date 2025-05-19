<?php
require_once '../php_action/db_connect.php';

// Log incoming POST request for debugging
file_put_contents("debug_log.txt", "Received Data:\n" . print_r($_POST, true) . "\n", FILE_APPEND);

// Check if all required fields are present and not empty
if (!empty($_POST['client_id']) && !empty($_POST['product_id']) && !empty($_POST['product_store_id']) && !empty($_POST['product_name']) && !empty($_POST['price']) && isset($_POST['weight'])) {

    $client_id = trim($_POST['client_id']);
    $product_id = trim($_POST['product_id']);
    $product_store_id = trim($_POST['product_store_id']);
    $product_name = trim($_POST['product_name']);
    $price = trim($_POST['price']);
    $weight = trim($_POST['weight']);

    // Validate numeric values
    if (!is_numeric($price) || !is_numeric($weight)) {
        echo "Error: Price and weight must be numeric values.";
        exit;
    }

    // Use prepared statements for security
    $query = "INSERT INTO products (client_id, product_id, product_store_id, product_name, price, weight) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssdd", $client_id, $product_id, $product_store_id, $product_name, $price, $weight);
        if (mysqli_stmt_execute($stmt)) {
            echo "Product added successfully!";
        } else {
            echo "Error inserting product: " . mysqli_error($connect);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($connect);
    }
} else {
    echo "Invalid request. Missing fields.";
}
?>
