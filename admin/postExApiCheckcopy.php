<?php
// API Endpoint
$order_api_url = "https://api.postex.pk/services/integration/api/order/v1/get-all-order";

// API Token
$api_token = "OGQ3YjA0NDIzYTA3NGM5MzhiZjk5ZWE3NWE3ODY4MjA6NjNmYWFkM2Y0MTFiNGU2ZDlmZmIyYzcyZWQwNjNlOWU="; // Replace with your actual token

// Determine date range based on filter selection
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$endDate = date("Y-m-d");

switch ($filter) {
    case 'today':
        $startDate = $endDate;
        break;
    case 'week':
        $startDate = date("Y-m-d", strtotime("-7 days"));
        break;
    case '15days':
        $startDate = date("Y-m-d", strtotime("-15 days"));
        break;
    case 'month':
        $startDate = date("Y-m-d", strtotime("-1 month"));
        break;
    default:
        $startDate = "2024-02-04";
}

// Build Query String
$query_params = http_build_query([
    "orderStatusId" => 0,
    "startDate" => $startDate,
    "endDate" => $endDate
]);

$order_api_url .= "?" . $query_params;

// Initialize cURL session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $order_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $api_token",
    "token: $api_token",
    "Content-Type: application/json",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

// Execute API Request
$order_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Debugging Output
if ($http_code !== 200) {
    die("API Request Failed! HTTP Code: $http_code\nError: $error\nResponse: " . htmlentities($order_response));
}

// Decode JSON Response
$order_data = json_decode($order_response, true);

// Check if data exists
if (!$order_data || !isset($order_data['dist']) || empty($order_data['dist'])) {
    die("No orders found in API response.");
}

$orders = $order_data['dist']; // List of orders

// Count Transaction Status
$status_counts = [];
foreach ($orders as $order) {
    $status = $order['transactionStatus'] ?? 'Unknown';
    $status_counts[$status] = ($status_counts[$status] ?? 0) + 1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PostEx Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">PostEx Orders (<?= $startDate ?> to <?= $endDate ?>)</h2>

        <form method="GET" class="mb-3">
            <label for="filter">Select Date Range:</label>
            <select name="filter" id="filter" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
                <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>All</option>
                <option value="today" <?= $filter == 'today' ? 'selected' : '' ?>>Today</option>
                <option value="week" <?= $filter == 'week' ? 'selected' : '' ?>>One Week</option>
                <option value="15days" <?= $filter == '15days' ? 'selected' : '' ?>>15 Days</option>
                <option value="month" <?= $filter == 'month' ? 'selected' : '' ?>>1 Month</option>
            </select>
        </form>

        <h4>Transaction Status Summary:</h4>
        <ul class="list-group mb-4">
            <?php foreach ($status_counts as $status => $count) { ?>
                <li class="list-group-item"> <?= htmlspecialchars($status) ?>: <strong><?= $count ?></strong></li>
            <?php } ?>
        </ul>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Order Ref</th>
                    <th>Tracking Number</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Delivery Address</th>
                    <th>City</th>
                    <th>Invoice Payment</th>
                    <th>Transaction Date</th>
                    <th>Transaction Status</th>
                    <th>Booking Weight</th>
                    <th>Actual Weight</th>
                    <th>Items</th>
                    <th>Pickup Address</th>
                    <th>Return Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) { ?>
                    <tr>
                        <td><?= htmlspecialchars($order['orderRefNumber'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order['trackingNumber'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order['customerName'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order['customerPhone'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order['deliveryAddress'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order['cityName'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order['invoicePayment'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order['transactionDate'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order['transactionStatus'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order['bookingWeight'] ?? 'N/A') ?> kg</td>
                        <td><?= htmlspecialchars($order['actualWeight'] ?? 'N/A') ?> kg</td>
                        <td><?= htmlspecialchars($order['items'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order['pickupAddress'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order['returnAddress'] ?? 'N/A') ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>