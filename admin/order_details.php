<?php
require_once '../php_action/db_connect.php';

// Ensure orderId is retrieved from GET request first
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_postex_order'])) {
    $orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

    if ($orderId <= 0) {
        echo "<p class='text-danger text-center'>Invalid order ID.</p>";
        exit;
    }

    // Fetch order details
    $orderQuery = "SELECT orders.*, products.product_name 
    FROM orders 
    LEFT JOIN products ON orders.product_id = products.product_id 
    WHERE orders.id = '$orderId'";

    $orderResult = mysqli_query($connect, $orderQuery);
    $order = mysqli_fetch_assoc($orderResult);

    if (!$order) {
        die("<p class='text-danger text-center'>Invalid order ID or product not found.</p>");
    }

    if ($order) {
        $clientId = $order['client_id'];

        // Fetch POSTex API token for the client's delivery service
        $apiQuery = "SELECT api_token FROM vendor_delivery_services WHERE client_id = '$clientId' AND delivery_type = 'POSTEX' LIMIT 1";
        $apiResult = mysqli_query($connect, $apiQuery);
        $apiData = mysqli_fetch_assoc($apiResult);

        if (!$apiData || empty($apiData['api_token'])) {
            die("<p class='text-danger text-center'>POSTex API credentials not found for this client.</p>");
        }

        $apiToken = $apiData['api_token'];

        if ($apiData) { // Prepare API request payload
            $postData = [
                'orderRefNumber' => (string) $order['id'], // Order reference number
                'invoicePayment' => $order['total_amount'], // Fix: Use actual order total
                'orderDetail' => $order['product_name'] ?? '', // Fix: Prevent undefined error
                'customerName' => $order['customer_name'],
                'customerPhone' => $order['customer_phone'],
                'customerEmail' => $order['customer_email'], // Add email (make sure field exists in your table)
                'deliveryAddress' => $order['delivery_address'], // Ensure this field is available
                'transactionNotes' => '', // Optional notes about the transaction
                'cityName' => $order['city'],
                'invoiceDivision' => 0, // Assuming invoice division is required
                'items' => $order['number_of_items'],
                'pickupAddressCode' => '001', // Optional: Pickup address code if available
                
                'orderType' => 'Normal', // Order type, ensure correct value
                'deliveryPostalCode' => '67210', // Add postal code for delivery
                'orderWeight' => '1', // Add weight of the items being shipped
                'deliveryInstructions' => 'abc', // Optional: Any special delivery instructions
                'insuranceRequired' => '1' ? true : false, // Optional: Insurance requirement
                'codAmount' => $order['cod_amount'] ?? 0, // Optional: Cash on delivery amount if applicable
                'customerCountry' => 'PK', // Ensure you have country field
            ];

            $ch = curl_init('https://api.postex.pk/services/integration/api/order/v3/create-order');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "token: $apiToken", // Corrected token header
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Handle response
            $responseData = json_decode($response, true);
            if ($httpCode == 200 && isset($responseData['dist']['trackingNumber'])) {
                $trackingNumber = htmlspecialchars($responseData['dist']['trackingNumber']);
                echo "<p class='text-success text-center'>Order successfully created on POSTex. Tracking Number: $trackingNumber</p>";
            } else {
                echo "<p class='text-danger text-center'>Failed to create order on POSTex. Response: $response</p>";
            }

        } else {
            echo "<p class='text-danger text-center'>POSTex API credentials not found for this client.</p>";
        }
    } else {
        echo "<p class='text-danger text-center'>Invalid order ID.</p>";
    }
}
?>

<!-- Ensure orderId is set before using it -->
<form method="post">
    <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderId) ?>">
    <button type="submit" name="create_postex_order" class="btn btn-primary">
        <i class="fa fa-truck"></i> Create POSTex Order
    </button>
</form>
