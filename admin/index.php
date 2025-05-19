<?php
require_once '../php_action/db_connect.php';
require_once 'includes/header.php';
require_once '../php_action/functions.php';
$notifications = getUnreadNotifications(0); // use session user id



$user_id = intval($_SESSION['userId']);

// addNotification($user_id, "Your order 010 has been delivered.");


$sql = "SELECT COUNT(*) AS total FROM products";
$result = $connect->query($sql);

$sql = "SELECT SUM(stock_quantity) as stock FROM `products` WHERE 1";
$queryy = $connect->query($sql);
$totalStock = ($queryy) ? $queryy->fetch_assoc()['stock'] : 0;


// Fetch the count
$countProduct = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $countProduct = $row['total'];
}

$clientsql = "SELECT COUNT(*) as count FROM clients";
$queryy = $connect->query($clientsql);
$countclients = ($queryy) ? $queryy->fetch_assoc()['count'] : 0;



$sqll = "SELECT COUNT(*) AS total FROM users";
$resultt = $connect->query($sqll);

$countUsers = 0;
if ($resultt->num_rows > 0) {
    $row = $resultt->fetch_assoc();
    $countUsers = $row['total'];
    $countUsers--;
}


$statusCounts = [
    '10' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders ")),
    '0' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 0")),
    '1' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 1")),
    '2' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 2")),
    '3' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 3")),
    '4' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 4")),
    '5' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 5")),
    '6' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 6")),
    
];



// Get client_id and last_login time
$userQuery = $connect->query("SELECT client_id, last_login FROM users WHERE user_id = '$userId'");
$user = $userQuery->fetch_assoc();
$clientId = $user['client_id'];
$lastLogin = $user['last_login'];

$newOrders = 0;
$updatedOrders = 0;

// New orders since last login
$newOrdersQuery = $connect->query("
    SELECT COUNT(*) as count FROM orders 
    WHERE client_id = '$clientId' AND created_at > '$lastLogin'
");
$newOrders = $newOrdersQuery->fetch_assoc()['count'];

// Updated orders since last login
$updatedOrdersQuery = $connect->query("
    SELECT COUNT(*) as count FROM orders 
    WHERE client_id = '$clientId' AND updated_at > '$lastLogin' AND created_at < updated_at
");
$updatedOrders = $updatedOrdersQuery->fetch_assoc()['count'];

if ($newOrders > 0 || $updatedOrders > 0): ?>
<div class="alert alert-info">
    <h5>Welcome back!</h5>
    <?php if ($newOrders > 0): ?>
    <p>You have <strong><?= $newOrders ?></strong> new order(s) since your last visit.</p>
    <?php endif; ?>
    <?php if ($updatedOrders > 0): ?>
    <p><strong><?= $updatedOrders ?></strong> order(s) have been updated.</p>
    <?php endif; ?>
</div>
<?php endif; 

$connect->close();
?>



<script>
document.addEventListener("DOMContentLoaded", function() {
    fetch('chartdata.php')
        .then(response => response.json())
        .then(data => {
            const labels = Object.keys(data);
            const values = Object.values(data);

            const ctx = document.getElementById('line-chart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Delivered Orders (Last 7 Days)',
                        data: values,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true
                        },
                        title: {
                            display: true,
                            text: 'Delivered Orders - Past 7 Days'
                        },
                        tooltip: {
                            enabled: true,
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.dataset.label}: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
});
</script>



<!-- <div class="col-sm-12 col-xl-6">
    <div class="bg-light rounded h-100 p-4 bg-white">


        <h6 class="mb-4">Single Line Chart</h6>
        <canvas id="line-chart" width="100%" height="40"></canvas>

    </div>
</div> -->
<div class="row">

    <div class="col-lg-12">
        <div class="bg-light rounded h-100 d-flex align-items-center p-4 pb-0">
            <div class="table-container w-100 p-2" style="zoom:0.9;">
                <nav aria-label="breadcrumb ">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item active" aria-current="page">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="filter-tabs d-flex flex-wrap gap-2">
                                    <a href="index.php">&nbsp;&nbsp;&nbsp;&nbsp;<i
                                            class="bi bi-house"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Home</a>
                                </div>
                            </div>

                            <!-- Table -->
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>








    <div class="col-lg-12">
        <div class="bg-light rounded h-100 p-4">

            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-2 px-4">
                <div class="row g-4">
                    <p class="h5">Inventory & User Statistics</p>

                    <div class="col-sm-6 col-xl-3">
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

                    <div class="col-sm-6 col-xl-3">
                        <a href="client.php">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fa fa-users fa-2x text-primary"></i>
                                <div class="ms-3">
                                    <p class="mb-2">Total clients</p>
                                    <h6 class="mb-0"><?php echo $countclients; ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-3">
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
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                            <i class="fa fa-user fa-2x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Total Users</p>
                                <h6 class="mb-0"><?php echo $countUsers-1; ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br>

            <!-- Sale & Revenue End -->
            <div class="container-fluid  px-4">
                <div class="row g-4">
                    <p class="h5">Order Statistics</p>
                    <div class="col-sm-6 col-xl-3">
                        <a href="orders.php" onclick="saveStatus(10); ">
                            <div
                                class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                                <i class="fas fa-list-alt fa-2x text-primary"></i>
                                <div class="ms-3">
                                    <p class="mb-2">Total Orders</p>
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['10']['count'] ?></h6>
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
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['0']['count'] ?></h6>
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
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['1']['count'] ?></h6>
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
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['2']['count'] ?></h6>
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
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['3']['count'] ?></h6>
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
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['4']['count'] ?></h6>
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
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['5']['count'] ?></h6>
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
                                    <h6 class="mb-0 text-danger"><?= $statusCounts['6']['count'] ?></h6>
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