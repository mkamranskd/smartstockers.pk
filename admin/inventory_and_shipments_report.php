<?php
require_once '../php_action/db_connect.php'; 
require_once 'includes/header.php';
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Initialize total_orders to avoid undefined variable
$total_orders = 0;

// --- Handle form submission ---
$report = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientId = $_POST['client_id'];
 $clientStmt = $connect->prepare("SELECT * FROM clients WHERE client_id = ?");
$clientStmt->bind_param("s", $clientId);
$clientStmt->execute();
$clientResult = $clientStmt->get_result();
$client = $clientResult->fetch_assoc();
$clientStmt->close();

$rateOfProduct = $client['rate_of_product'];
$clientName = $client['vendor_name'];
$clientAddress = $client['address'];
$clientPhone = $client['phone_number'];
$clientEmail = $client['email'];

    $client_id = $_POST['client_id'];
    $from_date = $_POST['from_date'];
    $to_date   = $_POST['to_date'];

   $sql = "
    SELECT 
        p.product_name,
        p.product_id,
        COALESCE(sa.total_stock_added, 0) AS total_stock_added,
        sa.stock_added_dates,
        COALESCE(sh.total_shipped, 0) AS total_shipped,
        (COALESCE(sa.total_stock_added, 0) - COALESCE(sh.total_shipped, 0)) AS stock_remaining,
        sh.order_ids
    FROM products p
    LEFT JOIN (
        SELECT 
            product_id,
            client_id,
            SUM(quantity_added) AS total_stock_added,
            GROUP_CONCAT(DATE_FORMAT(added_at, '%d-%b-%y') ORDER BY added_at DESC SEPARATOR ', ') AS stock_added_dates
        FROM stock_logs
        WHERE added_at BETWEEN ? AND ?
        GROUP BY product_id, client_id
    ) sa ON sa.product_id = p.product_id AND sa.client_id = p.client_id
    LEFT JOIN (
        SELECT 
            oi.product_id,
            o.client_id,
            SUM(oi.quantity) AS total_shipped,
            GROUP_CONCAT(CONCAT(oi.quantity, ' on #', o.order_id) ORDER BY o.order_id SEPARATOR '<br> ') AS order_ids
        FROM order_items oi
        INNER JOIN orders o ON o.order_id = oi.order_id
        WHERE o.order_date BETWEEN ? AND ? AND o.status != 6
        GROUP BY oi.product_id, o.client_id
    ) sh ON sh.product_id = p.product_id AND sh.client_id = p.client_id
    WHERE p.client_id = ?
    ORDER BY p.product_name
";



    $stmt = $connect->prepare($sql);

$stmt->bind_param("sssss", $from_date, $to_date, $from_date, $to_date, $client_id);

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $report[] = $row;
    }
    
    // Get total orders count for client in selected date range
    $orderCountSql = "
        SELECT COUNT(DISTINCT o.order_id) as total_orders
        FROM orders o
        WHERE o.client_id = ?
        AND o.order_date BETWEEN ? AND ?
    ";

    $stmt2 = $connect->prepare($orderCountSql);
    $stmt2->bind_param("sss", $client_id, $from_date, $to_date);
    $stmt2->execute();
    $orderCountResult = $stmt2->get_result();
    $orderCountRow = $orderCountResult->fetch_assoc();
    $total_orders = $orderCountRow['total_orders'] ?? 0;
    
}

// Fetch available client IDs for the dropdown
$clients_result = mysqli_query($connect, "SELECT client_id, vendor_name FROM clients");

if (!$clients_result) {
    die("Error fetching clients: " . mysqli_error($connect));  // Debugging error
}
?>




<div class="col-lg-12">
    <div class="bg-light rounded h-100 d-flex align-items-center p-4 pb-0">
        <div class="table-container w-100 p-2" style="zoom:0.9;">
            <nav aria-label="breadcrumb ">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="index.php">&nbsp;&nbsp;&nbsp;&nbsp;<i class="bi bi-house"></i></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="invoices.php">Reports</a>
                    </li>

                    <li class="breadcrumb-item active" aria-current="page">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="filter-tabs d-flex flex-wrap gap-2">

                                Inventory and Shipments Report

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
        <div class="table-container p-4">
            <!-- Filter Tabs & Search Bar (Row 1) -->
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="filter-tabs d-flex flex-wrap gap-2">
                    <h5 class="mb-0 d-flex align-items-center">
                        Inventory and Shipments Report
                    </h5>
                </div>
            </div>
            <div class="row">
                <form id="invoiceForm" method="POST" class="row g-3 mb-4">

                    <div class="col-md-4">
                        <label for="client_id" class="form-label">Select Client</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">-- Select Client --</option>
                            <?php
        if (mysqli_num_rows($clients_result) > 0) {
            while ($client = mysqli_fetch_assoc($clients_result)):
                // Check if this option was selected
                $selected = (isset($client_id) && $client_id == $client['client_id']) ? 'selected' : '';
        ?>
                            <option value="<?= $client['client_id'] ?>" <?= $selected ?>>
                                <?= htmlspecialchars($client['vendor_name']) ?>
                            </option>
                            <?php endwhile;
        } else {
            echo "<option>No clients available</option>";
        }
        ?>
                        </select>

                    </div>


                    <div class="col-md-3">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control" required
                            value="<?= isset($from_date) ? htmlspecialchars($from_date) : '' ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control" required
                            value="<?= isset($to_date) ? htmlspecialchars($to_date) : '' ?>">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Generate Invoice</button>
                    </div>




                </form>
            </div>
        </div>
    </div>
</div>





<!-- Invoice Result Area -->
<div class="row" id="invoiceRow">
    <div class="col-lg-12">
        <div class="bg-light rounded h-100 p-4">
            <div id="invoicePrintArea">
                <!-- Invoice will be loaded here -->
            </div>


        </div>

    </div>
</div>


<!-- Report Table -->
<div class="col-lg-12" id="invoiceRoww"
    style="display: <?= $_SERVER["REQUEST_METHOD"] == "POST" ? 'block' : 'none' ?>;">
    <div class="bg-light rounded h-100 p-4">
        <!-- Breadcrumb & Header -->


        <div class="table-container p-4">

            <div class="p-3 d-flex justify-content-end">
                <button class="btn btn-primary" onclick="printDiv('myDiv')">Print</button>
            </div>

            <div class="row" id="myDiv">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                <div class="container">
                    <div class="row align-items-center">
                        <!-- Left column: Logo -->
                        <div class="col-3 col-md-3 text-center text-md-left mb-1 mb-md-0">
                            <img src="../img/logo.png" alt="Logo" class="img-fluid" style="max-height: 170px;">
                        </div>

                        <!-- Right column: Invoice info -->
                        <div class="col-9 col-md-9 p-2">
                            <div class="row">
                                <!-- Invoice heading -->
                                <div class="col-12">
                                    <h1 class="display-5 text-md-right text-end">Orders Report</h1>
                                </div>

                                <!-- Invoice month -->
                                <div class="col-12">

                                    <h5 class="mb-0 text-md-right text-end"> From
                                        <?= date('d-M-Y', strtotime($from_date)) ?> to
                                        <?= date('d-M-Y', strtotime($to_date)) ?>
                                    </h5>
                                </div>
                                <div class="col-12 mt-2">
                                    <h5 class="mb-0 text-md-right text-end">Report Date: <?= date('F j, Y') ?></h5>
                                </div>




                            </div>
                        </div>
                    </div>
                </div>


                <div class="row g-3 p-3 mb-4 ">
                    <!-- Billing From -->
                    <div class="col-12 col-sm-6">
                        <div class="card h-100">
                            <div class="card-header ">
                                <h6 class="mb-0 p-0 text-start">To</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-3 text-end"><strong>Name</strong></div>
                                    <div class="col-9 text-start"><?= htmlspecialchars($clientName) ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-3 text-end"><strong>Address</strong></div>
                                    <div class="col-9 text-start"><?= nl2br(htmlspecialchars($clientAddress)) ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-3 text-end"><strong>Phone</strong></div>
                                    <div class="col-9 text-start"><?= htmlspecialchars($clientPhone) ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-3 text-end"><strong>Email</strong></div>
                                    <div class="col-9 text-start"><?= htmlspecialchars($clientEmail) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing To -->
                    <div class="col-12 col-sm-6">
                        <div class="card h-100">
                            <div class="card-header ">
                                <h6 class="mb-0 p-0 text-start ">From</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-3 text-end"><strong>Name</strong></div>
                                    <div class="col-9 text-start">Smart Stockers</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-3 text-end"><strong>Phone</strong></div>
                                    <div class="col-9 text-start">+92 324 2711 265</div>
                                </div>
                                <div class="row">
                                    <div class="col-3 text-end"><strong>Email</strong></div>
                                    <div class="col-9 text-start">smartstockers2@gmail.com</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>





                <div class="table-responsive">
                    <?php 
                     $total_added = 0;
                         $total_shipped = 0;
                         $total_remaining = 0;
                    if (!empty($report)): ?>
                    <table class="table table-bordered align-middle" id="myTable">
                        <thead class=" table-light">
                            <tr>
                                <th>#</th> <!-- New serial number column -->
                                <th>Product Name</th>
                                <th>Product ID</th>
                                <th>Stock Added</th>
                                <th>Shipped</th>
                                <th>Remaining</th>
                                <th>Shipped (Qty - Order ID)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                         $i = 1; 
                         $total_added = 0;
                         $total_shipped = 0;
                         $total_remaining = 0;
                                
                         foreach ($report as $row): 
                             $added = $row['total_stock_added'] ?? 0;
                             $shipped = $row['total_shipped'] ?? 0;
                             $remaining = $row['stock_remaining'] ?? 0;
                            
                             $total_added += $added;
                             $total_shipped += $shipped;
                             $total_remaining += $remaining;
                     ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><?= $row['product_id'] ?></td>
                                <td><?= $added ?></td>
                                <td><?= $shipped ?></td>
                                <td><?= $remaining ?></td>
                                <td><?= $row['order_ids'] ?? '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>




                    </table>
                    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                    <p><strong>No records found for the selected date range.</strong></p>
                    <?php endif; ?>
                </div>
                <div class="col-lg-12">
                    <div class="card mb-4">

                        <div class="card-header  ">
                            <h6 class="mb-0 p-0  text-start "> Report Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-4 text-start text-md-end"><strong>Report Date:</strong></div>
                                <div class="col-8 text-start">
                                    From <?= date('d-M-Y', strtotime($from_date)) ?> to
                                    <?= date('d-M-Y', strtotime($to_date)) ?>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4  text-start text-md-end"><strong>Total Orders:</strong></div>
                                <div class="col-8  text-start"> <?= $total_orders ?>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4  text-start text-md-end"><strong>Total Products:</strong>
                                </div>
                                <div class="col-8  text-start"><?= count($report) ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4  text-start text-md-end"><strong>Total Stock Added:</strong>
                                </div>
                                <div class="col-8  text-start"><?= $total_added ?></div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-4  text-start text-md-end"><strong>Total Shipped:</strong>
                                </div>
                                <div class="col-8  text-start"><?= $total_shipped ?></div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-4  text-start text-md-end"><strong>Total Stock Remaining :</strong>
                                </div>
                                <div class="col-8  text-start"><?= $total_remaining ?></div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row mb-0">
                                <div class="text-center"><strong>This is a system-generated
                                        invoice and does not require a signature.</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="p-3 d-flex justify-content-end">
                <button class="btn btn-primary" onclick="printDiv('myDiv')">Print</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>