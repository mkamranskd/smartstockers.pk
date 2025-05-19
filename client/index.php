<?php
require_once '../php_action/db_connect.php';
require_once 'includes/header.php';
$user_id = $_SESSION['userId'];
$sql = "SELECT client_id FROM users WHERE user_id = {$user_id}";
$result = $connect->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $client_id = $row['client_id'];
   
} else {
    echo "Client ID not found.";
}



$sql = "SELECT COUNT(*) AS total FROM products WHERE  client_id = '$client_id'";
$result = $connect->query($sql);

$sql = "SELECT SUM(stock_quantity) as stock FROM `products` WHERE  client_id = '$client_id'";
$queryy = $connect->query($sql);
$totalStock = ($queryy) ? $queryy->fetch_assoc()['stock'] : 0;


// Fetch the count
$countProduct = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $countProduct = $row['total'];
}




$sqll = "SELECT COUNT(*) AS total FROM users";
$resultt = $connect->query($sqll);

$countUsers = 0;
if ($resultt->num_rows > 0) {
    $row = $resultt->fetch_assoc();
    $countUsers = $row['total'];
}


// Make sure $client_id is already set before this block
$statusCounts = [
    '0' => 0,  // New Order
    '1' => 0,  // Confirmed
    '2' => 0,  // Packed
    '3' => 0,  // Shipped
    '4' => 0,  // Delivered
    '5' => 0,  // Returned
    '6' => 0,  // Cancelled
    '10' => 0, // Total Orders
];

$query = "SELECT status, COUNT(*) AS count FROM orders WHERE client_id = '$client_id' GROUP BY status";
$result = mysqli_query($connect, $query);

$total = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $status = $row['status'];
    $count = $row['count'];

    if (isset($statusCounts[$status])) {
        $statusCounts[$status] = $count;
    }

    $total += $count;
}
$statusCounts['10'] = $total;



$connect->close();
?>

<div class="row">
    <!-- Clients Table Column -->
    <div class="col-lg-12">
        <div class="bg-light rounded h-100 p-4">

            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <p class="h5">Inventory & User Statistics</p>

                    <div class="col-sm-6 col-xl-4">
                        <a href="products.php">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fa fa-boxes fa-2x text-primary"></i>
                                <div class="ms-3">
                                    <p class="mb-2">In Stock Products</p>
                                    <h6 class="mb-0"><?php echo $totalStock??0; ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>


                    <div class="col-sm-6 col-xl-4">
                        <a href="products.php">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fa fa-box-open fa-2x text-primary"></i>
                                <div class="ms-3">
                                    <p class="mb-2">Total Products</p>
                                    <h6 class="mb-0"><?php echo $countProduct; ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
            <br>

            <!-- Sale & Revenue End -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <p class="h5">Order Statistics</p>
                    <div class="col-sm-6 col-xl-3">
                        <a href="orders.php" onclick="saveStatus(10); ">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fas fa-list-alt fa-2x text-primary"></i>
                                <div class="ms-3">
                                    <p class="mb-2">Total Orders</p>
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['10'] ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <script>
                    function saveStatus(status) {
                        localStorage.setItem("activeOrderTab", status);
                        loadOrders(status);
                    }
                    </script>

                    <div class="col-sm-6 col-xl-3">
                        <a href="orders.php" onclick="loadOrders(0)">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                                <div class="ms-3">
                                    <p class="mb-2">New Order</p>
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['0'] ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <a href="orders.php" onclick="loadOrders(1)">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fas fa-check-circle fa-2x text-secondary"></i>
                                <div class="ms-3">
                                    <p class="mb-2">Confirmed</p>
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['1'] ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <a href="orders.php" onclick="loadOrders(2)">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fas fa-box-open fa-2x text-info"></i>
                                <div class="ms-3">
                                    <p class="mb-2">Ready to Ship</p>
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['2'] ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <a href="orders.php" onclick="loadOrders(3)">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fas fa-truck fa-2x text-success"></i>
                                <div class="ms-3">
                                    <p class="mb-2">Shipped</p>
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['3'] ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <a href="orders.php" onclick="loadOrders(4)">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fas fa-check fa-2x text-success"></i>
                                <div class="ms-3">
                                    <p class="mb-2">Delivered</p>
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['4'] ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <a href="orders.php" onclick="loadOrders(5)">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                                <div class="ms-3">
                                    <p class="mb-2">Pending</p>
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['5'] ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <a href="orders.php" onclick="loadOrders(6)">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                                <div class="ms-3">
                                    <p class="mb-2">Cancelled/Returned</p>
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['6'] ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>



<?php require_once 'includes/footer.php'; ?>