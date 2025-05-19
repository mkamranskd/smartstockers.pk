<?php 
require_once 'includes/header.php'; 
require_once '../php_action/db_connect.php'; ?>


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

                                Local Orders

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

            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="filter-tabs d-flex flex-wrap gap-2">
                    <h5 class="mb-0 d-flex align-items-center">
                        Create New Order
                    </h5>
                </div>
            </div>
            <br>
            <div class="row">
                <form class="col-12 d-flex flex-wrap gap-3 align-content-start" action="" method="POST">
                    <?php
                    $fields = [
                        "customer_name" => "Customer Name",
                        "customer_phone" => "Customer Phone",
                        "parcel_description" => "Description ",
                        "receiver_name" => "Receiver Name",
                        "receiver_phone" => "Receiver Phone", // New field
                        "receiver_address" => "Receiver Address",
                        "delivery_charges" => "COD"
                      ];
                      
                    foreach ($fields as $name => $placeholder) {
                      echo "<div class='col-md-3'><input type='text' name='$name' class='form-control' placeholder='$placeholder' required></div>";
                    }
                  ?>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="0">New Order Placed</option>
                            <option value="3">Shipped</option>
                            <option value="4">Delivered</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="w-100 btn btn-primary" name="submit" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Clients Table Column -->
<div class="col-lg-12">
    <div class="bg-light rounded h-100 p-4">
        <div class="table-container p-4">
            <!-- Filter Tabs & Search Bar (Row 1) -->
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="filter-tabs d-flex flex-wrap gap-2">
                    <h5 class="mb-0 d-flex align-items-center">
                        Order List
                    </h5>
                </div>
                <div class="search-bar">
                    <input type="text" class="form-control w-auto" id="tableSearch" placeholder="Search"
                        style="min-width: 400px;">
                    <!-- Reload Button -->
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>

            <br>

            <!-- Table -->
            <div class="table-responsive" style="height: 380px; overflow-y: auto; overflow-x: auto;">
                <table class="table mb-0 table table-hover" id="clientTable">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Parcel</th>
                            <th>Receiver</th>
                            <th>Receiver Phone</th>
                            <th>Address</th>
                            <th>Charges</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="vendor-table-body">
                        <?php
$statuses = ["New Order Placed", "Confirmed", "Ready to Shipped", "Shipped", "Delivered", "Pending", "Cancelled"];
$result = $connect->query("SELECT * FROM parcels ORDER BY id DESC");
$counter = 1; // Initialize counter
while($row = $result->fetch_assoc()) {
    echo "<tr>
    <td>{$counter}</td>
    <td>{$row['custom_order_id']}</td>
    <td>{$row['customer_name']}</td>
    <td>{$row['customer_phone']}</td>
    <td>{$row['parcel_description']}</td>
    <td>{$row['receiver_name']}</td>
    <td>{$row['receiver_phone']}</td>
    <td>{$row['receiver_address']}</td>
    <td>{$row['delivery_charges']}</td>
    <td>{$statuses[$row['status']]}</td>
    <td>{$row['created_at']}</td>
    <td>
    <div class='btn-group'>
    <a href='#' class='btn btn-info btn-sm' onclick='showAirwayBill(".json_encode($row).")'>Airway</a>
    <a href='?action=update&id={$row['id']}&status=3' class='btn btn-warning btn-sm' onclick='return confirm(\"Mark as Shipped?\")'>Shipped</a>
    <a href='?action=update&id={$row['id']}&status=4' class='btn btn-success btn-sm' onclick='return confirm(\"Mark as Delivered?\")'>Delivered</a>
    
    <button type='button' class='btn btn-secondary btn-sm dropdown-toggle dropdown-toggle-split' data-bs-toggle='dropdown' aria-expanded='false'>
        <span class='visually-hidden'>Toggle Dropdown</span>
    </button>
    <ul class='dropdown-menu'>
        <li><a class='dropdown-item text-danger' href='?action=update&id={$row['id']}&status=6' onclick='return confirm(\"Cancel this order?\")'><i class='fa fa-times' aria-hidden='true'></i> &nbsp;&nbsp;&nbsp;Cancel</a></li>
        <li><a class='dropdown-item text-danger' href='?action=delete&id={$row['id']}' onclick='return confirm(\"Delete this order?\")'><i class='fa fa-trash' aria-hidden='true'></i> &nbsp;&nbsp;&nbspDelete</a></li>
    </ul>
</div>

    </td>
  </tr>";
  
  $counter++; // Increment counter
}
?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Airway Bill Modal -->
<div class="modal fade" id="airwayBillModal" tabindex="-1" aria-labelledby="airwayBillLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="printableArea">
            <div class="modal-header">
                <h5 class="modal-title" id="printModalLabel">Print Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body " id="airwayContent">
                <div class="d-flex align-items-center justify-content-center">
                    <img src="../img/logo.png" alt="Logo" style="max-height: 120px;">
                </div>

                <table class="table table-bordered">
                    <tbody id="airwayDetails">
                        <!-- Filled dynamically -->
                    </tbody>
                </table>

            </div>
            <div class="text-end p-3">
                <button class="btn btn-primary" onclick="printAirwayBill()">Print</button>
                <button type="button" class=" btn btn-danger" data-bs-dismiss="modal" aria-label="Close">Close
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Actions (delete/update)
if (isset($_GET['action']) && isset($_GET['id'])) {
  $id = $_GET['id'];
  if ($_GET['action'] == 'delete') {
    $connect->query("DELETE FROM parcels WHERE id = $id");
  } elseif ($_GET['action'] == 'update' && isset($_GET['status'])) {
    $status = $_GET['status'];
    $connect->query("UPDATE parcels SET status = $status WHERE id = $id");
  }
  echo "<script>location.href='localOrder.php'</script>";
}
?>
<?php require_once 'includes/footer.php'; ?>
<?php
if (isset($_POST['submit'])) {
    // Generate custom_order_id
    $result = $connect->query("SELECT MAX(custom_order_id) AS max_id FROM parcels");
    $row = $result->fetch_assoc();
    $new_custom_id = ($row['max_id'] ?? 1199) + 1;
  
    $stmt = $connect->prepare("
      INSERT INTO parcels (
        customer_name, customer_phone, parcel_description,
        receiver_name, receiver_phone, receiver_address,
        delivery_charges, status, custom_order_id
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssssii",
      $_POST['customer_name'],
      $_POST['customer_phone'],
      $_POST['parcel_description'],
      $_POST['receiver_name'],
      $_POST['receiver_phone'],
      $_POST['receiver_address'],
      $_POST['delivery_charges'],
      $_POST['status'],
      $new_custom_id
    );
    $stmt->execute();
    echo "<script>location.href='localOrder.php'</script>";
  }
  
  ?>


<script>
document.getElementById("tableSearch").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#vendor-table-body tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
});

function showAirwayBill(data) {
    const content = `
   <tr>
    <th >Order ID</th>
    <td colspan="3">${data.custom_order_id}</td>
</tr>
<tr>
    <th>Sender Name</th>
    <td>${data.customer_name}</td>
    <th>Sender Phone</th>
    <td>${data.customer_phone}</td>
</tr>

<tr>
    <th>Description</th>
    <td colspan="3">${data.parcel_description}</td>
</tr>
<tr>
    <th>Receiver Name</th>
    <td>${data.receiver_name}</td>
    <th>Receiver Phone</th>
    <td>${data.receiver_phone}</td>
</tr>

<tr>
    <th>Receiver Address</th>
    <td colspan="3">${data.receiver_address}</td>
</tr>
<tr>
    <th>Delivery Charges</th>
    <td>${data.delivery_charges}</td>
    <th>Order Date</th>
    <td>${data.created_at}</td>
</tr>
  `;
    document.getElementById('airwayDetails').innerHTML = content;
    new bootstrap.Modal(document.getElementById('airwayBillModal')).show();
}

function getStatusName(code) {
    const statuses = ["New Order Placed", "Confirmed", "Ready to Shipped", "Shipped", "Delivered", "Pending",
        "Cancelled"
    ];
    return statuses[code] ?? "Unknown";
}

function printAirwayBill() {
    const content = document.getElementById('airwayContent').innerHTML;
    const frame = window.open('', '', 'height=700,width=900');
    frame.document.write(`<html><head><title>Airway Bill</title>
    <style>
      body { font-family: Arial, sans-serif; padding: 20px; }
      table { width: 100%; border-collapse: collapse; }
      th, td { padding: 10px; border: 1px solid #ccc; }
      .text-center { text-align: center; }
    </style>
  </head><body>${content}</body></html>`);
    frame.document.close();
    frame.focus();
    setTimeout(() => {
        frame.print();
        frame.close();
    }, 500);
}
</script>