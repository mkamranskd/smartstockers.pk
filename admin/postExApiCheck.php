<?php
require_once '../php_action/db_connect.php';

// Get the parameter from the URL
$Client = isset($_GET['Client']) ? $_GET['Client'] : '';

// Fetch products based on SKU parameter
$products = [];
if (!empty($Client)) {
    $stmt = $connect->prepare("SELECT product_id, product_name FROM products WHERE sku = ?");
    $stmt->bind_param("s", $Client);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
</head>
<body>
    <?php if (!empty($products)): ?>
        <h2>Product List for SKU: <?= htmlspecialchars($Client) ?></h2>
        <ul>
            <?php foreach ($products as $product): ?>
                <li><strong>ID:</strong> <?= htmlspecialchars($product['product_id']) ?> - <strong>Name:</strong> <?= htmlspecialchars($product['product_name']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No products found for SKU: <?= htmlspecialchars($Client) ?></p>
    <?php endif; ?>
</body>
</html>
