<?php
session_start();
require_once '../php_action/db_connect.php';
include '../php_action/getOrderStatus.php';

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['userId'];

// Fetch client_id from users table
$sql = "SELECT client_id FROM users WHERE user_id = {$user_id}";
$result = $connect->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $client_id = mysqli_real_escape_string($connect, $row['client_id']);
} else {
    die("Client ID not found.");
}

// Sanitize filters
$status = isset($_GET['status']) ? intval($_GET['status']) : 1;

$startDate = isset($_GET['start_date']) ? mysqli_real_escape_string($connect, $_GET['start_date']) : null;
$endDate = isset($_GET['end_date']) ? mysqli_real_escape_string($connect, $_GET['end_date']) : null;

$clientIdFilter = isset($_GET['client_id']) ? mysqli_real_escape_string($connect, $_GET['client_id']) : null;

// Build filter conditions
$dateCondition = "";
if ($startDate && $endDate) {
    $dateCondition = "AND orders.order_date BETWEEN '$startDate' AND '$endDate'";
}

$clientCondition = "";
if ($clientIdFilter) {
    $clientCondition = "AND orders.client_id = '$clientIdFilter'";
}

// Query depending on status
if ($status === 10) {
    $query = "SELECT 
        orders.*, 
        GROUP_CONCAT(IFNULL(products.product_name, 'Unknown Product') SEPARATOR ', ') AS product_names, 
        GROUP_CONCAT(IFNULL(products.product_store_id, 'N/A') SEPARATOR ', ') AS product_store_ids,
        GROUP_CONCAT(IFNULL(order_items.quantity, 0) SEPARATOR ', ') AS quantities,
        GROUP_CONCAT(IFNULL(order_items.price, 0) SEPARATOR ', ') AS prices,
        GROUP_CONCAT(IFNULL(order_items.subtotal, 0) SEPARATOR ', ') AS subtotals,
        GROUP_CONCAT(IFNULL(products.weight, 0) SEPARATOR ', ') AS weights,
        GROUP_CONCAT(IFNULL(products.weight, 0) SEPARATOR ', ') AS weights,
        IFNULL(vds.delivery_type, 'N/A') AS delivery_type 
    FROM orders 
    LEFT JOIN order_items ON orders.order_id = order_items.order_id 
    LEFT JOIN products ON order_items.product_id = products.product_id 
    LEFT JOIN (
        SELECT client_id, MIN(delivery_type) AS delivery_type
        FROM vendor_delivery_services
        GROUP BY client_id
    ) AS vds ON orders.client_id = vds.client_id 
    WHERE orders.status = orders.status AND orders.client_id = '$client_id' $dateCondition $clientCondition
    GROUP BY orders.order_id
    ORDER BY orders.order_date DESC;";
} else {
    $query = "SELECT 
        orders.*, 
        GROUP_CONCAT(IFNULL(products.product_name, 'Unknown Product') SEPARATOR ', ') AS product_names, 
        GROUP_CONCAT(IFNULL(products.product_store_id, 'N/A') SEPARATOR ', ') AS product_store_ids,
        GROUP_CONCAT(IFNULL(order_items.quantity, 0) SEPARATOR ', ') AS quantities,
        GROUP_CONCAT(IFNULL(order_items.price, 0) SEPARATOR ', ') AS prices,
        GROUP_CONCAT(IFNULL(order_items.subtotal, 0) SEPARATOR ', ') AS subtotals,
        GROUP_CONCAT(IFNULL(products.weight, 0) SEPARATOR ', ') AS weights,
        IFNULL(vds.delivery_type, 'N/A') AS delivery_type 
    FROM orders 
    LEFT JOIN order_items ON orders.order_id = order_items.order_id 
    LEFT JOIN products ON order_items.product_id = products.product_id 
    LEFT JOIN (
        SELECT client_id, MIN(delivery_type) AS delivery_type
        FROM vendor_delivery_services
        GROUP BY client_id
    ) AS vds ON orders.client_id = vds.client_id 
    WHERE orders.status = $status AND orders.client_id = '$client_id' $dateCondition $clientCondition
    GROUP BY orders.order_id
    ORDER BY orders.order_date DESC;";
}

// Execute the query
$result = $connect->query($query);

if (!$result) {
    die("Query failed: " . $connect->error);
}


if (mysqli_num_rows($result) > 0): ?>



<div class="table-responsive">
    <table class="table table-hover" id="myTable">
        <thead>
            <tr>
                <th style="white-space: nowrap;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="customCheck" style="display:none;">
                    </div>
                </th>
                <th onclick="sortTable(1, this)" style="white-space: nowrap; cursor: pointer;">
                    Print <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(2, this)" style="white-space: nowrap; cursor: pointer;">
                    Actions <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(3, this)" style="white-space: nowrap; cursor: pointer;">
                    Client ID <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(4, this)" style="white-space: nowrap; cursor: pointer;">
                    Order ID <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(5, this)" style="white-space: nowrap; cursor: pointer;">
                    Order Date <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(6, this)" style="white-space: nowrap; cursor: pointer;">
                    Customer Name <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(7, this)" style="white-space: nowrap; cursor: pointer;">
                    Phone <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(8, this)" style="white-space: nowrap; cursor: pointer;">
                    Address <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(9, this)" style="white-space: nowrap; cursor: pointer;">
                    City <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(10, this)" style="white-space: nowrap; cursor: pointer;">
                    Product <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(11, this)" style="white-space: nowrap; cursor: pointer;">
                    COD <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(12, this)" style="white-space: nowrap; cursor: pointer;">
                    Quantity <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(13, this)" style="white-space: nowrap; display:none; cursor: pointer;">
                    Total Price <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(14, this)" style="white-space: nowrap; cursor: pointer;">
                    Total Weight (kg) <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(15, this)" style="white-space: nowrap; cursor: pointer;">
                    Status <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(16, this)" style="white-space: nowrap; cursor: pointer;">
                    Delivery Service <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(17, this)" style="white-space: nowrap; cursor: pointer;">
                    OrderTrackingId <i class="fa fa-sort"></i>
                </th>
                <th onclick="sortTable(18, this)" style="white-space: nowrap; cursor: pointer;">
                    Updated At<i class="fa fa-sort"></i>
                </th>
            </tr>
        </thead>

        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): 
        $product_names = explode(', ', $row['product_names']);
        $product_store_ids = explode(', ', $row['product_store_ids']);
        $quantities = explode(', ', $row['quantities']);
        $prices = explode(', ', $row['prices']);
        $subtotals = explode(', ', $row['subtotals']);
        $weights = explode(', ', $row['weights']);
    ?>
            <tr id="order-<?= $row['id'] ?>" data-quantity="<?= htmlspecialchars($row['number_of_items']) ?>">
                <td>
                    <input class="form-check-input" type="checkbox" id="customCheck" style="display:none;">
                </td>
                <td>
                    <button type="button" onclick="generateAWB(<?= $row['id'] ?>)"
                        class="btn btn-outline-primary btn-sm custom-hover">
                        <i class="fa fa-print text-primary fw-bold"></i>
                    </button>

                    <style>
                    .custom-hover:hover {
                        background-color: transparent;
                        /* Primary color on hover */
                        color: white !important;
                        /* Text color change */
                    }

                    .custom-hover:hover i {
                        color: black !important;
                        /* Icon turns white on hover */
                    }
                    </style>

                </td>
                <td style="white-space: nowrap;">
                    <?php if ($row['status'] == 0): ?>

                    <div class="btn-group" role="group">
                        <button class="btn btn-primary btn-sm" onclick="updateOrderStatus(<?= $row['order_id'] ?>, 1)">
                            <i class="fa fa-check" aria-hidden="true"></i> &nbsp;&nbsp;Mark as Confirmed
                        </button>

                        <button type="button" class="btn btn-danger btn-sm d-flex align-items-center"
                            onclick="updateOrderStatus(<?= $row['order_id'] ?>, 6)">
                            <i class="fa fa-times" aria-hidden="true"></i>&nbsp;&nbsp;Cancel
                            <div id="spinn" class="loader ms-2" style="display:none;"></div>
                        </button>
                    </div>












                    <?php elseif ($row['status'] == 1 && !empty($row['delivery_type'])): ?>

                    <span class="badge bg-primary">Confirmed</span>













                    <?php elseif ($row['status'] == 2): ?>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">Ready to Shipped</span>

                    </div>

                    <?php elseif ($row['status'] == 3): ?>
                    <span class="badge bg-primary">Shipped</span>

                    <?php elseif ($row['status'] == 4): ?>
                    <span class="badge bg-success">Delivered</span>

                    <?php elseif ($row['status'] == 5): ?>
                    <span class="badge bg-warning">Pending</span>

                    <?php elseif ($row['status'] == 6): ?>
                    <span class="badge bg-danger">Cancelled</span>

                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['client_id']) ?></td>
                <td><?= htmlspecialchars($row['order_id']) ?></td>
                <td><?= htmlspecialchars($row['order_date']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                <td><?= htmlspecialchars($row['shipping_address']) ?></td>
                <td><?= htmlspecialchars($row['city']) ?></td>
                <td>
                    <?php foreach ($product_names as $index => $product): ?>
                    <ul class="list-group" type="disc">
                        <li class=" bg-transparent" style="white-space: nowrap;">
                            <?= htmlspecialchars($quantities[$index]) ?> pcs <?= htmlspecialchars($product) ?></li>
                    </ul>
                    <?php endforeach; ?>

                </td>
                <td><?= htmlspecialchars($row['total_amount']) ?></td>
                <td><?= array_sum($quantities) ?></td>
                <td style="white-space: nowrap; display:none;"><?= array_sum($subtotals) ?></td>
                <td><?= array_sum($weights) ?></td>
                <td><?= htmlspecialchars(getOrderStatus($row['status'])) ?></td>
                <td><?= htmlspecialchars($row['delivery_service']) ?></td>
                <td><?= htmlspecialchars($row['order_tracking_id']) ?></td>
                <td><?= htmlspecialchars($row['updated_at']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>


    </table>
</div>




<?php else: ?>
<p class="text-center text-danger">No orders found.</p>
<?php endif; ?>