<?php
require_once '../php_action/db_connect.php';

if (isset($_POST['delete_vendor'])) {
    $client_id = mysqli_real_escape_string($connect, $_POST['delete_vendor']);

    // Delete from vendor_stores
    mysqli_query($connect, "DELETE FROM vendor_stores WHERE client_id = '$client_id'");

    // Delete from vendor_delivery_services
    mysqli_query($connect, "DELETE FROM vendor_delivery_services WHERE client_id = '$client_id'");

    // Delete from users
    mysqli_query($connect, "DELETE FROM users WHERE client_id = '$client_id'");

    // Delete from clients
    mysqli_query($connect, "DELETE FROM clients WHERE client_id = '$client_id'");
    
    mysqli_query($connect, "DELETE FROM orders WHERE client_id = '$client_id'");
     
    mysqli_query($connect, "DELETE FROM products WHERE client_id = '$client_id'");
    
    mysqli_query($connect, "DELETE FROM order_items WHERE client_id = '$client_id'");
    

    echo "<script>showMessage('Vendor deleted successfully.', 'success');</script>";
    echo "<script>window.location = 'client.php';</script>";
}
?>