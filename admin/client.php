<?php 
require_once 'includes/header.php'; 
require_once '../php_action/db_connect.php'; 

    // Fetch the max SKU character
    // Fetch the max SKU character from clients (only single uppercase letters)
    $sku_query = mysqli_query($connect, "SELECT client_id FROM clients WHERE client_id REGEXP '^[A-Z]$' ORDER BY client_id DESC LIMIT 1");

    if (!$sku_query) {
        die("Query failed: " . mysqli_error($connect));
    }

    $sku_result = mysqli_fetch_assoc($sku_query);
    $max_sku = $sku_result ? $sku_result['client_id'] : null;

    // Determine the next SKU letter
    if ($max_sku && preg_match('/^[A-Z]$/', $max_sku)) {
        if ($max_sku !== 'Z') {
            $sku_value = chr(ord($max_sku) + 1); // Increment character (A → B, B → C)
        } else {
            die("SKU limit reached! Cannot go beyond 'Z'.");
        }
    } else {
        $sku_value = 'A'; // Default start
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
    LEFT JOIN vendor_stores vs ON c.client_id = vs.client_id
    GROUP BY c.client_id
");


    ?>
<!-- Vendor Section -->

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

                                Clients

                            </div>
                        </div>
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
            <div class="row align-items-center gy-2">
                <!-- Title -->
                <div class="col-12 col-md-4 d-flex align-items-center">
                    <div class="filter-tabs d-flex flex-wrap gap-2">
                        <h5 class="mb-0">Clients</h5>
                    </div>
                </div>

                <!-- Controls -->
                <div class="col-12 col-md-8">
                    <div class="d-flex align-items-center gap-2 flex-nowrap overflow-auto">
                        <!-- Search -->
                        <input type="text" class="form-control" id="tableSearch" placeholder="Search clients..."
                            style="min-width: 200px;">

                        <!-- Reload Button -->
                        <button class="btn btn-primary" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>

                        <!-- Add Button -->
                        <button class="btn btn-primary add-client-btn" data-bs-toggle="modal"
                            data-bs-target="#addClientModal">
                            <i class="bi bi-plus"></i>
                        </button>

                        <!-- Export Button -->
                        <button id="downloadTableDataInExcel"
                            onclick="downloadTableDataInExcel('clientTable','Clients');" class="btn btn-primary">
                            <i class="bi bi-download"></i>
                        </button>
                    </div>
                </div>

            </div>


            <br>

            <!-- Table -->
            <div class="table-responsive" style="height: 350px; overflow-y: auto; overflow-x: auto;">
                <table class="table mb-0 table table-hover" id="clientTable">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>CNIC</th>
                            <th>Address</th>
                            <th>Rate</th>
                            <th>Service</th>
                            <th>Store</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="vendor-table-body">
                        <!-- Rows will be inserted dynamically -->
                    </tbody>
                </table>
            </div>

        </div>


    </div>
</div>


<!-- Bootstrap Modal for Adding Client -->
<div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-user-plus me-2"></i> Add Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <!-- Client ID -->
                    <div class="mb-2 d-flex align-items-center">
                        <label for="vendorID" class="me-2" style="width: 150px; text-align: right;">Client ID</label>
                        <input type="text" class="form-control" id="vendorID" name="client_id" value="<?= $sku_value ?>"
                            readonly>
                    </div>

                    <!-- Vendor Name -->
                    <div class="mb-2 d-flex align-items-center">
                        <label for="vendorName" class="me-2" style="width: 150px; text-align: right;">Client
                            Name</label>
                        <input type="text" class="form-control" id="vendorName" name="vendor_name" required>
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-2 d-flex align-items-center">
                        <label for="phoneNumber" class="me-2" style="width: 150px; text-align: right;">Phone
                            Number</label>
                        <input type="text" class="form-control" id="phoneNumber" name="phone_number" required>
                    </div>

                    <!-- Email -->
                    <div class="mb-2 d-flex align-items-center">
                        <label for="email" class="me-2" style="width: 150px; text-align: right;">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <!-- CNIC -->
                    <div class="mb-2 d-flex align-items-center">
                        <label for="cnic" class="me-2" style="width: 150px; text-align: right;">CNIC</label>
                        <input type="text" class="form-control" id="cnic" name="cnic" required>
                    </div>

                    <!-- Rate of Product -->
                    <div class="mb-2 d-flex align-items-center">
                        <label for="rateProduct" class="me-2" style="width: 150px; text-align: right;">Rate of
                            Product</label>
                        <input type="number" class="form-control" id="rateProduct" name="rate_of_product" required>
                    </div>

                    <!-- Address -->
                    <div class="mb-2 d-flex align-items-center">
                        <label for="address" class="me-2" style="width: 150px; text-align: right;">Address</label>
                        <input type="text" class="form-control" id="address" name="address" required>
                    </div>

                    <!-- Delivery Services -->
                    <hr>
                    <p><strong>Delivery Services Details</strong></p>
                    <div class="delivery-service-group mb-3" id="deliveryServices">
                        <div class="delivery-service-row mb-3">
                            <!-- JS-injected inputs -->
                        </div>
                    </div>
                    <button type="button" class="btn btn-success add-service-btn mb-3" onclick="addDeliveryService()">
                        Add Delivery Service
                    </button>

                    <!-- Store Details -->
                    <hr>
                    <p><strong>Store Details</strong></p>
                    <div class="store-detail-group mb-3" id="storeDetails">
                        <div class="store-detail-row mb-3">
                            <!-- JS-injected inputs -->
                        </div>
                    </div>
                    <button type="button" class="btn btn-success add-store-btn mb-3" onclick="addStore()">
                        Add Store
                    </button>

                    <!-- New User Section -->
                    <hr>
                    <p><strong>New User</strong></p>

                    <!-- Username -->
                    <div class="mb-2 d-flex align-items-center">
                        <label for="username" class="me-2" style="width: 150px; text-align: right;">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>

                    <!-- Password -->
                    <div class="mb-2 d-flex align-items-center">
                        <label for="password" class="me-2" style="width: 150px; text-align: right;">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3 d-flex align-items-center">
                        <label for="confirmPassword" class="me-2" style="width: 150px; text-align: right;">Confirm
                            Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password"
                            required>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" name="add_vendor" class="btn btn-primary">
                            <i class="bi bi-plus me-1"></i> Add Client
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<?php

    if (isset($_POST['add_vendor'])) {
        // Sanitize Input Data
        $name = mysqli_real_escape_string($connect, $_POST['vendor_name']);
        $phone = mysqli_real_escape_string($connect, $_POST['phone_number']);
        $email = mysqli_real_escape_string($connect, $_POST['email']);
        $cnic = mysqli_real_escape_string($connect, $_POST['cnic']);
        $address = mysqli_real_escape_string($connect, $_POST['address']);
        $rate_of_product = mysqli_real_escape_string($connect, $_POST['rate_of_product']);
        $username = mysqli_real_escape_string($connect, $_POST['username']);
        $password = mysqli_real_escape_string($connect, $_POST['password']);
        $confirm_password = mysqli_real_escape_string($connect, $_POST['confirm_password']);

        // Password Check
        if ($password !== $confirm_password) {
            echo "<script>showMessage('Passwords do not match!', 'danger');</script>";
            exit;
        }

        // Secure Password Hashing
        $hashed_password = md5($password);

        // Insert Client Data
        $query = "INSERT INTO clients (client_id, vendor_name, phone_number, email, cnic, address, rate_of_product) 
                VALUES ('$sku_value','$name', '$phone', '$email', '$cnic', '$address', '$rate_of_product')";
        if (mysqli_query($connect, $query)) {
            // Insert User Credentials
            $user_query = "INSERT INTO users (username, password, email, uas, client_id) 
                        VALUES ('$username', '$hashed_password', '$email', 2, '$sku_value')";
            mysqli_query($connect, $user_query);

            // Insert Delivery Services
            if (!empty($_POST['delivery_type']) && !empty($_POST['api_token'])) {
                $delivery_types = $_POST['delivery_type'];
                $api_tokens = $_POST['api_token'];
                for ($i = 0; $i < count($delivery_types); $i++) {
                    $delivery_type = mysqli_real_escape_string($connect, $delivery_types[$i]);
                    $api_token = mysqli_real_escape_string($connect, $api_tokens[$i]);
                    $service_query = "INSERT INTO vendor_delivery_services (client_id, delivery_type, api_token) 
                                    VALUES ('$sku_value', '$delivery_type', '$api_token')";
                    mysqli_query($connect, $service_query);
                }
            }

            // Insert Store Details
            if (!empty($_POST['store_type']) && !empty($_POST['store_link']) && !empty($_POST['consumer_key']) && !empty($_POST['consumer_secret'])) {
                $store_types = $_POST['store_type'];
                $store_links = $_POST['store_link'];
                $consumer_keys = $_POST['consumer_key'];
                $consumer_secrets = $_POST['consumer_secret'];
                $access_tokens = $_POST['access_token'];

                for ($i = 0; $i < count($store_types); $i++) {
                    $store_type = mysqli_real_escape_string($connect, $store_types[$i]);
                    $store_link = mysqli_real_escape_string($connect, $store_links[$i]);
                    $consumer_key = mysqli_real_escape_string($connect, $consumer_keys[$i]);
                    $consumer_secret = mysqli_real_escape_string($connect, $consumer_secrets[$i]);
                    $access_token = mysqli_real_escape_string($connect, $access_tokens[$i]);

                    $store_query = "INSERT INTO vendor_stores (client_id, store_type, store_link, consumer_key, consumer_secret, access_token) 
                                    VALUES ('$sku_value', '$store_type', '$store_link', '$consumer_key', '$consumer_secret', '$access_token')";
                    mysqli_query($connect, $store_query);
                }
            }

            echo "<script>showMessage('Client Added Successfully!', 'danger');</script>";
            echo "<script>window.location = 'client.php';</script>";
        } else {
            echo "<script>showMessage('Error: " . mysqli_error($connect) . "!', 'danger');</script>";
        }
    }
        ?>

<!-- HTML: Client Details Modal with Labels on Left -->
<div class="modal fade" id="clientDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Client Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2 d-flex align-items-center">
                    <label for="editClientclient_id" class="me-2" style="width: 150px; text-align: right;">Client
                        ID</label>
                    <input id="editClientclient_id" class="form-control" readonly>
                </div>
                <div class="mb-2 d-flex align-items-center">
                    <label for="editClientvendor_name" class="me-2" style="width: 150px; text-align: right;">Vendor
                        Name</label>
                    <input id="editClientvendor_name" class="form-control">
                </div>
                <div class="mb-2 d-flex align-items-center">
                    <label for="editClientemail" class="me-2" style="width: 150px; text-align: right;">Email</label>
                    <input id="editClientemail" class="form-control">
                </div>
                <div class="mb-2 d-flex align-items-center">
                    <label for="editClientphone_number" class="me-2" style="width: 150px; text-align: right;">Phone
                        Number</label>
                    <input id="editClientphone_number" class="form-control">
                </div>
                <div class="mb-2 d-flex align-items-center">
                    <label for="editClientcnic" class="me-2" style="width: 150px; text-align: right;">CNIC</label>
                    <input id="editClientcnic" class="form-control">
                </div>
                <div class="mb-2 d-flex align-items-center">
                    <label for="editClientaddress" class="me-2" style="width: 150px; text-align: right;">Address</label>
                    <textarea id="editClientaddress" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-2 d-flex align-items-center">
                    <label for="editClientrate_of_product" class="me-2" style="width: 150px; text-align: right;">Rate of
                        Product</label>
                    <input id="editClientrate_of_product" class="form-control">
                </div>
                <div class="mb-2 d-flex align-items-center">
                    <label for="editClientcreated_at" class="me-2" style="width: 150px; text-align: right;">Created
                        At</label>
                    <input id="editClientcreated_at" class="form-control" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="updateClient()">Update</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal to Update User Details -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailsModalLabel">Update User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="mb-2 d-flex align-items-center">
                    <label for="editUseruserClientID" class="me-2" style="width: 150px; text-align: right;">Client
                        ID</label>
                    <input id="editUseruserClientID" class="form-control" readonly>
                </div>

                <div class="mb-2 d-flex align-items-center">
                    <label for="editUseruserVendorName" class="me-2" style="width: 150px; text-align: right;">Vendor
                        Name</label>
                    <input id="editUseruserVendorName" class="form-control" readonly>
                </div>

                <div class="mb-2 d-flex align-items-center">
                    <label for="editUserusername" class="me-2" style="width: 150px; text-align: right;">Username</label>
                    <input id="editUserusername" class="form-control">
                </div>

                <div class="mb-2 d-flex align-items-center">
                    <label for="editUseremail" class="me-2" style="width: 150px; text-align: right;">Email</label>
                    <input id="editUseremail" class="form-control">
                </div>

                <div class="mb-2 d-flex align-items-center position-relative">
                    <label for="editUserpassword" class="me-2" style="width: 150px; text-align: right;">Password</label>
                    <div class="input-group">
                        <input type="password" id="editUserpassword" class="form-control"
                            placeholder="Leave blank to keep current">
                        <button class="btn btn-outline-secondary" type="button" tabindex="-1"
                            onclick="togglePassword(this)">
                            <i class="bi bi-eye-slash"></i>

                        </button>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="updateUser()">Update User</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal to Update Delivery Service Details -->
<div class="modal fade" id="deliveryServiceModal" tabindex="-1" aria-labelledby="deliveryServiceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveryServiceModalLabel">Update Delivery Service Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-md-2 col-4 d-flex align-items-center">
                            <label for="deliveryServiceClientID"
                                class="form-label text-start   w-100 text-start   w-100">Client
                                ID</label>
                        </div>
                        <div class="col-md-3 col-8 d-flex align-items-center">
                            <input id="editDeliveryServiceClientID" class="form-control" readonly>
                        </div>
                        <div class="col-md-2 col-4 d-flex align-items-center mt-2 mt-md-0">
                            <label for="deliveryServiceVendorName"
                                class="form-label text-start   w-100 text-start   w-100">Client
                                Name</label>
                        </div>
                        <div class="col-md-5 col-8 d-flex align-items-center mt-2 mt-md-0">
                            <input id="editDeliveryServiceVendorName" class="form-control" readonly>
                        </div>
                    </div>

                    <div id="deliveryServiceRows">
                        <!-- Dynamic rows for each delivery service will be added here -->
                    </div>

                    <div class="text-end mt-3">
                        <button class="btn btn-secondary" type="button" onclick="addNewDeliveryServiceRow()">Add New
                            Delivery Service</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="updateDeliveryServices()">Save Changes</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal to Update Vendor Store Details -->
<div class="modal fade" id="vendorStoreModal" tabindex="-1" aria-labelledby="vendorStoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" style="width: 90%; max-width:90%; margin: auto;">

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vendorStoreModalLabel">Update Vendor Store Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-md-2 col-4  col-lg-2 d-flex align-items-center">
                            <label for="clientID" class="form-label text-start   w-100 text-start   w-100">Client
                                ID</label>
                        </div>
                        <div class="col-md-3 col-8 col-lg-3 d-flex align-items-center">
                            <input id="editClientID" class="form-control" readonly>
                        </div>
                        <div class="col-md-2 col-4 col-lg-2 d-flex align-items-center mt-2 mt-md-0">
                            <label for="deliveryServiceVendorName"
                                class="form-label text-start   w-100 text-start   w-100">Client
                                Name</label>
                        </div>
                        <div class="col-md-5 col-8  col-lg-5 d-flex align-items-center mt-2 mt-md-0">
                            <input id="editStoreVendorName" class="form-control" readonly>
                        </div>
                    </div>

                    <div id="vendorStoreRows">
                        <!-- Dynamic rows for each store will be added here -->
                    </div>

                    <div class="text-end mt-3">
                        <button class="btn btn-secondary" type="button" onclick="addNewStoreRow()">Add New
                            Store</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="updateStores()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
async function openVendorStoreModal(clientId) {
    try {
        const res = await fetch(`get_client_data.php?client_id=${encodeURIComponent(clientId)}`);
        const data = await res.json();

        if (data.status === 'success') {
            console.log(data); // Check the response

            document.getElementById('editClientID').value = clientId;
            document.getElementById('editStoreVendorName').value = data.client.vendor_name || '';

            const stores = data.stores || [];
            const vendorStoreRows = document.getElementById('vendorStoreRows');
            vendorStoreRows.innerHTML = ''; // Clear existing rows

            // Check if the stores array contains data
            console.log(stores);

            stores.forEach((store, index) => {
                addStoreRow(store.id, store.store_link, store.store_type, store.consumer_key, store
                    .consumer_secret, store.access_token, store.platform, index);
            });

            const modal = new bootstrap.Modal(document.getElementById('vendorStoreModal'));
            modal.show();
        } else {
            alert('Client not found');
        }
    } catch (error) {
        console.error('Error loading client data:', error);
        alert('Failed to load client data.');
    }
}


function addNewStoreRow() {
    addStoreRow('', '', '', '', '', '', '', document.querySelectorAll('.store-row').length);
}

function addStoreRow(id, storeLink, storeType, consumerKey, consumerSecret, accessToken, platform, index) {
    const row = document.createElement('div');
    row.classList.add('mb-2', 'store-row');
    row.innerHTML = `
    
        <div class="row  bg-light p-2 rounded m-0" style="; border: 2px solid #ccc; ">

    <!-- Store Link -->
    <div class="col-md-2 col-4 col-lg-2 d-flex align-items-center mb-3">
        <label for="storeLink${index}" class="form-label text-start   w-100">Store Link</label>
    </div>
    <div class="col-md-3 col-8 col-lg-3 d-flex align-items-center mb-3mb-3">
        <input id="storeLink${index}" class="form-control" value="${storeLink || ''}">
    </div>

    <!-- Store Type -->
    <div class="col-md-2 col-4 col-lg-2 d-flex align-items-center mb-3">
        <label for="storeType${index}" class="form-label text-start   w-100">Store Type</label>
    </div>
    <div class="col-md-3 col-8 col-lg-5 d-flex align-items-center mb-3">
        <select id="storeType${index}" class="form-select">
            <option value="WooCommerce" ${storeType === 'WooCommerce' ? 'selected' : ''}>WooCommerce</option>
            <option value="Shopify" ${storeType === 'Shopify' ? 'selected' : ''}>Shopify</option>
            <option value="Local" ${storeType === 'Local' ? 'selected' : ''}>Local</option>
        </select>
    </div>

    <!-- Consumer Key -->
    <div class="col-md-2 col-4 col-lg-2 d-flex align-items-center mb-3">
        <label for="consumerKey${index}" class="form-label text-start   w-100">Consumer Key</label>
    </div>
    <div class="col-md-3 col-8 col-lg-3 d-flex align-items-center mb-3">
        <input id="consumerKey${index}" class="form-control" value="${consumerKey || ''}">
    </div>

    <!-- Consumer Secret -->
    <div class="col-md-2 col-4 col-lg-2 d-flex align-items-center mb-3">
        <label for="consumerSecret${index}" class="form-label text-start   w-100">Consumer Secret</label>
    </div>
    <div class="col-md-3 col-8 col-lg-5 d-flex align-items-center mb-3">
        <input id="consumerSecret${index}" class="form-control" value="${consumerSecret || ''}">
    </div>

    <!-- Access Token -->
    <div class="col-md-2 col-4 col-lg-2 d-flex align-items-center mb-3">
        <label for="accessToken${index}" class="form-label text-start   w-100">Access Token (Optional)</label>
    </div>
    <div class="col-md-3 col-8 col-lg-3 d-flex align-items-center  mb-3">
        <input id="accessToken${index}" class="form-control" value="${accessToken || ''}">
    </div>

    <!-- Remove Button -->
     <div class="col-md-2 col-4 col-lg-2 d-flex align-items-center mb-3">
        
    </div>
    <div class="col-md-3 col-8 col-lg-2 d-flex align-items-center mb-3">
         <button type="button" class="btn btn-danger w-100" onclick="removeStoreRow(this)">
            <i class="fa fa-times" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Remove Store
        </button>
    </div>
    </div>

    `;
    document.getElementById('vendorStoreRows').appendChild(row);
}



function removeStoreRow(button) {
    const row = button.closest('.row');
    if (row) row.remove();
}




async function updateStores() {
    const clientId = document.getElementById('editClientID').value;
    const stores = [];

    // Loop through each store row and collect the data
    document.querySelectorAll('.store-row').forEach((row, index) => {
        const storeLinkInput = document.getElementById(`storeLink${index}`);
        const storeTypeInput = document.getElementById(`storeType${index}`);
        const consumerKeyInput = document.getElementById(`consumerKey${index}`);
        const consumerSecretInput = document.getElementById(`consumerSecret${index}`);
        const accessTokenInput = document.getElementById(`accessToken${index}`);

        if (storeLinkInput && storeTypeInput && consumerKeyInput && consumerSecretInput &&
            accessTokenInput) {
            const store = {
                id: storeLinkInput.dataset.storeId || '', // Use an empty string if it's a new store
                store_link: storeLinkInput.value,
                store_type: storeTypeInput.value,
                consumer_key: consumerKeyInput.value,
                consumer_secret: consumerSecretInput.value,
                access_token: accessTokenInput.value,
            };

            stores.push(store);
        }
    });

    const payload = {
        client_id: clientId,
        stores: stores
    };

    try {
        const res = await fetch('update-vendor-stores.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        // Check if the response is OK (status 200)
        if (!res.ok) {
            const errorText = await res.text();
            console.error('Error response:', errorText);
            alert('Error updating stores. Please try again.');
            return;
        }

        // Try to parse the response as JSON
        const result = await res.json();

        if (result.success) {
            alert('Stores updated successfully');
            const modal = bootstrap.Modal.getInstance(document.getElementById('vendorStoreModal'));
            modal.hide();
            window.location = 'client.php';
        } else {
            alert('Error updating stores: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Update error:', error);
        alert('Error updating stores');
    }
}




function togglePassword(button) {
    const icon = button.querySelector('i');
    const input = button.closest('.input-group').querySelector('input');

    if (!input || !icon) return;

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';

    icon.classList.toggle('bi-eye', isPassword);
    icon.classList.toggle('bi-eye-slash', !isPassword);
}
async function fetchClientDetails(clientId) {
    try {
        const response = await fetch(`get_client_data.php?client_id=${encodeURIComponent(clientId)}`);
        if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

        const data = await response.json();
        console.log("Client Details:", data);

        if (data.status === 'success' && data.client && data.client.client_id) {
            const client = data.client;

            document.getElementById('editClientclient_id').value = client.client_id;
            document.getElementById('editClientvendor_name').value = client.vendor_name || '';
            document.getElementById('editClientemail').value = client.email || '';
            document.getElementById('editClientphone_number').value = client.phone_number || '';
            document.getElementById('editClientcnic').value = client.cnic || '';
            document.getElementById('editClientaddress').value = client.address || '';
            document.getElementById('editClientrate_of_product').value = client.rate_of_product || '';
            document.getElementById('editClientcreated_at').value = client.created_at || '';

            // If you want to use user/delivery/stores data later, you can access:
            // data.user, data.delivery_services, data.stores

            const modal = new bootstrap.Modal(document.getElementById('clientDetailsModal'));
            modal.show();
        } else {
            alert("Client data not found.");
        }
    } catch (error) {
        console.error("Error fetching client details:", error);
        alert("Error fetching client details: " + error.message);
    }
}

async function updateClient() {
    const clientData = {
        client_id: document.getElementById("editClientclient_id").value,
        vendor_name: document.getElementById("editClientvendor_name").value,
        email: document.getElementById("editClientemail").value,
        phone_number: document.getElementById("editClientphone_number").value,
        cnic: document.getElementById("editClientcnic").value,
        address: document.getElementById("editClientaddress").value,
        rate_of_product: document.getElementById("editClientrate_of_product").value
    };

    try {
        const response = await fetch("update-client-details.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(clientData)
        });

        const result = await response.json();
        if (result.success) {
            alert("Client updated successfully!");
            const modal = bootstrap.Modal.getInstance(document.getElementById('clientDetailsModal'));
            modal.hide();
            window.location = 'client.php';
        } else {
            alert("Failed to update client: " + (result.error || "Unknown error"));
        }
    } catch (error) {
        console.error("Error updating client:", error);
        alert("An error occurred while updating client.");
    }
}

async function openUserModal(clientId) {
    try {
        const res = await fetch(`get_client_data.php?client_id=${encodeURIComponent(clientId)}`);
        const data = await res.json();

        if (data.status === 'success') {
            document.getElementById('editUseruserClientID').value = clientId;
            document.getElementById('editUseruserVendorName').value = data.client.vendor_name || '';
            document.getElementById('editUserusername').value = data.user.username || '';
            document.getElementById('editUseremail').value = data.user.email || '';

            const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
            modal.show();
        } else {
            alert('Client not found');
        }
    } catch (error) {
        console.error('Error loading client data:', error);
        alert('Failed to load client data.');
    }
}
async function updateUser() {
    const clientId = document.getElementById('editUseruserClientID').value;
    const username = document.getElementById('editUserusername').value;
    const email = document.getElementById('editUseremail').value;
    const password = document.getElementById('editUserpassword').value;

    const payload = {
        client_id: clientId,
        username: username,
        email: email
    };

    if (password.trim() !== "") {
        payload.password = password;
    }

    try {
        const res = await fetch('update-user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await res.json();
        if (result.success) {
            alert('User details updated successfully');
            const modal = bootstrap.Modal.getInstance(document.getElementById('userDetailsModal'));
            modal.hide();
            window.location = 'client.php';
        } else {
            alert('Error updating user: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Update error:', error);
        alert('Error updating user');
    }
}

function confirmDeletion(clientId) {
    if (confirm("Are you sure you want to permanently delete this vendor?")) {
        // Send to a PHP script using a hidden form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete_vendor.php';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_vendor';
        input.value = clientId;
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }
}
// Function to add a new delivery service row
function addDeliveryService() {
    const deliveryServicesContainer = document.getElementById('deliveryServices');
    const newRow = document.createElement('div');
    newRow.classList.add('delivery-service-row', 'mb-3');
    newRow.innerHTML = `
               <div class="row align-items-end g-3 delivery-service-row">
    <div class="col-md-5">
    <label class="form-label text-start   w-100">Delivery Type</label>
    <select class="form-select" name="delivery_type[]" required>
        <?php
        $deliveryQuery = "SELECT service_name FROM delivery_services_list ORDER BY service_name ASC";
        $deliveryResult = mysqli_query($connect, $deliveryQuery);

        if ($deliveryResult && mysqli_num_rows($deliveryResult) > 0) {
            while ($row = mysqli_fetch_assoc($deliveryResult)) {
                echo '<option value="' . htmlspecialchars($row['service_name']) . '">' . htmlspecialchars($row['service_name']) . '</option>';
            }
        } else {
            echo '<option disabled>No delivery services found</option>';
        }
        ?>
    </select>
</div>

    <div class="col-md-5">
        <label class="form-label text-start   w-100">API Token</label>
        <input type="text" class="form-control" name="api_token[]" placeholder="API Token" required>
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-danger w-100 mt-4 remove-service-btn" onclick="removeDeliveryService(this)">
            Remove
        </button>
    </div>
</div>

            `;
    deliveryServicesContainer.appendChild(newRow);
}

// Function to remove a delivery service row
function removeDeliveryService(button) {
    // Find the parent row of the clicked button and remove it
    const row = button.closest('.delivery-service-row');
    if (row) {
        row.remove();
    }
}


// Function to add a new store row
function addStore() {
    const storeDetailsContainer = document.getElementById('storeDetails');
    const newRow = document.createElement('div');
    newRow.classList.add('store-detail-row', 'mb-3');
    newRow.innerHTML = `
               <div class="mb-3">
    <hr>
   
    <div class="row g-3">
        <div class="col-md-6">
    <label for="store_type" class="form-label text-start   w-100">Store Type</label>
    <select class="form-select" name="store_type[]" required>
        <?php
        $storeQuery = "SELECT store_name FROM store_list ORDER BY store_name ASC";
        $storeResult = mysqli_query($connect, $storeQuery);

        if ($storeResult && mysqli_num_rows($storeResult) > 0) {
            while ($store = mysqli_fetch_assoc($storeResult)) {
                echo '<option value="' . htmlspecialchars($store['store_name']) . '">' . htmlspecialchars($store['store_name']) . '</option>';
            }
        } else {
            echo '<option disabled>No stores found</option>';
        }
        ?>
    </select>
</div>

        <div class="col-md-6">
            <label for="store_link" class="form-label text-start   w-100">Store Link</label>
            <input type="text" class="form-control" name="store_link[]" placeholder="Store Link">
        </div>
        <div class="col-md-6">
            <label for="consumer_key" class="form-label text-start   w-100">API Key</label>
            <input type="text" class="form-control" name="consumer_key[]" placeholder="API Key">
        </div>
        <div class="col-md-6">
            <label for="consumer_secret" class="form-label text-start   w-100">API Secret</label>
            <input type="text" class="form-control" name="consumer_secret[]" placeholder="API Secret">
        </div>
        <div class="col-md-6">
            <label for="access_token" class="form-label text-start   w-100">Access Token (Optional)</label>
            <input type="text" class="form-control" name="access_token[]" placeholder="Access Token (Optional)">
        </div>
        <div class="col-md-6">
        <label for="access_token" class="form-label text-start w-100">&nbsp;</label>
            <button type="button" class="w-100 btn btn-danger remove-store-btn" onclick="removeStore(this)">Remove</button>
        </div>
    </div>
</div>


            `;
    storeDetailsContainer.appendChild(newRow);
}

// Function to remove a store row
function removeStore(button) {
    // Find the parent row of the clicked button and remove it
    const row = button.closest('.row');
    if (row) {
        row.remove();
    }
}

document.getElementById("tableSearch").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#vendor-table-body tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
});

document.addEventListener("DOMContentLoaded", function() {
    const recordsPerPage = 10;
    let currentPage = 1;

    const clients = [
        <?php while ($row = mysqli_fetch_assoc($result)) : ?> {
            name: "<?= htmlspecialchars($row['vendor_name'] ?? '') ?>",
            phone: "<?= htmlspecialchars($row['phone_number'] ?? '') ?>",
            cnic: "<?= htmlspecialchars($row['cnic'] ?? '') ?>",
            address: "<?= htmlspecialchars($row['address'] ?? '') ?>",
            rate: "<?= htmlspecialchars($row['rate_of_product'] ?? '') ?>",
            delivery_services: "<?= htmlspecialchars($row['delivery_services'] ?? '') ?>",
            store_details: "<?= htmlspecialchars($row['store_details'] ?? '') ?>",
            client_id: "<?= htmlspecialchars($row['client_id'] ?? '') ?>",
        },
        <?php endwhile; ?>
    ];


    const tableBody = document.getElementById("vendor-table-body");
    const pagination = document.getElementById("pagination");

    function renderTable(page) {
        tableBody.innerHTML = "";
        let start = (page - 1) * recordsPerPage;
        let end = start + recordsPerPage;
        let paginatedItems = clients.slice(start, end);

        paginatedItems.forEach(vendor => {
            let row = `<tr>
      
    <td>${vendor.client_id}</td>
    <td>${vendor.name}</td>
    <td>${vendor.phone}</td>
    <td>${vendor.cnic}</td>
    <td>${vendor.address}</td>
    <td>${vendor.rate}</td>
    <td>${vendor.delivery_services}</td>
    <td>${vendor.store_details}</td>
    <td>
     <div class="btn-group" role="group">
  <!-- Edit Dropdown Button -->
  <div class="btn-group" role="group">
    <button type="button" class="btn btn-sm btn-warning dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-pencil text-white"></i>
    </button>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item" href="#" id="client-details" onclick="fetchClientDetails('${vendor.client_id}')"><i class="bi bi-person"></i>&nbsp;&nbsp;&nbsp; Client Details</a></li>

      <li><a class="dropdown-item" href="#" onclick="openVendorStoreModal('${vendor.client_id}')"id="store-details"><i class="bi bi-shop"></i>&nbsp;&nbsp;&nbsp; Store Details</a></li>
      <li><a class="dropdown-item" href="#" onclick="openDeliveryServiceModal('${vendor.client_id}')"><i class="bi bi-truck"></i>&nbsp;&nbsp;&nbsp; Delivery Service Details</a></li>

      <li><a class="dropdown-item" href="#" onclick="openUserModal('${vendor.client_id}')"><i class="bi bi-door-open"></i>&nbsp;&nbsp;&nbsp; User Details</a></li>
      <hr>
      <li><a class="dropdown-item" href="#" onclick="confirmDeletion('${vendor.client_id}')"><i class="bi bi-trash" ></i>&nbsp;&nbsp;&nbsp; Delete User</a></li>
    </ul>
  </div>

  <!-- Trash/Delete Button -->
  
</div>

</div>
    </td> 
</tr>`;

            tableBody.innerHTML += row;
        });

        updatePagination();
    }





    function updatePagination() {
        let totalPages = Math.ceil(clients.length / recordsPerPage);
        let paginationHTML = "";

        paginationHTML += `<li class="page-item ${currentPage === 1 ? " disabled" : "" }" id="prev-page">
    <a class="page-link" href="#">Previous</a>
</li>`;

        for (let i = 1; i <= totalPages; i++) {
            paginationHTML += `<li class="page-item ${currentPage === i ? " active" : "" }">
    <a class="page-link" href="#">${i}</a></li>`;
        }

        paginationHTML += `<li class="page-item ${currentPage === totalPages ? " disabled" : "" }" id="next-page">
        <a class="page-link" href="#">Next</a>
    </li>`;

        pagination.innerHTML = paginationHTML;
        addPaginationEvents();
    }

    function addPaginationEvents() {
        document.querySelectorAll(".page-item").forEach(item => {
            item.addEventListener("click", function(event) {
                event.preventDefault();
                let text = this.textContent.trim();
                if (text === "Previous" && currentPage > 1) {
                    currentPage--;
                } else if (text === "Next" && currentPage < Math.ceil(clients.length /
                        recordsPerPage)) {
                    currentPage++;
                } else if (!isNaN(text)) {
                    currentPage = parseInt(text);
                }
                renderTable(currentPage);
            });
        });
    }
    renderTable(currentPage);
});
async function openDeliveryServiceModal(clientId) {
    try {
        const res = await fetch(`get_client_data.php?client_id=${encodeURIComponent(clientId)}`);
        const data = await res.json();

        if (data.status === 'success') {
            document.getElementById('editDeliveryServiceClientID').value = clientId;
            document.getElementById('editDeliveryServiceVendorName').value = data.client.vendor_name || '';

            const deliveryServices = data.delivery_services || [];
            const deliveryServiceRows = document.getElementById('deliveryServiceRows');
            deliveryServiceRows.innerHTML = ''; // Clear existing rows

            deliveryServices.forEach((service, index) => {
                addDeliveryServiceRow(service.id, service.delivery_type, service.api_token, index);
            });

            const modal = new bootstrap.Modal(document.getElementById('deliveryServiceModal'));
            modal.show();
        } else {
            alert('Client not found');
        }
    } catch (error) {
        console.error('Error loading client data:', error);
        alert('Failed to load client data.');
    }
}

function addNewDeliveryServiceRow() {
    addDeliveryServiceRow('', '', '', document.querySelectorAll('.delivery-service-row').length);
}
let deliveryServiceOptions = []; // This will be filled once from PHP

function setDeliveryServiceOptions(options) {
    deliveryServiceOptions = options;
}

function addDeliveryServiceRow(id, deliveryType, apiToken, index) {
    const row = document.createElement('div');
    row.classList.add('mb-2', 'delivery-service-row');

    // Build <option>s
    const optionsHTML = deliveryServiceOptions.map(opt => {
        const selected = opt === deliveryType ? 'selected' : '';
        return `<option value="${opt}" ${selected}>${opt}</option>`;
    }).join('');

    row.innerHTML = `
    <div class="row mb-2">
        <div class="col-md-2 d-flex align-items-center">
            <label for="deliveryType${index}" class="me-2 text-start   w-100">Delivery Type</label>
        </div>
        <div class="col-md-3 d-flex align-items-center">
            <select id="deliveryType${index}" class="form-select">${optionsHTML}</select>
        </div>
        <div class="col-md-2 d-flex align-items-center">
            <label for="apiToken${index}" class="me-2 text-start   w-100">API Token</label>
        </div>
        <div class="col-md-4 d-flex align-items-center">
            <input id="apiToken${index}" class="form-control" value="${apiToken || ''}">
        </div>
        <div class="col-md-1 d-flex align-items-center">
            <button type="button" class="btn btn-danger" onclick="removeDeliveryServiceRow(this)">
                <i class="fa fa-times" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    `;

    document.getElementById('deliveryServiceRows').appendChild(row);
}



function removeDeliveryServiceRow(button) {
    const row = button.closest('.row');
    if (row) row.remove();
}


async function updateDeliveryServices() {
    const clientId = document.getElementById('editDeliveryServiceClientID').value;
    const deliveryServices = [];

    // Loop through each row and get the values of delivery type and API token
    document.querySelectorAll('.delivery-service-row').forEach((row, index) => {
        const deliveryTypeInput = document.getElementById(`deliveryType${index}`);
        const apiTokenInput = document.getElementById(`apiToken${index}`);

        if (deliveryTypeInput && apiTokenInput) {
            const deliveryType = deliveryTypeInput.value;
            const apiToken = apiTokenInput.value;

            deliveryServices.push({
                delivery_type: deliveryType,
                api_token: apiToken
            });
        }
    });

    const payload = {
        client_id: clientId,
        delivery_services: deliveryServices
    };

    try {
        const res = await fetch('update-delivery-services.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await res.json();
        if (result.success) {
            alert('Delivery services updated successfully');
            const modal = bootstrap.Modal.getInstance(document.getElementById('deliveryServiceModal'));
            modal.hide();
            window.location = 'client.php';
        } else {
            alert('Error updating delivery services: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Update error:', error);
        alert('Error updating delivery services');
    }
}

<?php
require_once '../php_action/db_connect.php';
$options = [];
$query = "SELECT service_name FROM delivery_services_list ORDER BY service_name ASC";
$result = mysqli_query($connect, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $options[] = $row['service_name'];
}
?>

// Set delivery service options in JavaScript
setDeliveryServiceOptions(<?= json_encode($options) ?>);
</script>

<?php require_once 'includes/footer.php'; ?>