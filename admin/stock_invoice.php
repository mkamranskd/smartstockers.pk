<?php
require_once '../php_action/db_connect.php';
require_once 'includes/header.php';

// Initialize
$stocks = [];
$rateOfProduct = 0;
$totalRate = 0;
$fromDate = '';
$toDate = '';
$clientId = '';
$monthOptions = [];

// Generate month options from stock table
$dateQuery = mysqli_query($connect, "
    SELECT MIN(added_at) AS min_date, MAX(added_at) AS max_date FROM stock_logs
");

if ($row = mysqli_fetch_assoc($dateQuery)) {
    $start = new DateTime($row['min_date']);
    $end = new DateTime($row['max_date']);
    $end->modify('first day of next month');

    while ($start < $end) {
        $value = $start->format('Y-m');
        $label = $start->format('F Y');
        $selected = (isset($_POST['select_month']) && $_POST['select_month'] === $value) ? 'selected' : '';
        $monthOptions[] = "<option value=\"$value\" $selected>$label</option>";
        $start->modify('+1 month');
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clientId = $_POST['client_id'];
    $selectedMonth = $_POST['select_month'];
    $fromDate = $selectedMonth . "-01";
    $toDate = date("Y-m-t", strtotime($fromDate));

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

    $stmt = $connect->prepare("
    SELECT sl.*, p.product_name 
    FROM stock_logs sl
    JOIN products p ON sl.product_id = p.product_id AND sl.client_id = p.client_id
    WHERE sl.client_id = ? AND sl.added_at BETWEEN ? AND ?
    ORDER BY sl.added_at
");

    $stmt->bind_param("sss", $clientId, $fromDate, $toDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $stocks = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Assuming total quantity * rate
    $totalQty = array_sum(array_column($stocks, 'quantity'));
    $totalRate = $totalQty * $rateOfProduct;
}
?>

<!-- Breadcrumb & Header -->
<div class="col-lg-12">
    <div class="bg-light rounded h-100 d-flex align-items-center p-4 pb-0">
        <div class="table-container w-100 p-2" style="zoom:0.9;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">&nbsp;&nbsp;&nbsp;&nbsp;<i
                                class="bi bi-house"></i></a></li>
                    <li class="breadcrumb-item"><a href="invoices.php">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="filter-tabs d-flex flex-wrap gap-2">Stock Invoice Report</div>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>


<!-- Filter Form -->
<div class="col-lg-12">
    <div class="bg-light rounded h-100 p-4">
        <div class="table-container p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="filter-tabs d-flex flex-wrap gap-2">
                    <h5 class="mb-0 d-flex align-items-center">
                        Stock Invoice Report
                    </h5>
                </div>
            </div>
            <div class="row">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label for="client_id" class="form-label">Select Client</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">-- Select Client --</option>
                            <?php
                    $clients = mysqli_query($connect, "SELECT client_id, vendor_name FROM clients");
                    while ($client = mysqli_fetch_assoc($clients)) {
                        $selected = isset($clientId) && $clientId == $client['client_id'] ? 'selected' : '';
                        echo "<option value='{$client['client_id']}' $selected>{$client['vendor_name']}</option>";
                    }
                    ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="select_month" class="form-label">Select Month</label>
                        <select name="select_month" class="form-select" required>
                            <option value="">-- Select Month --</option>
                            <?= implode('', $monthOptions) ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- INVOICE -->
<?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
<div class="col-lg-12" id="invoiceRoww">
    <div class="bg-light rounded h-100 p-4">
        <div class="table-container p-4">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary" onclick="printDiv('myDiv')">Print</button>
            </div>

            <div id="myDiv">
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
                                    <h1 class="display-5 text-md-right text-end">STOCK REPORT</h1>
                                </div>

                                <!-- Invoice month -->
                                <div class="col-12">

                                    <h5 class="mb-0 text-md-right text-end"> Report Month:
                                        <?= htmlspecialchars(date('F Y', strtotime($fromDate))) ?>
                                    </h5>
                                </div>
                                <div class="col-12 mt-2">
                                    <h5 class="mb-0 text-md-right text-end">Date: <?= date('F j, Y') ?></h5>
                                </div>




                            </div>
                        </div>
                    </div>
                </div>

                <!-- BILLING INFO -->
                <div class="row g-3 p-3 mb-4 mt-3">
                    <!-- Billing From -->
                    <div class="col-12 col-sm-6">
                        <div class="card h-100">
                            <div class="card-header  ">
                                <h6 class="mb-0 p-0 text-start ">To</h6>
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
                            <div class="card-header  ">
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

                <!-- STOCK TABLE -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>

                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Stock Date</th>
                                <th>Quantity</th>


                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($stocks)): 
                            $index = 1;
                            $totalQty = 0;
                            foreach ($stocks as $stock):
                                $subtotal = $stock['quantity_added'] * $rateOfProduct;
                                $totalQty += $stock['quantity_added'];
                        ?>
                            <tr>
                                <td><?= $index++ ?></td>

                                <td><?= htmlspecialchars($stock['product_id']) ?></td>
                                <td><?= htmlspecialchars($stock['product_name']) ?></td>
                                <td><?= date('d - F - Y', strtotime($stock['added_at'])) ?></td>
                                <td><?= htmlspecialchars($stock['quantity_added']) ?></td>


                            </tr>
                            <?php endforeach; ?>
                            <tr style="border-top: 3px solid gray;">
                                <td colspan="4" class="text-end">Total</td>
                                <td><?= $totalQty ?></td>
                            </tr>

                            <?php else: ?>

                            <tr>
                                <td colspan="5" class="text-center text-muted">No stock records found for the selected
                                    month.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-12">
                    <div class="card mb-4">

                        <div class="card-header   ">
                            <h6 class="mb-0 p-0 text-start ">Report Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-4 text-start text-md-end"><strong>Report Month:</strong></div>
                                <div class="col-8 text-start">
                                    <?= htmlspecialchars(date('F Y', strtotime($fromDate))) ?>
                                </div>

                            </div>


                            <div class="row mb-2">
                                <div class="col-4  text-start text-md-end"><strong>Total Quantity:</strong></div>
                                <div class="col-8  text-start"><?= $totalQty ?></div>
                            </div>

                        </div>
                    </div>
                </div>


                <!-- FOOTER -->
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
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-primary" onclick="printDiv('myDiv')">Print</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>