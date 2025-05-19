<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../php_action/db_connect.php';

// Get client_id from the query string
$clientId = isset($_GET['client_id']) ? $_GET['client_id'] : null;

if ($clientId) {
    // Query to fetch products based on client_id
    $query = "SELECT *  FROM products WHERE client_id = '$clientId'";
    $result = mysqli_query($connect, $query);

    if (!$result) {
        echo json_encode(['error' => 'Failed to fetch products.']);
        exit;
    }

    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    // Return the product data as JSON
    echo json_encode($products);
} else {
    echo json_encode([]);
}
?>
