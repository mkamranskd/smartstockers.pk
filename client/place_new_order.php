<?php 
    require_once '../php_action/db_connect.php';
    require_once 'includes/header.php';

    // Fetch all clients
    $user_id = $_SESSION['userId'];
    $clients = [];

$user_id = $_SESSION['userId'];
$sql = "SELECT client_id FROM users WHERE user_id = {$user_id}";
$result = $connect->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $client_id = $row['client_id'];
} else {
    echo "Client ID not found.";
}


    
    $result = $connect->query("SELECT client_id, vendor_name FROM clients where client_id = '$client_id'");
    if ($result && $row = $result->fetch_assoc()) {
        $clients[] = $row;
    } else {
        echo "Client ID not found.";
    }
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }

    // Get the max order_id and set new one
    $order_id = 1;
    $result = $connect->query("SELECT MAX(order_id) AS max_id FROM orders");
    if ($row = $result->fetch_assoc()) {
        $order_id = $row['max_id'] + 1;
    }

    // Get today's date
    $order_date = date("Y-m-d H:i:s");
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

                                Add New Order Manually

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
                        Add New Order Manually
                    </h5>
                </div>
            </div>
            <br>




            <div class="row">
                <form method="POST" id="order_form">

                    <div class="row mb-3">
                        <div class="col-12 col-md-2 d-flex align-items-center text-md-start text-lg-end">
                            <label for="field1" class="form-label mb-0 w-100"><strong>Order ID</strong></label>
                        </div>
                        <div class="col-12 col-md-4">

                            <input type="text" name="order_id" id="order_id" class="form-control"
                                value="<?= $order_id ?>" readonly>
                        </div>


                        <div class="col-12 col-md-2 d-flex align-items-center text-md-start text-lg-end">
                            <label for="field2" class="form-label mb-0 w-100"><strong>Order Date</strong></label>
                        </div>
                        <div class="col-12 col-md-4">
                            <input type=" text" name="order_date" class="form-control" value="<?= $order_date ?>"
                                readonly>
                        </div>
                    </div>



                    <div class="row mb-3 align-items-center">
                        <label class="col-12 col-md-2 text-md-start text-lg-end col-form-label"><strong>Customer
                                Name</strong></label>
                        <div class="col-12 col-md-4">
                            <input type="text" name="customer_name" class="form-control"
                                placeholder="Enter Customer Name" required>
                        </div>
                        <label class="col-12 col-md-2 text-md-start text-lg-end col-form-label"><strong>Email
                                Address</strong></label>
                        <div class="col-12 col-md-4">
                            <input type="email" name="customer_email" class="form-control" placeholder="Enter Email"
                                required>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <label class="col-12 col-md-2 text-md-start text-lg-end col-form-label"><strong>Phone
                                Number</strong></label>
                        <div class="col-12 col-md-4">
                            <input type="text" name="customer_phone" class="form-control" placeholder="Enter Phone No"
                                required>
                        </div>
                        <label
                            class="col-12 col-md-2 text-md-start text-lg-end col-form-label"><strong>Address</strong></label>
                        <div class="col-12 col-md-4">
                            <input type="text" name="customer_address" class="form-control" placeholder="Enter Address"
                                required>
                        </div>
                    </div>


                    <div class="row mb-3 align-items-center">
                        <label
                            class="col-12 col-md-2 text-md-start text-lg-end col-form-label"><strong>City</strong></label>
                        <div class="col-12 col-md-4">
                            <input type="text" name="city" class="form-control" placeholder="Enter City" required>
                        </div>

                        <label class="col-12 col-md-2 text-md-start text-lg-end col-form-label"><strong>Select
                                Client</strong></label>
                        <div class="col-12 col-md-4">
                            <select name="client_id" id="client_id" class="form-select" required>
                                <option value="">Select Client</option>
                                <?php foreach ($clients as $client): ?>
                                <option value="<?= htmlspecialchars($client['client_id']) ?>">
                                    <?= htmlspecialchars($client['vendor_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                    <!-- Client Selection -->


                    <!-- Product Selection -->
                    <div class="row mb-3 align-items-center">
                        <label
                            class="col-12 col-md-2 text-md-start text-lg-end col-form-label"><strong>Products</strong></label>
                        <div class="col-12 col-md-10">
                            <div id="order_products_section"></div>
                            <button type="button" id="add_product_button" class="btn btn-primary mt-1">
                                <i class="fa fa-plus"></i> Add Product
                            </button>
                        </div>
                    </div>

                    <!-- Delivery Charges -->
                    <div class="row mb-3 align-items-center">
                        <label class="col-12 col-md-2 text-md-start text-lg-end col-form-label"><strong>Delivery
                                Charges</strong></label>
                        <div class="col-12 col-md-4">
                            <input type="number" name="delivery_charges" id="delivery_charges" class="form-control"
                                value="">
                        </div>
                        <label class="col-12 col-md-2 text-md-start text-lg-end col-form-label"><strong>Total COD
                                Amount</strong></label>
                        <div class="col-12 col-md-4">
                            <input type="text" id="total_cod" name="total_cod" class="form-control" value="0" readonly>
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div class="row">
                        <div class="col-12 col-md-8 offset-md-2">
                            <button type="button" id="previewOrderButton" class="btn btn-success">
                                <i class="fa fa-check"></i> Place Order
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="place_order" value="1">

                </form>
            </div>
        </div>
    </div>
</div>




<script>
function recalculateTotalCOD() {
    let total = 0;
    const prices = document.querySelectorAll('input[name="price[]"]');
    const quantities = document.querySelectorAll('input[name="quantity[]"]');
    const deliveryCharges = parseFloat(document.getElementById('delivery_charges').value) || 0;

    prices.forEach((priceInput, index) => {
        const price = parseFloat(priceInput.value) || 0;
        const qty = parseInt(quantities[index].value) || 0;
        total += price * qty;
    });

    total += deliveryCharges;

    document.getElementById('total_cod').value = total.toFixed(2);
}

// Attach event listeners
document.querySelectorAll('input[name="price[]"], input[name="quantity[]"]').forEach(input => {
    input.addEventListener('input', recalculateTotalCOD);
});

document.getElementById('delivery_charges').addEventListener('input', recalculateTotalCOD);
</script>

<!-- Bootstrap Modal -->
<div class="modal fade" id="orderConfirmationModal" tabindex="-1" aria-labelledby="orderConfirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderConfirmationModalLabel">Confirm Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" style="border: 2px solid black; width: 100%; text-align: left;">
                    <thead>
                        <tr>
                            <th colspan="8">
                                <img src="../img/logo.png" alt="Logo" style="max-width: 100px;">
                                <span style="text-align: center; font-size: 24px; font-weight: bold;">Smart
                                    Stockers</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Order ID</th>
                            <td colspan="1">
                                <span style="text-align:center; font-family: 'Libre Barcode 39'; font-size: 22px;"
                                    id="confirmOrderIdBarcode"></span><br>
                                <span id="confirmOrderId"></span>
                            </td>
                            <th>Tracking ID</th>
                            <td colspan="2" id="confirmTrackingId"></td>
                        </tr>
                        <tr>
                            <th>Customer Name</th>
                            <td colspan="5" id="confirmCustomerName"></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td colspan="5" id="confirmCustomerPhone"></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td colspan="5" id="confirmAddress"></td>
                        </tr>
                        <tr>
                            <th>Products</th>
                            <td colspan="5" id="confirmProductDetails"></td>
                        </tr>
                        <tr>
                            <th>COD</th>
                            <td id="confirmCOD"></td>
                            <th>Quantity</th>
                            <td id="confirmQuantity"></td>
                        </tr>
                        <tr>
                            <th>Total Weight</th>
                            <td id="confirmTotalWeight"></td>
                            <th>Status</th>
                            <td colspan="3" id="confirmStatus"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmOrderButton">Confirm Order</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to Populate Modal with Order Data -->
<script>
document.getElementById("previewOrderButton").addEventListener("click", function() {
    let orderId = document.getElementById("order_id").value;
    let customerName = document.querySelector("[name='customer_name']").value;
    let customerPhone = document.querySelector("[name='customer_phone']").value;
    let customerAddress = document.querySelector("[name='customer_address']").value;
    let address = document.querySelector("[name='city']").value;
    let cod = document.getElementById("total_cod").value;

    let productDetails = "";
    let quantity = 0;
    let totalWeight = 0; // You can replace this with actual weight logic if available

    document.querySelectorAll(".product_select").forEach((select, index) => {
        let productName = select.options[select.selectedIndex].text;
        let productQuantity = document.querySelectorAll(".product_quantity")[index].value;
        let productPrice = document.querySelectorAll(".product_price")[index].value;

        productDetails +=
            `<strong>${productName}</strong> - Qty: ${productQuantity}, Price: ${productPrice} <br>`;
        quantity += parseInt(productQuantity);
    });

    if (!customerName || !customerPhone || !customerAddress || quantity === 0) {
        alert("Please fill all required fields and add at least one product.");
        return;
    }

    // Populate the modal with order details
    document.getElementById('confirmOrderId').innerText = orderId;
    document.getElementById('confirmOrderIdBarcode').innerText = orderId;
    document.getElementById('confirmTrackingId').innerText = orderId;
    document.getElementById('confirmCustomerName').innerText = customerName;
    document.getElementById('confirmCustomerPhone').innerText = customerPhone;
    document.getElementById('confirmAddress').innerText = customerAddress;
    document.getElementById('confirmProductDetails').innerHTML = productDetails;
    document.getElementById('confirmCOD').innerText = cod;
    document.getElementById('confirmQuantity').innerText = quantity;
    document.getElementById('confirmTotalWeight').innerText = totalWeight;
    document.getElementById('confirmStatus').innerText = "Pending";

    // Show the modal
    let orderModal = new bootstrap.Modal(document.getElementById('orderConfirmationModal'));
    orderModal.show();
});

// When the confirm button is clicked, submit the form
document.getElementById("confirmOrderButton").addEventListener("click", function() {
    document.getElementById("order_form").submit();
});



// Handle Order Confirmation
document.getElementById('confirmOrderButton').addEventListener('click', function() {

    var orderModal = bootstrap.Modal.getInstance(document.getElementById('orderConfirmationModal'));
    orderModal.hide();
});

document.addEventListener("DOMContentLoaded", function() {
    let selectedProducts = new Set();

    document.getElementById("add_product_button").addEventListener("click", function() {
        const clientId = document.getElementById("client_id").value;
        if (!clientId) {
            alert("Please select a client first!");
            return;
        }
        fetch(`get_products.php?client_id=${clientId}`)
            .then(response => response.json())
            .then(products => {
                let availableProducts = products.filter(p => !selectedProducts.has(p
                    .product_id));

                if (availableProducts.length === 0) {
                    alert("No more products available for selection.");
                    return;
                }

                const section = document.getElementById("order_products_section");
                const row = document.createElement("div");
                row.classList.add("row", "mb-3");

                row.innerHTML = `
                        <div class="col-md-4">
                                            <select class="form-select product_select" required name="product_id[]">
                                                <option value="">Select Product</option>
                                                ${availableProducts.map(product => `<option value="${product.product_id}"
                                                    data-name="${product.product_name}" data-price="${product.price}">
                                                    ${product.product_name}</option>`).join('')}
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="form-control product_quantity" placeholder="Qty"
                                                min="1" value="1" name="quantity[]" required>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control product_price" name="price[]"
                                                placeholder="Price" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control product_total" name="subtotal[]"
                                                placeholder="Total" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger remove_product w-100">X</button>
                                        </div>
                        <input type="hidden" name="product_name[]" class="product_name">
                    `;

                section.appendChild(row);

                const select = row.querySelector(".product_select");
                const quantity = row.querySelector(".product_quantity");
                const price = row.querySelector(".product_price");
                const total = row.querySelector(".product_total");
                const hiddenProductName = row.querySelector(".product_name");

                select.addEventListener("change", function() {
                    let selectedOption = select.options[select.selectedIndex];
                    let productId = selectedOption.value;
                    let productPrice = selectedOption.dataset.price;
                    let productName = selectedOption.dataset.name;

                    if (selectedProducts.has(productId)) {
                        alert("This product is already added!");
                        row.remove();
                        return;
                    }

                    selectedProducts.add(productId);
                    price.value = productPrice;
                    total.value = productPrice * quantity.value;
                    hiddenProductName.value = productName;
                    updateTotalCOD();
                });

                quantity.addEventListener("input", function() {
                    total.value = price.value * quantity.value;
                    updateTotalCOD();
                });

                row.querySelector(".remove_product").addEventListener("click", function() {
                    selectedProducts.delete(select.value);
                    row.remove();
                    updateTotalCOD();
                });
            });
    });

    function updateTotalCOD() {
        let totalCod = 0;
        document.querySelectorAll(".product_total").forEach(input => {
            totalCod += parseFloat(input.value) || 0;
        });
        document.getElementById("total_cod").value = totalCod;
    }
});
</script>

<?php
    if (isset($_POST['place_order'])) {
        $client_id = mysqli_real_escape_string($connect, $_POST['client_id']);
        $customer_name = mysqli_real_escape_string($connect, $_POST['customer_name']);
        $customer_email = mysqli_real_escape_string($connect, $_POST['customer_email']);
        $customer_phone = mysqli_real_escape_string($connect, $_POST['customer_phone']);
        $customer_address = mysqli_real_escape_string($connect, $_POST['customer_address']);
        $city = mysqli_real_escape_string($connect, $_POST['city']);
        $total_cod = mysqli_real_escape_string($connect, $_POST['total_cod']);
        $order_id = mysqli_real_escape_string($connect, $_POST['order_id']);
        $order_date = date("Y-m-d H:i:s");
        $status = 0;
    

        // Insert order details into orders table
    $query = "INSERT INTO orders (
            order_id, client_id, customer_name, customer_email, customer_phone, 
            delivery_address,billing_address,shipping_address, city, order_date, status, total_amount,source
        ) VALUES (
            '$order_id', '$client_id', '$customer_name', '$customer_email', 
            '$customer_phone', '$customer_address', '$customer_address', '$customer_address', '$city', '$order_date', '$status', '$total_cod','2'
        )";
        
        if ($connect->query($query)) {
            $order_insert_id = $connect->insert_id; // Get the last inserted order ID
        
            if (!empty($_POST['product_id'])) {
                for ($i = 0; $i < count($_POST['product_id']); $i++) {
                    $product_id = mysqli_real_escape_string($connect, $_POST['product_id'][$i]);
                    $product_name = mysqli_real_escape_string($connect, $_POST['product_name'][$i]);
                    $quantity = (int) $_POST['quantity'][$i];
                    $price = (float) $_POST['price'][$i];
                    $subtotal = $quantity * $price;
            
                    $insertItemQuery = "INSERT INTO order_items (
                        order_id, product_id, product_name, quantity, price, subtotal
                    ) VALUES (
                        '$order_id', '$product_id', '$product_name', '$quantity', '$price', '$subtotal'
                    )";
            
                    $connect->query($insertItemQuery);
                }
            }
            
        
            echo "<script>alert('Order placed successfully!'); window.location.href='orders.php';</script>";
        } else {
            die("Error placing order: " . $connect->error);
        }
        
    }

    include 'includes/footer.php';
    ?>
<script>
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>