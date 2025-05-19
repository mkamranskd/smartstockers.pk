<?php require_once 'includes/header.php'; 
require_once '../php_action/db_connect.php'; 
include '../php_action/getOrderStatus.php';

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
    '10' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders")),
    '0' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 0")),
    '1' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 1")),
    '2' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 2")),
    '3' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 3")),
    '4' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 4")),
    '5' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 5")),
    '6' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 6")),
    '7' => mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS count FROM orders WHERE status = 7"))
    
];
?>



<div class="col-lg-12">
    <div class="bg-light rounded h-100 d-flex align-items-center p-4 pb-0">
        <div class="table-container w-100 p-2" style="zoom:0.9;">
            <nav aria-label="breadcrumb ">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="index.php">&nbsp;&nbsp;&nbsp;&nbsp;<i class="bi bi-house"></i></a>
                    </li>


                    <li class="breadcrumb-item active" aria-current="page">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="filter-tabs d-flex flex-wrap gap-2">

                                Orders

                            </div>
                        </div>

                        <!-- Table -->
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<div class="col-lg-12 mb-0" style="zoom:85%;">
    <div class="bg-light rounded h-100 d-flex align-items-center p-4 pb-0">

        <div class="accordion bg-white w-100" id="accordionExample">
            <div class="accordion-item bg-white">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed bg-white text-dark" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false"
                        aria-controls="collapseTwo">
                        <h6 class="mb-0">Filter & Tools</h6>
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body bg-white text-dark">
                        <div class="row align-items-end g-2 flex-nowrap overflow-auto">
                            <!-- From Date -->
                            <div class="col-auto">
                                <label for="startDate" class="form-label mb-1">From</label>
                                <input type="date" id="startDate" class="form-control" onclick="loadOrders(10);">
                            </div>

                            <!-- To Date -->
                            <div class="col-auto">
                                <label for="endDate" class="form-label mb-1">To</label>
                                <input type="date" id="endDate" class="form-control">
                            </div>

                            <!-- Filter Button -->
                            <div class="col-auto d-flex align-items-end">
                                <button class="btn btn-primary w-100" onclick="filterByCustomDate()">
                                    <i class="fa fa-filter" aria-hidden="true"></i>&nbsp;&nbsp;Filter
                                </button>
                            </div>

                            <!-- Clear Button -->
                            <div class="col-auto d-flex align-items-end">
                                <button class="btn btn-primary w-100" onclick="loadOrders(10);">
                                    <i class="fa fa-times" aria-hidden="true"></i>&nbsp;&nbsp;Clear
                                </button>
                            </div>
                        </div>
                        <br>
                        <div class="row align-items-end g-2 flex-nowrap overflow-auto">
                            <div class="col-auto">
                                <div class="container mt-4">
                                    <div class="input-group mb-3">
                                        <button class="btn btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#returnOrderSearchModal">
                                            Return Order
                                        </button>

                                    </div>
                                </div>

                            </div>



                        </div>
                    </div>
                </div>
            </div> <!-- accordion-item -->
        </div> <!-- accordion -->
    </div> <!-- table-container -->



    <!-- Clients Table Column -->
    <div class="col-lg-12">
        <div class="bg-light rounded h-100 d-flex align-items-center p-2 pb-0">
            <div class="col-sm-12">
                <div class=" bg-light rounded h-100 p-4">
                    <div class="table-container table-responsive p-4">
                        <div class="row align-items-center gy-2">
                            <!-- Title -->
                            <div class="col-12 col-md-4 d-flex align-items-center">
                                <div class="filter-tabs d-flex flex-wrap gap-2">
                                    <h5 class="mb-0">Orders</h5>
                                </div>
                            </div>

                            <!-- Controls -->
                            <div class="col-12 col-md-8">
                                <div class="d-flex align-items-center gap-2 flex-nowrap overflow-auto">
                                    <!-- Search -->
                                    <input type="text" class="form-control " id="searchBox1"
                                        placeholder="Search Orders..."
                                        onkeyup="setupTableSearch('searchBox1', 'myTable')" onclick="loadOrders(10);">
                                    <button class="btn btn-primary  align-items-center justify-content-center"
                                        onclick="location.reload()">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button><button
                                        class="btn btn-primary  d-flex align-items-center justify-content-center"
                                        onclick="window.location.href='place_new_order.php';">
                                        <i class="bi bi-plus"></i>&nbsp;
                                    </button>
                                    <button id="downloadTableDataInExcel"
                                        onclick="downloadTableDataInExcel('myTable','Orders');"
                                        class="btn btn-primary d-flex align-items-center justify-content-center">
                                        <i class="bi bi-download"></i>
                                    </button>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#printModal">
                                        <i class="fa fa-print"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                        <br>
                        <div class="row align-items-center gy-2" style="zoom:0.9;">
                            <nav>
                                <div class="d-flex flex-wrap" id="order-tabs" role="tablist">
                                    <button class="tab-nav" data-status="10"
                                        onclick="resetActiveTabs(this); loadOrders(10);">
                                        <span class="circle-badge bg-primary"><?= $statusCounts['10']['count'] ?></span>
                                        All
                                    </button>
                                    <button class="tab-nav" data-status="0"
                                        onclick="resetActiveTabs(this); loadOrders(0);">
                                        <span class="circle-badge bg-primary"><?= $statusCounts['0']['count'] ?></span>
                                        New Order Placed
                                    </button>
                                    <button class="tab-nav" data-status="1"
                                        onclick="resetActiveTabs(this); loadOrders(1)">
                                        <span
                                            class="circle-badge bg-secondary"><?= $statusCounts['1']['count'] ?></span>
                                        Confirmed
                                    </button>
                                    <button class="tab-nav" data-status="7"
                                        onclick="resetActiveTabs(this); loadOrders(7)">
                                        <span
                                            class="circle-badge bg-secondary"><?= $statusCounts['7']['count'] ?></span>
                                        Manual
                                    </button>
                                    <button class="tab-nav" data-status="2"
                                        onclick="resetActiveTabs(this); loadOrders(2)">
                                        <span class="circle-badge bg-info"><?= $statusCounts['2']['count'] ?></span>
                                        Ready to Shipped
                                    </button>
                                    <button class="tab-nav" data-status="3"
                                        onclick="resetActiveTabs(this); loadOrders(3)">
                                        <span class="circle-badge bg-info"><?= $statusCounts['3']['count'] ?></span>
                                        Shipped
                                    </button>
                                    <button class="tab-nav" data-status="4"
                                        onclick="resetActiveTabs(this); loadOrders(4)">
                                        <span class="circle-badge bg-success"><?= $statusCounts['4']['count'] ?></span>
                                        Delivered
                                    </button>
                                    <button class="tab-nav" data-status="5"
                                        onclick="resetActiveTabs(this); loadOrders(5)">
                                        <span class="circle-badge bg-success"><?= $statusCounts['5']['count'] ?></span>
                                        Pending
                                    </button>
                                    <button class="tab-nav" data-status="6"
                                        onclick="resetActiveTabs(this); loadOrders(6)">
                                        <span class="circle-badge bg-danger"><?= $statusCounts['6']['count'] ?></span>
                                        Cancelled/Returned
                                    </button>


                                </div>
                            </nav>
                        </div>
                        <div class="tab-content " id="orders-container">
                            <div id="orders-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


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

                            <th>City</th>
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

                <style>
                /* Apply border only to horizontal rows */
                #printTable {
                    border-collapse: collapse;
                    width: 100%;
                }

                #printTable th,
                #printTable td {
                    border-top: 1px solid black;
                    /* Horizontal top border */
                    border-bottom: 1px solid black;
                    /* Horizontal bottom border */
                    padding: 8px;
                    /* Optional: Add padding for readability */
                    text-align: left;
                    /* Optional: Align text to the left */
                }

                /* Remove vertical borders */
                #printTable th:first-child,
                #printTable td:first-child {
                    border-left: none;
                }

                #printTable th:last-child,
                #printTable td:last-child {
                    border-right: none;
                }
                </style>


            </div>
            <div class="modal-footer">
                <button onclick="printOrders()" class="btn btn-primary"><i class="fa fa-print"></i>
                    Print</button>
            </div>
        </div>
    </div>
</div>

<script>
let printOrdersList = [];

function addToPrintList(orderData) {
    // Check for duplicates
    if (printOrdersList.find(o => o.id === orderData.id)) {
        showMessage("Already added in print list", "danger");
        return;
    }

    printOrdersList.push(orderData);
    renderPrintTable();
    //$('#printModal').modal('show');
    showMessage("Order Added for Print ", "primary");
}

function renderPrintTable() {
    const tbody = document.querySelector('#printTable tbody');
    tbody.innerHTML = '';

    printOrdersList.forEach((order, index) => {
        const totalQuantities = Array.isArray(order.quantities) ?
            order.quantities.reduce((sum, val) => sum + Number(val), 0) :
            order.quantities.toString().split(',').reduce((sum, val) => sum + Number(val.trim()), 0);

        const totalWeights = Array.isArray(order.weights) ?
            order.weights.reduce((sum, val) => sum + Number(val), 0) :
            order.weights.toString().split(',').reduce((sum, val) => sum + Number(val.trim()), 0);

        const totalSubtotals = Array.isArray(order.subtotals) ?
            order.subtotals.reduce((sum, val) => sum + Number(val), 0) :
            order.subtotals.toString().split(',').reduce((sum, val) => sum + Number(val.trim()), 0);

        const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${order.id}</td>
                <td>${order.customer_name}</td>
                <td>${order.city}</td>
                <td>${totalQuantities}</td>
                <td>${totalWeights}</td>
                <td>${totalSubtotals}</td>
                <td>${order.order_tracking_id}</td>
                <td>
                    <span onclick="removeFromPrintList(${order.id})" class="btn btn-sm btn-danger">
                        <i class="fa fa-trash"></i>
                    </span>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

function removeFromPrintList(orderId) {
    printOrdersList = printOrdersList.filter(order => order.id !== orderId);
    renderPrintTable();
    showMessage("Order removed from print list", "danger");
}


function addToPrintListFromRow(orderId) {
    const row = document.querySelector(`#order-${orderId}`);
    if (!row) return;

    const tds = row.querySelectorAll('td');

    const customer = tds[6].textContent.trim();
    const city = tds[9].textContent.trim();
    const qty = parseFloat(tds[12].textContent.trim());
    const weight = parseFloat(tds[14].textContent.trim());
    const cod = parseFloat(tds[11].textContent.trim());
    const tracking = tds[17].textContent;

    const orderData = {
        id: orderId,
        customer_name: customer,
        city: city,
        quantities: [qty],
        weights: [weight],
        subtotals: [cod],
        order_tracking_id: tracking
    };

    addToPrintList(orderData);
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
                /* Apply border only to horizontal rows */
                #printTable {
                    border-collapse: collapse;
                    width: 100%;
                }

                #printTable th,
                #printTable td {
                    border-top: 1px solid black;
                    /* Horizontal top border */
                    border-bottom: 1px solid black;
                    /* Horizontal bottom border */
                    padding: 8px;
                    /* Optional: Add padding for readability */
                    text-align: left;
                    /* Optional: Align text to the left */
                }

                /* Remove vertical borders */
                #printTable th:first-child,
                #printTable td:first-child {
                    border-left: none;
                }

                #printTable th:last-child,
                #printTable td:last-child {
                    border-right: none;
                }
                
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

function deleteOrder(orderId) {
    if (confirm("Are you sure you want to delete this order?")) {


        fetch('delete_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'order_id=' + orderId
            })
            .then(response => response.text())
            .then(data => {

                location.reload(); // Refresh the page after deletion
                showMessage(data, 'info');

            })
            .catch(error => {
                alert("Error: " + error);
            });
    }
}
</script>

<script>
function showDeliveryChargesModal(orderId, codAmount) {
    document.getElementById('dc_order_id').value = orderId;
    document.getElementById('codAmount').value = codAmount;
    document.getElementById('deliveryCharges').value = '';
    document.getElementById('dcStatus').innerText = '';
    const modal = new bootstrap.Modal(document.getElementById('deliveryChargesModal'));
    modal.show();
}

function saveDeliveryCharges() {
    const orderId = document.getElementById('dc_order_id').value;
    const codAmount = parseFloat(document.getElementById('codAmount').value);
    const deliveryCharges = parseFloat(document.getElementById('deliveryCharges').value);

    if (isNaN(deliveryCharges)) {
        alert("Please enter valid delivery charges.");
        return;
    }

    const total = codAmount + deliveryCharges;

    fetch('save_delivery_charges.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                order_id: orderId,
                total_cod: total,
                delivery_charges: deliveryCharges
            })
        })

        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const modal = new bootstrap.Modal(document.getElementById('deliveryChargesModal'));
                modal.hide();
                showMessage("DC Saved Successfully", "primary");
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));

            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to update.');
        });
}










function viewReturnOrderDetails() {
    const orderId = document.getElementById('returnOrderIdInput').value;
    if (!orderId) return alert("Please enter an Order ID");

    fetch('fetch_order_for_return.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'order_id=' + encodeURIComponent(orderId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) return alert(data.error);

            // Fill inputs
            document.getElementById('client_id').value = data.client_id;
            document.getElementById('order_id').value = data.order_id;
            document.getElementById('customer_name').value = data.customer_name;
            document.getElementById('customer_phone').value = data.customer_phone;
            document.getElementById('delivery_address').value = data.delivery_address;
            document.getElementById('city').value = data.city;
            document.getElementById('order_date').value = data.order_date;
            document.getElementById('order_tracking_id').value = data.order_tracking_id;
            document.getElementById('total_amount').value = data.total_amount;

            let itemsText = '';
            data.items.forEach(item => {
                itemsText +=
                    `(${item.product_id}) ${item.product_name} - Price: ${item.price} x Qty: ${item.quantity}\n`;
            });
            document.getElementById('orderItemsTextarea').value = itemsText;

            // Hide first modal, show second
            const searchModal = bootstrap.Modal.getInstance(document.getElementById('returnOrderSearchModal'));
            searchModal.hide();

            const detailModal = new bootstrap.Modal(document.getElementById('returnOrderDetailsModal'));
            detailModal.show();
        })
        .catch(err => alert("Error fetching order: " + err));
}

function returnOrderFinal() {
    const orderId = document.getElementById('returnOrderIdInput').value;

    fetch('return_order_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'order_id=' + encodeURIComponent(orderId)
        })
        .then(res => res.text())
        .then(response => {
            document.getElementById('returnResult').textContent = response.trim() === 'success' ?
                'Order returned successfully.' :
                'Return failed: ' + response;

            location.reload();


        })
        .catch(err => alert("Return failed: " + err));
}
</script>


<!-- Modal for Delivery Charges -->
<div class="modal fade" id="deliveryChargesModal" tabindex="-1" aria-labelledby="deliveryChargesLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveryChargesLabel">Add Delivery Charges</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="deliveryChargesForm">
                    <input type="hidden" id="dc_order_id">
                    <div class="mb-3">
                        <label for="codAmount" class="form-label">COD Amount</label>
                        <input type="text" class="form-control" id="codAmount" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="deliveryCharges" class="form-label">Delivery Charges</label>
                        <input type="number" class="form-control" id="deliveryCharges" required>
                    </div>
                </form>
                <div id="dcStatus" class="text-success"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="saveDeliveryCharges()">Save</button>
            </div>
        </div>
    </div>
</div>












<!-- Step 1: Search Modal -->
<div class="modal fade" id="returnOrderSearchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Find Order to Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Order ID:</label>
                <input type="number" id="returnOrderIdInput" class="form-control" placeholder="Enter Order ID">
            </div>
            <div class="modal-footer">
                <button onclick="viewReturnOrderDetails()" class="btn btn-primary">View</button>
            </div>
        </div>
    </div>
</div>
<!-- Return Order Preview Modal -->
<div class="modal fade" id="returnOrderDetailsModal" tabindex="-1" aria-labelledby="returnModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Return Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="container-fluid">

                    <div class="row mb-2">
                        <label class="col-md-2 col-form-label">Client ID</label>
                        <div class="col-md-4"><input readonly id="client_id" class="form-control"></div>

                        <label class="col-md-2 col-form-label">Store Order ID</label>
                        <div class="col-md-4"><input readonly id="order_id" class="form-control"></div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-md-2 col-form-label">Tracking ID</label>
                        <div class="col-md-4"><input readonly id="order_tracking_id" class="form-control"></div>

                        <label class="col-md-2 col-form-label">Customer Name</label>
                        <div class="col-md-4"><input readonly id="customer_name" class="form-control"></div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-md-2 col-form-label">Customer Phone</label>
                        <div class="col-md-4"><input readonly id="customer_phone" class="form-control"></div>

                        <label class="col-md-2 col-form-label">Delivery Address</label>
                        <div class="col-md-4"><input readonly id="delivery_address" class="form-control"></div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-md-2 col-form-label">City</label>
                        <div class="col-md-4"><input readonly id="city" class="form-control"></div>

                        <label class="col-md-2 col-form-label">Order Date</label>
                        <div class="col-md-4"><input readonly id="order_date" class="form-control"></div>
                    </div>

                    <div class="row mb-2">
                        <label class="col-md-2 col-form-label">Total Amount</label>
                        <div class="col-md-4"><input readonly id="total_amount" class="form-control"></div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-2 col-form-label">Order Items</label>
                        <div class="col-md-10">
                            <textarea readonly id="orderItemsTextarea" class="form-control" rows="5"></textarea>
                        </div>
                    </div>

                    <div id="returnResult" class="text-success fw-bold mt-2 text-center"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button onclick="returnOrderFinal()" class="btn btn-danger">Return Order</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="orders.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js">
</script>
<?php require_once 'includes/footer.php'; ?>