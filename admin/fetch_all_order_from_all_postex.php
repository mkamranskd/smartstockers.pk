<?php
require_once '../php_action/db_connect.php';
require_once '../php_action/functions.php';

$orderQuery = "
    SELECT o.order_id, o.client_id, o.order_tracking_id
    FROM orders o
    WHERE o.order_tracking_id LIKE 'PostEx-%' AND (status = 2 OR status = 3 OR status = 5 OR status = 7)
";
$orderResult = mysqli_query($connect, $orderQuery);

if (!$orderResult) {
    die("Query failed: " . mysqli_error($connect));
}

$results = [];

while ($order = mysqli_fetch_assoc($orderResult)) {
    $orderId = $order['order_id'];
    $clientId = $order['client_id'];
    $trackingIdFull = $order['order_tracking_id'];
    $trackingParts = explode('-', $trackingIdFull);
    $trackingId = end($trackingParts);

    $tokenQuery = "
        SELECT api_token 
        FROM vendor_delivery_services 
        WHERE client_id = '$clientId' AND delivery_type = 'PostEx' 
        LIMIT 1
    ";
    $tokenResult = mysqli_query($connect, $tokenQuery);

    if (!$tokenResult || mysqli_num_rows($tokenResult) == 0) {
        continue;
    }

    $token = mysqli_fetch_assoc($tokenResult)['api_token'];

    $url = "https://api.postex.pk/services/integration/api/order/v1/track-order/" . urlencode($trackingId);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["token: $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || $statusCode !== 200) {
        continue;
    }

    $data = json_decode($response, true);
    $history = $data['dist']['transactionStatusHistory'] ?? [];

    if (empty($history)) {
        continue;
    }

    // Sort history by updatedAt to get the latest status
    usort($history, function ($a, $b) {
        return strtotime($a['updatedAt']) <=> strtotime($b['updatedAt']);
    });
    $latestStatus = end($history);

    $code = $latestStatus['transactionStatusMessageCode'];
    $updateTime = $latestStatus['updatedAt'] ?? '';
    $formattedDate = $updateTime ? (new DateTime($updateTime))->format('H:i, d-M-Y') : '';

    $responseStatus = 'Unknown';
    $newStatus = null;
    $responseMessage = '';

    switch ($code) {
        case '0005':
            $newStatus = 4;
            $responseStatus = 'Delivered';
            $responseMessage = 'On ' . $formattedDate . ' Delivered to Customer';
            addNotification(0, $orderId .' '. $responseMessage);
            break;
        case '0001':
        case '0036':
            $newStatus = 2;
            $responseStatus = 'Ready to Ship';
            $responseMessage = 'On ' . $formattedDate . ' At Merchant’s Warehouse';
            addNotification(0, $orderId .' '. $responseMessage);
            break;
        case '0003':
            $newStatus = 2;
            $responseStatus = 'Ready to Ship';
            $responseMessage = 'On ' . $formattedDate . ' At PostEx Warehouse';
            addNotification(0, $orderId .' '. $responseMessage);
            break;
        case '0004':
        case '0031':
        case '0003':
        case '0033':
        case '0035':
        case '0038':
        case '0004':
        case '0008':
            $newStatus = 3;
            $responseStatus = 'Shipped';
            $responseMessage = 'On ' . $formattedDate . ' Package on Route';
            break;
        case '0013':
        case '0040':
        case '0005':
            $newStatus = 5;
            $responseStatus = 'Returned';
            $responseMessage = 'On ' . $formattedDate . ' Returned to Sender';
            break;
        case '0013':
            $newStatus = 5;
            $responseStatus = 'Under Review';
            $responseMessage = 'On ' . $formattedDate . ' Delivery Under Review';
            break;
        case '0008':
            $newStatus = 5;
            $responseStatus = 'Failed';
            $responseMessage = 'On ' . $formattedDate . ' Attempt Made';
            break;
        case '0002':
            $newStatus = 5;
            $responseStatus = 'Failed';
            $responseMessage = 'On ' . $formattedDate . ' Booking Cancelled by Merchant';
            break;
        default:
            $newStatus = 5;
            $responseStatus = "Unknown Status ($code)";
            $responseMessage = 'On ' . $formattedDate . ' Waiting for Further Update';
            break;
    }

    if ($newStatus !== null) {
        $updateQuery = "
            UPDATE orders 
            SET status = $newStatus, response_status = '$responseMessage' 
            WHERE order_id = $orderId
        ";
        mysqli_query($connect, $updateQuery);
    }

    $results[] = [
        'order_id' => $orderId,
        'tracking_id' => $trackingIdFull,
        'postex_status_code' => $code,
        'response_status' => $responseStatus,
        'db_status' => $newStatus,
        'response_message' => $responseMessage
    ];
}

$connect->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>PostEx Order Status Update Summary</title>
    <style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
    }

    th,
    td {
        border: 1px solid #aaa;
        padding: 8px;
        text-align: center;
    }

    th {
        background-color: #eee;
    }
    </style>
</head>

<body>
    <h2>Order Status Update Summary</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Tracking ID</th>
            <th>PostEx Status Code</th>
            <th>Response Status</th>
            <th>Internal DB Status</th>
            <th>Response Message</th>
        </tr>
        <?php foreach ($results as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['order_id']) ?></td>
            <td><?= htmlspecialchars($row['tracking_id']) ?></td>
            <td><?= htmlspecialchars($row['postex_status_code']) ?></td>
            <td><?= htmlspecialchars($row['response_status']) ?></td>
            <td><?= htmlspecialchars($row['db_status']) ?></td>
            <td><?= htmlspecialchars($row['response_message']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>