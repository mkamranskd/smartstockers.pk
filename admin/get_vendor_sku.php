<?php
require_once '../php_action/db_connect.php';

if (isset($_GET['vendor_id'])) {
    $vendor_id = mysqli_real_escape_string($connect, $_GET['vendor_id']);

    // Get Vendor SKU
    $vendor_query = mysqli_query($connect, "SELECT sku FROM vendors WHERE id = '$vendor_id'");
    $vendor_data = mysqli_fetch_assoc($vendor_query);
    
    if (!$vendor_data) {
        echo json_encode(["sku" => "Error", "product_id" => "Error"]);
        exit;
    }

    $vendor_sku = $vendor_data['sku'];

    // Generate Product ID (Find latest product for this vendor)
    $product_query = mysqli_query($connect, "SELECT MAX(product_id) AS last_product_id FROM products WHERE product_id LIKE '$vendor_sku%'");
    $product_data = mysqli_fetch_assoc($product_query);

    if ($product_data['last_product_id']) {
        // Extract number part & increment it
        preg_match('/(\d+)$/', $product_data['last_product_id'], $matches);
        $new_number = str_pad($matches[1] + 1, 5, '0', STR_PAD_LEFT);
    } else {
        $new_number = "00001"; // Start from 00001 if no product exists
    }

    $new_product_id = $vendor_sku . $new_number;

    echo json_encode(["sku" => $vendor_sku, "product_id" => $new_product_id]);
}
?>
