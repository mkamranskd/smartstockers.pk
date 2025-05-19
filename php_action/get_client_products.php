<?php
require_once 'db_connect.php';

if (isset($_GET['client'])) {
    $client_id = mysqli_real_escape_string($connect, $_GET['client']);
    var_dump($client_id); // Debugging output

    $query = "SELECT product_id, product_name FROM products WHERE client_id = '$client_id'";
    $result = mysqli_query($connect, $query);

    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    echo json_encode($products);
}
?>
