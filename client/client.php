<?php 
require_once 'includes/header.php'; 
require_once '../php_action/db_connect.php'; 

$user_id = $_SESSION['userId'];
$sql = "SELECT client_id FROM users WHERE user_id = {$user_id}";
$result = $connect->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $client_id = $row['client_id'];
} else {
    echo "Client ID not found.";
}

$result = mysqli_query($connect, "
SELECT
c.client_id,
c.vendor_name,
c.phone_number,
c.cnic,
c.address,
c.rate_of_product,
GROUP_CONCAT(DISTINCT vds.delivery_type SEPARATOR ', ') AS delivery_services,
GROUP_CONCAT(DISTINCT vs.store_type, ': ', vs.store_link, '' SEPARATOR ' , ') AS store_details
FROM clients c
LEFT JOIN vendor_delivery_services vds ON c.client_id = vds.client_id
LEFT JOIN vendor_stores vs ON c.client_id = vs.client_id where c.client_id = '$client_id'
GROUP BY c.client_id
");
?>

<div class="row">
    <!-- Clients Table Column -->
    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="bg-light rounded h-100 p-6 mb-4">
            <div class="table-container">

                <!-- Filter Tabs & Search Bar (Row 1) -->
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="filter-tabs d-flex flex-wrap gap-2">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="bi bi-people me-2"></i> Profile
                        </h5>
                    </div>
                </div>
                <br>

                <!-- Read-Only Form -->
                <div class="table-responsive">
                    <form action="#" method="post" id="viewClientForm" class="mt-3">
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="client_id" id="client_id"
                                value="<?= htmlspecialchars($row['client_id']); ?>" readonly>
                            <label for="client_id">Client ID</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="vendor_name" id="vendor_name"
                                value="<?= htmlspecialchars($row['vendor_name']); ?>" readonly>
                            <label for="vendor_name">Full Name</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="phone_number" id="phone_number"
                                value="<?= htmlspecialchars($row['phone_number']); ?>" readonly>
                            <label for="phone_number">Phone Number</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="cnic" id="cnic"
                                value="<?= htmlspecialchars($row['cnic']); ?>" readonly>
                            <label for="cnic">CNIC</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="address" id="address"
                                value="<?= htmlspecialchars($row['address']); ?>" readonly>
                            <label for="address">Address</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="rate_of_product" id="rate_of_product"
                                value="<?= htmlspecialchars($row['rate_of_product']); ?>" readonly>
                            <label for="rate_of_product">Rate of Product</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input class="form-control" name="delivery_services" id="delivery_services"
                                value="<?= htmlspecialchars($row['delivery_services']); ?>" readonly>
                            <label for="delivery_services">Delivery Services</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input class="form-control" name="store_details" id="store_details"
                                value="<?= htmlspecialchars($row['store_details']); ?>" readonly>
                            <label for="store_details">Store Details</label>
                        </div>

                        <?php endwhile; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once 'includes/footer.php'; ?>