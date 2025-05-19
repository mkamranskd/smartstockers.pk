<?php 
require_once 'includes/header.php'; 
require_once '../php_action/db_connect.php'; 
// Get logged-in user's client_id
$user_id = intval($_SESSION['userId']);
$sql = "SELECT client_id FROM users WHERE user_id = {$user_id}";
$query = $connect->query($sql);
$result = $query->fetch_assoc();
$client_id = $result['client_id']; // Store client_id
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let clientId = "<?php echo $client_id; ?>"; // Get client_id from PHP
    if (clientId) {
        fetchProductsByclient(clientId); // Auto-load products on page load
    }
});

function fetchProductsByclient(client_id) {
    if (client_id) {
        fetch('get_all_vendor_products.php?client_id=' + client_id)
            .then(response => response.json())
            .then(data => {
                let tableBody = document.getElementById("productTableBody");
                tableBody.innerHTML = "";
                if (data.length > 0) {
                    data.forEach(product => {
                        let row = `<tr>
                        <td>${product.product_id}</td>
                        <td>${product.product_name}</td>
                        <td>${product.price}</td>
                        <td>${product.weight}</td>
                        <td>${product.stock_quantity}</td>
                        <td>
                            <button class="btn btn-primary btn-sm">
                                <i class="fa fa-eye"></i>
                            </button>
                        </td>
                        </tr>`;
                        tableBody.innerHTML += row;
                    });
                } else {
                    tableBody.innerHTML = "<tr><td colspan='6' class='text-center'>No products found</td></tr>";
                }
            })
            .catch(error => console.error("Error fetching products:", error));
    } else {
        document.getElementById("productTableBody").innerHTML = "";
    }
}
</script>


<div class="row">
    <!-- View Products Section -->
    <div class="col-lg-12">
        <div class="bg-light rounded h-100 p-4 w-100 mb-4">
            <h5 class="mb-4"><i class="fa fa-list"></i> My Products</h5>

            <!-- Scrollable Table Wrapper -->
            <div style="overflow-x: auto; overflow-y: auto; ">
                <table class="table table-hover table-bordered">
                    <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Weight</th>
                            <th>Stock Quantity</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <tr>
                            <td colspan="6" class="text-center">Loading products...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


<?php require_once 'includes/footer.php'; ?>
<?php 
require_once 'includes/header.php'; 
require_once '../php_action/db_connect.php'; 
// Get logged-in user's client_id
$user_id = intval($_SESSION['userId']);
$sql = "SELECT client_id FROM users WHERE user_id = {$user_id}";
$query = $connect->query($sql);
$result = $query->fetch_assoc();
$client_id = $result['client_id']; // Store client_id
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let clientId = "<?php echo $client_id; ?>"; // Get client_id from PHP
    if (clientId) {
        fetchProductsByclient(clientId); // Auto-load products on page load
    }
});

function fetchProductsByclient(client_id) {
    if (client_id) {
        fetch('get_all_vendor_products.php?client_id=' + client_id)
            .then(response => response.json())
            .then(data => {
                let tableBody = document.getElementById("productTableBody");
                tableBody.innerHTML = "";
                if (data.length > 0) {
                    data.forEach(product => {
                        let row = `<tr>
                        <td>${product.product_id}</td>
                        <td>${product.product_name}</td>
                        <td>${product.price}</td>
                        <td>${product.weight}</td>
                        <td>${product.stock_quantity}</td>
                       
                        </tr>`;
                        tableBody.innerHTML += row;
                    });
                } else {
                    tableBody.innerHTML = "<tr><td colspan='6' class='text-center'>No products found</td></tr>";
                }
            })
            .catch(error => console.error("Error fetching products:", error));
    } else {
        document.getElementById("productTableBody").innerHTML = "";
    }
}
</script>


<?php require_once 'includes/footer.php'; ?>