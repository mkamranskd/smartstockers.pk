<?php require_once 'includes/header.php'; 
require_once '../php_action/db_connect.php'; 
include '../php_action/getOrderStatus.php';
// Fetch client_id from users table
$user_id = $_SESSION['userId'];

// Fetch client_id from users table
$sql = "SELECT client_id FROM users WHERE user_id = {$user_id}";
$result = $connect->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $client_id = mysqli_real_escape_string($connect, $row['client_id']);
} else {
    die("Client ID not found.");
}















$query = "SELECT orders.*, products.product_name, products.price, products.weight 
          FROM orders 
          JOIN products ON orders.order_id = products.id";
$result = mysqli_query($connect, $query);
$orders = [];

while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row; // Store all orders in an array
}
?>

<link rel="stylesheet" href="orders.css">
<script>
document.addEventListener("DOMContentLoaded", function() {
    const savedStatus = localStorage.getItem("activeOrderTab");
    const defaultStatus = savedStatus || "10"; // fallback to "All" if nothing saved
    const activeTab = document.querySelector(`#order-tabs .tab-nav[data-status="${defaultStatus}"]`);

    if (activeTab) {
        resetActiveTabs(activeTab);
        loadOrders(defaultStatus);
    }
});
</script>
<?php
// Fetching record count for each tab
$statusCounts = [
    '10' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE client_id = '$client_id'")),
    '0'  => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 0 AND client_id = '$client_id'")),
    '1'  => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 1 AND client_id = '$client_id'")),
    '2'  => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 2 AND client_id = '$client_id'")),
    '3'  => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 3 AND client_id = '$client_id'")),
    '4'  => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 4 AND client_id = '$client_id'")),
    '5'  => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 5 AND client_id = '$client_id'")),
    '6'  => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 6 AND client_id = '$client_id'")),
    '7'  => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE DATE(updated_at) = CURDATE() AND client_id = '$client_id'"))
];
?>

<div class="row h-auto" style="  zoom:85%;">

    <!-- Clients Table Column -->
    <div class="col-lg-12">
        <div class="bg-light">
            <div class="col-sm-12">
                <div class=" bg-light rounded h-100 p-4">


                    <div class="">



                        <div class="row g-2 ">

                            <div class="col-12 col-sm-6 col-md-4 col-lg-6">

                            </div>

                            <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                                <!-- Search Box -->
                                <input type="text" class="form-control " id="searchBox1" placeholder="Search Orders..."
                                    onkeyup="setupTableSearch('searchBox1', 'myTable')" onclick="loadOrders(10);">

                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-1">
                                <!-- Search Box -->
                                <button class="btn btn-primary w-100 align-items-center justify-content-center"
                                    onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-1">
                                <!-- Add Button -->
                                <button class="btn btn-primary w-100 d-flex align-items-center justify-content-center"
                                    onclick="window.location.href='place_new_order.php';">
                                    <i class="bi bi-plus"></i>&nbsp; Order
                                </button>
                            </div>

                        </div>
                        <br>





                        <div class="row g-2">


                            <div class="col-12 col-sm-6 col-md-4 col-lg-6">
                                <h5 class="mb-0 d-flex align-items-center">
                                    <i class="fa fa-shopping-cart me-2"></i> Orders
                                </h5>
                            </div>

                            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                                <input type="date" id="startDate" class="form-control" placeholder="Start Date"
                                    onclick="loadOrders(10);">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                                <input type="date" id="endDate" class="form-control" placeholder="End Date">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-1">
                                <button class="btn btn-primary w-100" onclick="filterByCustomDate()">
                                    <i class="fa fa-filter" aria-hidden="true"></i>&nbsp;&nbsp;Filter
                                </button>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-1">
                                <button class="btn btn-primary w-100" onclick="loadOrders(10);">
                                    <i class="fa fa-times" aria-hidden="true"></i>&nbsp;&nbsp;Clear
                                </button>
                            </div>
                        </div>
                        <br>
                        <div class="row g-2">




                            <div class="col-12 col-sm-6 col-md-4 col-lg-10">

                            </div>

                            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                                <button id="downloadTableDataInExcel"
                                    onclick="downloadTableDataInExcel('myTable','Orders');"
                                    class="btn btn-primary d-flex align-items-center justify-content-center w-100">
                                    <i class="bi bi-download"></i>&nbsp;&nbsp; Download in Excel
                                </button>

                            </div>

                        </div>
                    </div>







                    <br>
                    <nav>
                        <div class="d-flex flex-wrap" id="order-tabs" role="tablist">
                            <button class="tab-nav" data-status="10" onclick="resetActiveTabs(this); loadOrders(10);">
                                <span class="circle-badge bg-primary"><?= $statusCounts['10']['count'] ?></span>
                                All
                            </button>
                            <button class="tab-nav" data-status="0" onclick="resetActiveTabs(this); loadOrders(0);">
                                <span class="circle-badge bg-primary"><?= $statusCounts['0']['count'] ?></span>
                                New Order Placed
                            </button>
                            <button class="tab-nav" data-status="1" onclick="resetActiveTabs(this); loadOrders(1)">
                                <span class="circle-badge bg-secondary"><?= $statusCounts['1']['count'] ?></span>
                                Confirmed
                            </button>
                            <button class="tab-nav" data-status="2" onclick="resetActiveTabs(this); loadOrders(2)">
                                <span class="circle-badge bg-info"><?= $statusCounts['2']['count'] ?></span>
                                Ready to Shipped
                            </button>
                            <button class="tab-nav" data-status="3" onclick="resetActiveTabs(this); loadOrders(3)">
                                <span class="circle-badge bg-info"><?= $statusCounts['3']['count'] ?></span>
                                Shipped
                            </button>
                            <button class="tab-nav" data-status="4" onclick="resetActiveTabs(this); loadOrders(4)">
                                <span class="circle-badge bg-success"><?= $statusCounts['4']['count'] ?></span>
                                Delivered
                            </button>
                            <button class="tab-nav" data-status="5" onclick="resetActiveTabs(this); loadOrders(5)">
                                <span class="circle-badge bg-success"><?= $statusCounts['5']['count'] ?></span>
                                Pending
                            </button>
                            <button class="tab-nav" data-status="6" onclick="resetActiveTabs(this); loadOrders(6)">
                                <span class="circle-badge bg-danger"><?= $statusCounts['6']['count'] ?></span>
                                Cancelled/Returned
                            </button>

                        </div>



                    </nav>

                    <div class="tab-content pt-3 border" id="orders-container">
                        <div id="orders-content"></div>
                    </div>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap Modal 
-->
<div class="modal fade" id="awb-modal" tabindex="-1" aria-labelledby="awbModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="awbModalLabel">Airway Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="awb-content">
                    <!-- Order details will be inserted here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="printAWB()">
                    <i class="fa fa-print"></i> Print AWB
                </button>

                <button class="btn btn-success" onclick="shareToWhatsApp()">
                    <i class="fa fa-whatsapp"></i> Share to WhatsApp
                </button>

                <button class="btn btn-danger" onclick="downloadPDF()">
                    <i class="fa fa-file-pdf"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="postex-order-modal" tabindex="-1" aria-labelledby="awbModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="awbModalLabel">Postex Order Creation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="postex-order-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="postExConfirmAndCreateOrder();">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="leopards-order-modal" tabindex="-1" aria-labelledby="awbModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="awbModalLabel">Leopards Order Creation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="leopards-order-content"></div>
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success"
                    onclick="leopardsConfirmAndCreateOrder(); ">Confirm</button>
            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<div style="zoom:85%;" class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printModalLabel">Print Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" id="printTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>City</th>
                            <th>Products</th>
                            <th>Quantity</th>
                            <th>Weight</th>
                            <th>COD</th>
                            <th>Tracking ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be inserted here dynamically -->
                    </tbody>
                </table>

                <!-- Custom CSS -->
                <style>
                #printTable {
                    width: 100%;
                    border-collapse: collapse;
                }

                #printTable th,
                #printTable td {
                    border: 1px solid black;
                    padding: 8px;
                    text-align: center;
                }

                #printTable thead {
                    background-color: #f8f9fa;
                }
                </style>

            </div>
            <div class="modal-footer">
                <button onclick="printOrders()" class="btn btn-primary"><i class="fa fa-print"></i> Print</button>
            </div>
        </div>
    </div>
</div>

<script>
let printOrdersList = [];

function addToPrintList(orderData) {
    // Check for duplicates
    if (printOrdersList.find(o => o.id === orderData.id)) return;

    printOrdersList.push(orderData);
    renderPrintTable();
    //$('#printModal').modal('show');
    showMessage("Order Added for Print ", "primary");
}

function renderPrintTable() {
    const tbody = document.querySelector('#printTable tbody');
    tbody.innerHTML = '';
    printOrdersList.forEach((order, index) => {
        const row = `
            <tr>
                <td>${index + 1}</td> <!-- Serial number -->
                <td>${order.id}</td>
                <td>${order.customer_name}</td>
                <td>${order.customer_phone}</td>
                <td>${order.shipping_address}</td>
                <td>${order.city}</td>
                <td>${order.product_names}</td>
                <td>${order.quantities}</td>
                <td>${order.weights}</td>
                <td>${order.subtotals}</td>
                <td>${order.order_tracking_id}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}


function printOrders() {
    const printContent = document.querySelector("#printTable").outerHTML;
    const newWin = window.open('', '', 'width=1000,height=800');
    newWin.document.write(
        `
        <html>

<head>
    <title>SmartStockers</title>
    <style>
    #printTable {
        border: 2px solid black;
    }

    #printTable th,
    #printTable td {
        border: 1px solid black;
    }

    .center-image {
        display: block;
        margin-left: auto;
        margin-right: auto;
        max-width: 150px;
    }
    </style>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
</head>

<body>

    <!-- Centered Image -->
    <img src="../img/logo.png" alt="Logo" class="center-image" height="100">
<br>
<br>
<br>
    <div id="printContent">
        ${printContent}
    </div>
    <br>
    <br>
    <br>
    <label>Name</label>:
    <span
        style="border-bottom:1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    <br>
    <br>
    <br>
    <label>Signature</label>:
    <span
        style="border-bottom:1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    </span>
</body>

</html>

`
    );
    newWin.document.close();
    newWin.focus();
    newWin.print();
    newWin.close();
}
</script>

<script src="orders.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<?php require_once 'includes/footer.php'; ?>