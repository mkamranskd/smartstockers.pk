<?php
require_once '../php_action/db_connect.php';
include '../php_action/getOrderStatus.php';

$status = isset($_GET['status']) ? intval($_GET['status']) : 1;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

$dateCondition = "";
if ($startDate && $endDate) {
    $dateCondition = "AND orders.order_date BETWEEN '$startDate' AND '$endDate'";
}
$clientIdFilter = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;

$clientCondition = "";
if ($clientIdFilter) {
    $clientCondition = "AND orders.client_id = $clientIdFilter";
}


if($status === 10){
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
WHERE orders.status = orders.status $dateCondition $clientCondition
GROUP BY orders.order_id
ORDER BY orders.order_date DESC;
";
}
else{
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
WHERE orders.status = $status $dateCondition $clientCondition

GROUP BY orders.order_id
ORDER BY orders.order_date DESC;
";
}

$result = mysqli_query($connect, $query);

if (mysqli_num_rows($result) > 0): ?>


<div class="table-responsive" style="height: 480px; overflow-y: auto; overflow-x: auto;">
    <div>
        <table class="table mb-0 table table-hover" id="myTable">

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
                    <th onclick="sortTable(16, this)" style="display:none; white-space: nowrap; cursor: pointer;">
                        Delivery Service <i class="fa fa-sort"></i>
                    </th>
                    <th onclick="sortTable(17, this)" style="white-space: nowrap; cursor: pointer;">
                        Order Tracking Id <i class="fa fa-sort"></i>
                    </th>
                    <th onclick="sortTable(18, this)" style="white-space: nowrap; cursor: pointer;">
                        Order Response <i class="fa fa-sort"></i>
                    </th>
                    <th onclick="sortTable(19, this)" style="white-space: nowrap; cursor: pointer;">
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
                <tr id="order-<?= $row['order_id']?>" data-quantity="<?= htmlspecialchars($row['number_of_items']) ?>">
                    <td>
                        <input class="form-check-input" type="checkbox" id="customCheck" style="display:none;">
                    </td>
                    <td>
                        <button type="button" onclick="generateAWB(<?= $row['order_id']?>)"
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
                        <?php
                    $sources = ['0' => 'Store', '1' => 'Admin', '2' => 'Client'];
                    $labels = ['Store' => 'primary', 'Admin' => 'secondary', 'Client' => 'info'];
                    $src = $sources[$row['source']] ?? null;
                   ?>

                        <?php if ($row['status'] == 0): ?>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle w-100" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="badge ">New Order </span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="d-flex justify-content-center">
                                        <?= isset($labels[$src]) ? '<div class="d-flex justify-content-center"><span class="w-100 badge bg-' . $labels[$src] . '"> Order From ' . $src . '</span></div>' : '' ?>


                                    </div>

                                </li>
                                <hr>
                                <li>
                                    <button class="dropdown-item text-primary"
                                        onclick="updateOrderStatus(<?= $row['order_id'] ?>, 1)">
                                        <i class="fa fa-check text-primary"
                                            aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Mark as
                                        Confirmed
                                    </button>
                                </li>
                            </ul>
                        </div>


                        <?php elseif ($row['status'] == 1 && !empty($row['delivery_type'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <span class="badge ">Confirmed</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="d-flex justify-content-center">
                                        <?= isset($labels[$src]) ? '<div class="d-flex justify-content-center"><span class="w-100 badge bg-' . $labels[$src] . '"> Order From ' . $src . '</span></div>' : '' ?>

                                    </div>

                                </li>
                                <hr>
                                <li>
                                    <button class="dropdown-item"
                                        onclick="showDeliveryChargesModal(<?= $row['order_id']?>, <?= $row['total_amount'] ?>)">
                                        <i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Delivery Charges
                                    </button>
                                </li>
                                <?php
                             // Sanitize client_id to ensure it's safe to use in SQL
                             $clientId = mysqli_real_escape_string($connect, $row['client_id']); // Escape string for security
                                        
                             // Query to fetch delivery types for the specific client_id from vendor_delivery_services table
                             $deliveryQuery = "SELECT DISTINCT delivery_type FROM vendor_delivery_services WHERE client_id = '{$clientId}'";
                             $deliveryResult = mysqli_query($connect, $deliveryQuery);
                                        
                             // Check if the query executed properly
                             if ($deliveryResult && mysqli_num_rows($deliveryResult) > 0) {
                                 // Loop through each delivery type
                                 while ($delivery = mysqli_fetch_assoc($deliveryResult)):
                                     $deliveryType = htmlspecialchars($delivery['delivery_type']);
                             ?>

                                <?php if ($deliveryType == 'Leopards'): ?>
                                <!-- Leopards Button -->

                                <li>
                                    <button class="dropdown-item"
                                        onclick="LeopardsConfirmOrderModal(<?= $row['order_id']?>, '<?= $deliveryType ?>');">
                                        <i class="fa fa-check" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Order On
                                        Leopards
                                    </button>
                                </li>
                                <?php elseif ($deliveryType == 'PostEx'): ?>
                                <!-- PostEx Button -->
                                <li>
                                    <button class="dropdown-item"
                                        onclick="PostExConfirmOrderModal(<?= $row['order_id']?>, '<?= $deliveryType ?>');">
                                        <i class="fa fa-check" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Order On PostEx
                                    </button>
                                </li>
                                <?php else: ?>
                                <!-- Generic Delivery Button for other delivery types -->
                                <li>
                                    <button class="dropdown-item"
                                        onclick="genericConfirmOrderModal(<?= $row['id'] ?>, '<?= $deliveryType ?>');">
                                        <?= $deliveryType ?> Delivery
                                    </button>
                                </li>
                                <?php endif; ?>
                                <?php endwhile; ?>
                                <?php } else { ?>
                                <li>No delivery types available</li>
                                <?php } ?>

                                <!-- Common cancel button -->
                                <li>
                                    <button class="dropdown-item"
                                        onclick="showCustomOrderDeliveryModal(<?= $row['order_id'] ?>);">
                                        <i class="fa fa-check" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp; Custom Order
                                        Delivery
                                    </button>


                                </li> <!-- Common cancel button -->
                                <li>
                                    <button class="text-danger btn dropdown-item"
                                        onclick="updateOrderStatus(<?= $row['order_id'] ?>, 6)">
                                        <i class="fa fa-times" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp; Cancel Order
                                    </button>
                                </li>
                                <hr>
                                <li>
                                    <button class="text-danger btn dropdown-item"
                                        onclick="deleteOrder(<?= $row['order_id'] ?>)">
                                        <i class="fa fa-trash text-danger" aria-hidden="true"></i>
                                        &nbsp;&nbsp;&nbsp;Delete Permanently
                                    </button>
                                </li>
                            </ul>
                        </div>


                        <?php elseif ($row['status'] == 2): ?>

                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <span class="badge">Ready to Shipped</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="d-flex justify-content-center">
                                        <?= isset($labels[$src]) ? '<div class="d-flex justify-content-center"><span class="w-100 badge bg-' . $labels[$src] . '"> Order From ' . $src . '</span></div>' : '' ?>

                                    </div>

                                </li>
                                <hr>
                                <li>

                                    <button class="btn dropdown-item"
                                        onclick="addToPrintListFromRow(<?= $row['order_id'] ?>)">
                                        <i class="fa fa-plus"></i> &nbsp;&nbsp;&nbsp;Add to Print List
                                    </button>



                                </li>



                                <hr>
                                <li>
                                    <button class="text-danger btn dropdown-item"
                                        onclick="deleteOrder(<?= $row['order_id'] ?>)">
                                        <i class="fa fa-trash text-danger" aria-hidden="true"></i>
                                        &nbsp;&nbsp;&nbsp;Delete Permanently
                                    </button>
                                </li>
                            </ul>
                        </div>




                        <?php elseif ($row['status'] == 3): ?>

                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <span class="badge">Shipped</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="d-flex justify-content-center">
                                        <?= isset($labels[$src]) ? '<div class="d-flex justify-content-center"><span class="w-100 badge bg-' . $labels[$src] . '"> Order From ' . $src . '</span></div>' : '' ?>

                                    </div>

                                </li>
                                <hr>
                                <li>
                                    <button class="btn dropdown-item"
                                        onclick="window.open('https://www.postex.pk/tracking?trackingNumber=<?= urlencode(explode('-', $row['order_tracking_id'])[1]) ?>', '_blank')">
                                        <i class="fa fa-truck"></i> &nbsp;&nbsp;&nbsp;Track <span
                                            style="cursor: pointer; color: blue;" onclick="
                            navigator.clipboard.writeText('<?= urlencode(explode('-', $row['order_tracking_id'])[1]) ?>');
                            showMessage('Tracking ID Copied','primary');
                                            ">
                                            <?= htmlspecialchars($row['order_tracking_id']) ?>
                                        </span>

                                    </button>
                                </li>




                            </ul>
                        </div>

                        <?php elseif ($row['status'] == 4): ?>

                        <div class=" dropdown">
                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <span class="badge">Delivered</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="d-flex justify-content-center">
                                        <?= isset($labels[$src]) ? '<div class="d-flex justify-content-center"><span class="w-100 badge bg-' . $labels[$src] . '"> Order From ' . $src . '</span></div>' : '' ?>

                                    </div>

                                </li>
                                <hr>
                                <li>
                                    <button class="btn dropdown-item"
                                        onclick="window.open('https://www.postex.pk/tracking?trackingNumber=<?= urlencode(explode('-', $row['order_tracking_id'])[1]) ?>', '_blank')">
                                        <i class="fa fa-truck"></i> &nbsp;&nbsp;&nbsp;Track <span
                                            style="cursor: pointer; color: blue;" onclick="
                            navigator.clipboard.writeText('<?= urlencode(explode('-', $row['order_tracking_id'])[1]) ?>');
                            showMessage('Tracking ID Copied','primary');
                                            ">
                                            <?= htmlspecialchars($row['order_tracking_id']) ?>
                                        </span>

                                    </button>
                                </li>




                            </ul>
                        </div>

                        <?php elseif ($row['status'] == 5): ?>
                        <div class=" dropdown">
                            <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <span class="badge">Pending</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="d-flex justify-content-center">
                                        <?= isset($labels[$src]) ? '<div class="d-flex justify-content-center"><span class="w-100 badge bg-' . $labels[$src] . '"> Order From ' . $src . '</span></div>' : '' ?>

                                    </div>

                                </li>
                                <hr>
                                <li>
                                    <button class="btn dropdown-item"
                                        onclick="window.open('https://www.postex.pk/tracking?trackingNumber=<?= urlencode(explode('-', $row['order_tracking_id'])[1]) ?>', '_blank')">
                                        <i class="fa fa-truck"></i> &nbsp;&nbsp;&nbsp;Track <span
                                            style="cursor: pointer; color: blue;" onclick="
                            navigator.clipboard.writeText('<?= urlencode(explode('-', $row['order_tracking_id'])[1]) ?>');
                            showMessage('Tracking ID Copied','primary');
                                            ">
                                            <?= htmlspecialchars($row['order_tracking_id']) ?>
                                        </span>

                                    </button>
                                </li>




                            </ul>
                        </div>
                        <?php elseif ($row['status'] == 6): ?>
                        <span class="badge bg-danger">Cancelled</span>

                        <?php elseif ($row['status'] == 7): ?>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle w-100" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="badge ">Ready To Shipped</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <div class="d-flex justify-content-center">
                                        <?= isset($labels[$src]) ? '<div class="d-flex justify-content-center"><span class="w-100 badge bg-' . $labels[$src] . '"> Order From ' . $src . '</span></div>' : '' ?>


                                    </div>

                                </li>
                                <hr>
                                <li>
                                    <button class="dropdown-item text-success "
                                        onclick="updateOrderStatus(<?= $row['order_id'] ?>, 4)">
                                        <i class="fa fa-check " aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Mark as
                                        Delivered
                                    </button>
                                </li>


                                <li>
                                    <button class="text-danger btn dropdown-item"
                                        onclick="updateOrderStatus(<?= $row['order_id'] ?>, 6)">
                                        <i class="fa fa-times" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp; Cancel Order
                                    </button>
                                </li>
                                <hr>
                                <li>
                                    <button class="text-danger btn dropdown-item"
                                        onclick="deleteOrder(<?= $row['order_id'] ?>)">
                                        <i class="fa fa-trash text-danger" aria-hidden="true"></i>
                                        &nbsp;&nbsp;&nbsp;Delete Permanently
                                    </button>
                                </li>
                            </ul>
                        </div>

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

                    <td class="cod">
                        <?= htmlspecialchars($row['total_amount']) ?>
                    </td>




                    <td class="qty"><?= array_sum($quantities) ?></td>
                    <td style="white-space: nowrap; display:none;"><?= array_sum($subtotals) ?></td>
                    <td><?= array_sum($weights) ?></td>
                    <td><?= htmlspecialchars(getOrderStatus($row['status'])) ?></td>
                    <td style="display:none;"><?= htmlspecialchars($row['delivery_service']) ?></td>
                    <td><?= htmlspecialchars($row['order_tracking_id']) ?></td>
                    <td><?= htmlspecialchars($row['response_status'] ?? '') ?></td>

                    <td><?= htmlspecialchars($row['updated_at']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>


        </table>
    </div>
</div>
<?php else: ?>
<p class="text-center text-danger">No orders found.</p>

<?php endif; ?>








<!-- Modal -->
<div class="modal fade" id="customOrderDeliveryModal" tabindex="-1" aria-labelledby="customOrderDeliveryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deliveryForm" method="post" action="submit_delivery.php">
                <!-- Update action as needed -->
                <div class="modal-header">
                    <h5 class="modal-title" id="customOrderDeliveryModalLabel">Custom Order Delivery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modalOrderId" class="form-label">Order ID</label>
                        <input type="text" class="form-control" id="modalOrderId" name="order_id" readonly>
                    </div>

                    <div class="mb-3 d-flex align-items-center">
                        <!-- Delivery Service -->
                        <div class="flex-grow-1 me-2">
                            <label for="deliveryService" class="form-label">Delivery Service</label>
                            <select class="form-select" id="deliveryService" name="delivery_service" required>
                                <option value="">-- Select Delivery Service --</option>
                                <?php
                                      $query = "SELECT id, service_name FROM delivery_services_list";
                                      $result = $connect->query($query);
                                      while($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['id']}'>{$row['service_name']}</option>";
                                      }
                                    ?>
                            </select>
                        </div>


                        <div class="mx-2 fw-bold pt-4">-</div>

                        <!-- Tracking ID -->
                        <div class="flex-grow-1 ms-2">
                            <label for="trackingId" class="form-label">Tracking ID</label>
                            <input type="text" class="form-control" id="trackingId" name="tracking_id"
                                placeholder="Enter Tracking ID">
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Delivery Info</button>
                </div>
            </form>
        </div>
    </div>
</div>













<script>
// Function to handle form submission
document.getElementById('deliveryForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    const form = new FormData(this);
    fetch('submit_delivery.php', {
            method: 'POST',
            body: form,
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Call the updateOrderStatus JS function after the PHP update
                updateOrderStatus(data.order_id, 4);
                alert("Order tracking info updated successfully.");
                $('#customOrderDeliveryModal').modal('hide'); // Hide the modal after success
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("There was an error submitting the form.");
        });
});
</script>