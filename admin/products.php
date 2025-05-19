<?php require_once 'includes/header.php'; 
require_once '../php_action/db_connect.php'; 
?>

<style>
.selected {
    background-color: #f8f9fa;
    /* bg-light color */
    color: #212529;
    /* Text color for light background (default text color) */
    border: 3px solid #007bff;
    /* Blue border for selected card */
}
</style>
<script>
function setupTableSearch(inputId, tableId) {
    document.getElementById(inputId).addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let table = document.getElementById(tableId);
        let rows = table.getElementsByTagName("tr");

        for (let i = 1; i < rows.length; i++) { // Skipping header row
            let cells = rows[i].getElementsByTagName("td");
            let match = false;

            for (let cell of cells) {
                if (cell.textContent.toLowerCase().includes(filter)) {
                    match = true;
                    break;
                }
            }
            rows[i].style.display = match ? "" : "none";
        }
    });
}

function fetchProductData(productId) {
    let productDetails = document.getElementById("productDetails");

    if (productId) {
        fetch('get_product_data.php?product_id=' + productId)
            .then(response => response.json())
            .then(data => {
                console.log("Response Data:", data);

                if (data && data.stock_quantity !== undefined) {
                    document.getElementById("current_stock").value = data.stock_quantity;
                    productDetails.style.display = "block";
                } else {
                    document.getElementById("current_stock").value = "0"; // Default to zero if not found
                    productDetails.style.display = "none";
                }
            })
            .catch(error => {
                console.error("Error fetching product data:", error);
                document.getElementById("current_stock").value = "Error";
                productDetails.style.display = "none";
            });
    } else {
        document.getElementById("current_stock").value = "";
        productDetails.style.display = "none"; // Hide details if no product is selected
    }
}

function confirmStockUpdate(event) {
    event.preventDefault(); // Prevent form submission

    let productId = document.querySelector("select[name='sku']").value;
    let productName = document.querySelector("select[name='sku'] option:checked").text;
    let currentStock = parseInt(document.getElementById("current_stock").value);
    let addingStock = parseInt(document.querySelector("input[name='stock_quantity']").value);

    if (!addingStock || addingStock <= 0) {
        showMessage('Please enter a valid stock quantity!', 'warning');

        return;
    }

    let finalStock = currentStock + addingStock;

    let confirmation = confirm(
        `Product ID: ${productId}\nProduct Name: ${productName}\n` +
        `Current Stock: ${currentStock}\nAdding Stock: ${addingStock}\n` +
        `Final Stock after update: ${finalStock}\n\nDo you want to proceed?`
    );

    if (confirmation) {
        document.getElementById("stockForm").submit(); // Submit form
    }
}
document.getElementById("tableSearch").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#product-table tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
});

function fetchClientData(client_id) {
    if (client_id) {
        fetch('get_new_product_id.php?client_id=' + client_id)
            .then(response => response.json())
            .then(data => {
                document.getElementById("client_id").value = data.client_id || "Error";
                document.getElementById("product_id").value = data.product_id || "Error";
            })
            .catch(error => {
                console.error("Error fetching client data:", error);
                document.getElementById("client_id").value = "Error";
                document.getElementById("product_id").value = "Error";
            });
    } else {
        document.getElementById("client_id").value = "";
        document.getElementById("product_id").value = "";
    }
}

function checkClientStore(client_id) {
    fetchClientData(client_id);
    if (client_id) {
        fetch('check_store.php?client_id=' + client_id)
            .then(response => response.json())
            .then(data => {
                let storeButtonDiv = document.getElementById("storeButtonDiv");
                let manualEntryBtn = document.getElementById("manualEntryBtn");

                storeButtonDiv.innerHTML = "";

                if (data.store_link) {
                    storeButtonDiv.innerHTML = `
                        <div class="form-floating mb-3">
                            <div class="input-group">
                                <div class="form-control bg-light text-secondary">
                                    <i class="fa fa-store me-2"></i> ${data.store_type} Store: 
                                    <a href="${data.store_link}" target="_blank" class="link-primary">${data.store_link}</a>
                                </div>
                                <button type="button" class="align-items-center btn btn-success" onclick="fetchStoreProducts('${client_id}')">
                                    <span id="spinn" style="display:none;" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;Get Products 
                                </button>
                                

                            </div>
                        </div>
                    `;
                    manualEntryBtn.style.display = "block";
                } else {
                    manualEntryBtn.style.display = "block";
                    manualEntryBtn.click();
                }
            })
            .catch(error => {
                console.error("Error fetching store details:", error);
                document.getElementById("storeButtonDiv").innerHTML = "";
                document.getElementById("manualEntryBtn").style.display = "block";
                document.getElementById("manualEntryBtn").click();
            });
    } else {
        document.getElementById("storeButtonDiv").innerHTML = "";
        document.getElementById("manualEntryBtn").style.display = "none";
        document.getElementById("manualFormDiv").style.display = "none";
    }
}

function fetchStoreProducts(client_id) {
    document.getElementById("spinn").style.display = "inline-block";

    fetch('get_store_credentials.php?client_id=' + client_id)
        .then(response => response.json())
        .then(data => {
            if (data.store_type === "WooCommerce") {
                if (data.consumer_key && data.consumer_secret) {
                    const perPage = 100;
                    let page = 1;
                    let allProducts = [];

                    const fetchAllPages = async () => {
                        let morePages = true;

                        while (morePages) {
                            let apiUrl =
                                `${data.store_link}/wp-json/wc/v3/products?consumer_key=${data.consumer_key}&consumer_secret=${data.consumer_secret}&per_page=${perPage}&page=${page}`;

                            const response = await fetch(apiUrl);
                            const products = await response.json();

                            if (products.length > 0) {
                                allProducts.push(...products);
                                page++;
                            } else {
                                morePages = false;
                            }
                        }

                        return allProducts;
                    };

                    fetchAllPages().then(products => {
                        let allProcessedProducts = [...products];
                        let variationRequests = [];

                        products.forEach(product => {
                            if (product.type === "variable") {
                                let variationUrl =
                                    `${data.store_link}/wp-json/wc/v3/products/${product.id}/variations?consumer_key=${data.consumer_key}&consumer_secret=${data.consumer_secret}`;

                                variationRequests.push(
                                    fetch(variationUrl)
                                    .then(response => response.json())
                                    .then(variations => {
                                        variations.forEach(variation => {
                                            allProcessedProducts.push({
                                                id: variation.id,
                                                name: `${product.name} - ${variation.attributes.map(attr => attr.option).join(", ")}`,
                                                sku: variation.sku,
                                                price: variation.price,
                                                stock_status: variation
                                                    .stock_status,
                                                categories: product
                                                    .categories.map(cat =>
                                                        cat.name),
                                                image: product.images
                                                    .length ? product
                                                    .images[0].src : "",
                                                description: `(Variation of ${product.name})`
                                            });
                                        });
                                    })
                                    .catch(error => console.error(
                                        `Error fetching variations for ${product.name}:`,
                                        error))
                                );
                            }
                        });

                        Promise.all(variationRequests).then(() => {
                            showProductModal(allProcessedProducts);
                            document.getElementById("spinn").style.display = "none";
                        });
                    }).catch(error => console.error("Error fetching WooCommerce products:", error));
                } else {
                    alert("No API credentials found for this client.");
                }
            } else if (data.store_type === "Shopify") {
                // Fetch Shopify products from your PHP file instead of calling Shopify API directly
                let apiUrl =
                    `get_shopify_products.php?store_link=${encodeURIComponent(data.store_link)}&access_token=${encodeURIComponent(data.access_token)}`;
                fetch(apiUrl)
                    .then(response => response.json())
                    .then(products => {
                        if (products.products && Array.isArray(products.products)) {
                            showProductModal(products.products);
                        } else {
                            console.error("Error fetching Shopify products or no products found.");
                        }
                        document.getElementById("spinn").style.display = "none";
                    })
                    .catch(error => {
                        console.error("Error fetching Shopify products:", error);
                        document.getElementById("spinn").style.display = "none";
                    });
            } else {
                alert("Unknown store type.");
                document.getElementById("spinn").style.display = "none";
            }
        })
        .catch(error => {
            console.error("Error fetching store credentials:", error);
            document.getElementById("spinn").style.display = "none";
        });
}

function fetchShopifyData(url, access_token) {
    return new Promise((resolve, reject) => {
        let ch = new XMLHttpRequest();
        ch.open('GET', url, true);
        ch.setRequestHeader("X-Shopify-Access-Token", access_token);
        ch.setRequestHeader("Content-Type", "application/json");
        ch.onload = function() {
            if (ch.status === 200) {
                resolve({
                    status: ch.status,
                    response: JSON.parse(ch.responseText)
                });
            } else {
                reject(`Error: ${ch.status}`);
            }
        };
        ch.onerror = function() {
            reject("Request failed.");
        };
        ch.send();
    });
}

function showProductModal(client_id) {

    let tableBody = document.getElementById("productTableBody");
    let spinner = document.getElementById("spinner");
    let modalElement = document.getElementById("productModal");

    if (!tableBody || !spinner) {
        console.error("Error: Required elements not found in DOM.");
        return;
    }


    fetch(`fetch_products.php?client_id=${client_id}`)
        .then(response => response.json())
        .then(products => {
            console.log(products);
            spinner.style.display = "none";

            if (products.length > 0) {
                products.forEach(product => {
                    let row = `
                        <tr>
                            <td>${product.name}</td>
                            <td>${product.id}</td>
                            <td>${product.price}</td>
                            
                            <td>
                                <button class="btn btn-primary btn-sm" 
                                    onclick="addProductToSystem('${product.id}', '${product.name}', '${product.price}')">
                                    <i class="fa fa-plus"></i> Add to System
                                </button>
                            </td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            } else {
                tableBody.innerHTML = "<tr><td colspan='4' class='text-center'>No products found</td></tr>";
            }

            // Show modal after loading
            if (modalElement) {
                let modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        })
        .catch(error => {
            // Hide the spinner in case of an error
            spinner.style.display = "none";
            console.error("Error fetching products:", error);
            tableBody.innerHTML =
                "<tr><td colspan='4' class='text-center text-danger'>Failed to load products</td></tr>";
        });
}

function showProductModal(products) {
    let tableBody = document.getElementById("productsFromStoreTable");

    if (!tableBody) {
        console.error("Error: #productTableBody element not found in DOM.");
        return;
    }

    tableBody.innerHTML = "";

    // Sort products by product.id in ascending order
    products.sort((a, b) => a.id - b.id);

    if (products.length > 0) {
        products.forEach(product => {
            let row = `
                <tr>
                  <td class="text-center align-middle">
    <img src="${product.image}" height="50" 
         onerror="this.onerror=null; this.src='../img/box.png';" 
         class="d-block mx-auto">
                    </td>

                    <td>${product.name}</td>
                    <td>${product.id}</td>
                    <td>${product.price}</td>
                    <td><input type="number" class="form-control" value="0.00" id="product_weight_${product.id}" name="product_weight"></td>
                    <td>
                        <button class="btn btn-primary btn-sm" 
                            onclick="addProductToSystem('${product.id}', '${product.name}', '${product.price}', document.getElementById('product_weight_${product.id}').value)">
                            <i class="fa fa-plus"></i> Add to System
                        </button>
                    </td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
    } else {
        tableBody.innerHTML = "<tr><td colspan='4' class='text-center'>No products found</td></tr>";
    }

    let modalElement = document.getElementById("productModal");
    if (modalElement) {
        let modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else {
        console.error("Error: #productModal element not found in DOM.");
    }
}

function addProductToSystem(productStoreId, productName, price, productWeight) {
    let client_id = document.getElementById("client_id").value;
    fetch(`check_product_exists.php?product_store_id=${productStoreId}`)
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                showMessage('This product is already added to the system!', 'warning');

            } else {
                // Fetch new product ID
                fetch(`get_new_product_id.php?client_id=${client_id}`)
                    .then(response => response.json())
                    .then(data => {
                        let newProductId = data.product_id || "Error";

                        if (newProductId === "Error") {
                            showMessage('Error generating product ID!', 'danger');

                            return;
                        }

                        // Insert new product into the system
                        fetch("insert_product.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: `client_id=${client_id}&product_id=${newProductId}&product_store_id=${productStoreId}&product_name=${encodeURIComponent(productName)}&price=${price}&weight=${productWeight}`
                            })
                            .then(response => response.text())
                            .then(result => {
                                alert(result);
                            })
                            .catch(error => console.error("Error inserting product:", error));
                    })
                    .catch(error => console.error("Error fetching new product ID:", error));
            }
        })
        .catch(error => console.error("Error checking product existence:", error));
}

function showManualForm() {
    document.getElementById("manualFormDiv").style.display = "block";
}
document.getElementById("tableSearch").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#productTableBody tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
});

function openAddStockModal(productId, productName) {
    let selectProduct = document.getElementById("selectProduct");

    // Set the selected value
    selectProduct.value = productId;

    // Trigger change event to load product details
    fetchProductData(productId);

    // Show the modal
    let addStockModal = new bootstrap.Modal(document.getElementById("addStockModal"));
    addStockModal.show();
}
</script>

<style>
.number {
    border: 3px solid blue;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    color: blue;
}
</style>
<script>
// Function to load vendors dynamically
function loadVendors() {
    fetch('get_vendors.php') // API endpoint to get the vendor names
        .then(response => response.json())
        .then(vendors => {
            let vendorContainer = document.getElementById("vendorContainer");
            vendorContainer.innerHTML = ''; // Clear existing vendors
            vendors.forEach(vendor => {
                let vendorCard = `
                
    <div class="zoom-hover" id="vendorContainer" style="margin-bottom:10px;">
        <div  data-client-id="${vendor.client_id}">
                <div class="card vendor-card  p-1" style="cursor: pointer;">
    <div class="card-body">
        <div class="row g-2 align-items-center">
            <!-- First Column: Number (Client ID) -->
            <div class="col-auto d-flex flex-column align-items-center">
                <div class="number">${vendor.client_id}</div>
            </div>&nbsp;&nbsp;&nbsp;&nbsp;
            <!-- Second Column: Vendor Details -->
            <div class="col">
                <h6 class="mb-1">${vendor.vendor_name}</h6>
                <p class="mb-0 text-muted">${vendor.email}</p>
            </div>
        </div>
        
    </div>
</div>
</div>
</div>

`;

                vendorContainer.innerHTML += vendorCard;
            });

            // Now add the click event listener after the vendors are loaded
            let vendorCards = document.querySelectorAll('.vendor-card');
            vendorCards.forEach(card => {
                card.addEventListener('click', function() {
                    let clientId = this.parentElement.getAttribute('data-client-id');
                    console.log("Clicked Vendor ID:", clientId); // Debugging line

                    // Remove the 'selected' class from all vendor cards
                    vendorCards.forEach(c => c.classList.remove('selected'));

                    // Add the 'selected' class to the clicked card
                    this.classList.add('selected');

                    loadProducts(clientId); // Call loadProducts when a vendor card is clicked


                });
            });
        })
        .catch(error => console.error("Error fetching vendor data:", error));
}

// Function to load products based on selected client
function loadProducts(clientId) {
    console.log("Loading products for client ID:", clientId); // Debugging line
    fetch('get_products.php?client_id=' + clientId)
        .then(response => response.text()) // Read as plain text
        .then(text => {
            console.log("Raw response:", text); // Log the raw response

            try {
                const products = JSON.parse(text); // Try parsing as JSON
                console.log("Parsed products:", products);
                let productTableBody = document.getElementById("productTableBody");
                productTableBody.innerHTML = ''; // Clear existing products

                if (products.length === 0) {
                    showMessage('Not Any Product Found!', 'information');
                    productTableBody.innerHTML =
                        '<tr><td colspan="7" class="text-center">No products found for this vendor.</td></tr>';
                } else {
                    products.forEach(product => {
                        let row = `
    <tr>
        <td>${product.product_id}</td>
        <td>${product.product_name}</td>
        <td>${product.price}</td>
        <td>${product.stock_quantity}</td>
        <td>${product.weight}</td>
        <td>${product.updated_at}</td>
        <td>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <button class="dropdown-item" onclick="openAddStockModal('${product.product_id}', '${product.product_name}')">
                            Add Stock
                        </button>
                    </li>
                    
                </ul>
            </div>
        </td>
    </tr>
`;

                        productTableBody.innerHTML += row;
                        document.getElementById("productTable").style.display = "block"; // Show the table
                        document.getElementById("productDetails").style.display = "block"; // Show the table

                        document.getElementById("productDetails").scrollIntoView({
                            behavior: "smooth"
                        });

                    });
                }
            } catch (error) {
                console.error("Error parsing JSON:", error);
            }
        })
        .catch(error => console.error("Error fetching products:", error));
}

// Load vendors on page load
window.onload = loadVendors;
</script>





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

                                Products

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
    <div class="bg-light rounded h-100 p-4 pb-0">
        <div class="table-container p-4">
            <!-- Filter Tabs & Search Bar (Row 1) -->
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="filter-tabs d-flex flex-wrap gap-2">
                    <h5 class="mb-0 d-flex align-items-center">
                        Products
                    </h5>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <!-- Search Box -->

                    <!-- Reload Button -->
                    <button class="btn btn-primary d-flex align-items-center justify-content-center"
                        onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>

                    <!-- Add Button -->
                    <button class="btn btn-primary d-flex align-items-center justify-content-center"
                        data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus"></i>&nbsp;&nbsp; Add Product
                    </button>


                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-12">
    <div class="bg-light rounded h-100 p-4">





        <div class="row h-100 bg-light">
            <!-- First Column -->
            <div class="col-md-4">
                <div class="bg-light rounded p-3">
                    <h5 class="mb-0">Clients</h5>
                </div>
                <div id="vendorContainer" class="row">
                    <!-- Vendor Cards will be injected here dynamically, each card will be in a separate row -->
                </div>
            </div>

            <!-- Second Column -->
            <div class="col-md-8">
                <div class="bg-light rounded h-100 p-4 " id="productDetails" style="display: none;">

                    <div class="table-responsive mt-5 mb-0 table-container">

                        <!-- Filter Tabs & Search Bar (Row 1) -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div class="filter-tabs d-flex flex-wrap gap-2">
                                <h5 class="mb-0 d-flex align-items-center">
                                    <i class="fa fa-box-open me-2"></i> Product Details
                                </h5>

                            </div>
                            <div class="search-bar">
                                <input type="text" class="form-control" style="min-width: 250px; height: 40px;"
                                    id="searchBox1" placeholder="Search Products..."
                                    onkeyup="setupTableSearch('searchBox1', 'productTable')">

                                <!-- Reload Button -->
                                <button id="downloadTableDataInExcel"
                                    onclick="downloadTableDataInExcel('productTable','Product');"
                                    class="btn btn-primary d-flex align-items-center justify-content-center"
                                    style="height: 40px;">
                                    <i class="bi bi-download"></i>
                                </button>


                            </div>
                        </div>

                        <br>

                        <table class="table table-hover" id="productTable" style="display: none;">

                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Stock Quantity</th>
                                    <th>Weight</th>
                                    <th>Updated at</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <!-- Products will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>




    </div>
</div>









<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel"><i class="fa fa-box me-2"></i> Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="form-floating mb-3">
                        <select class="form-select" name="client_id" required onchange="checkClientStore(this.value)">
                            <option value="">Select Client</option>
                            <?php
                                $clients = mysqli_query($connect, "SELECT * FROM clients ORDER BY client_id ASC");
                                while ($client = mysqli_fetch_assoc($clients)) {
                                    echo "<option value='{$client['client_id']}'>{$client['client_id']} - {$client['vendor_name']}</option>";
                                }
                            ?>
                        </select>
                        <label for="client_id">Select Client</label>
                    </div>

                    <div id="storeButtonDiv"></div> <!-- Store Button (If Available) -->

                    <button id="manualEntryBtn" class="btn btn-primary mb-3 w-100" style="display: none;"
                        onclick="showManualForm()">
                        <i class="fa fa-plus"></i> Manual Entry
                    </button>

                    <div id="manualFormDiv" style="display: none;">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="client_id" name="client_id" readonly>
                            <label for="client_id">Client ID</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="product_id" name="product_id" readonly>
                            <label for="product_id">Product ID</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="product_name" placeholder="Product Name"
                                required>
                            <label for="product_name">Product Name</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" name="price" step="0.01" placeholder="Price"
                                required>
                            <label for="price">Price</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" name="weight" step="0.01"
                                placeholder="Weight (kg)" required>
                            <label for="weight">Weight (kg)</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" name="stock_quantity" placeholder="Stock Quantity"
                                required>
                            <label for="stock_quantity">Stock Quantity</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_product" class="btn btn-primary">
                            <i class="fa fa-plus me-1"></i> Add Products
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>





<!-- add Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel"> Products From WooCommerce</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="white-space: nowrap;">Product Image</th>
                                <th style="white-space: nowrap;">Product Name</th>
                                <th style="white-space: nowrap;">Product ID</th>
                                <th style="white-space: nowrap;">Price</th>
                                <th style="width: 15%; white-space: nowrap;">Weight</th> <!-- Button Column -->
                                <th style="width: 15%; white-space: nowrap;">Actions</th> <!-- Button Column -->
                            </tr>
                        </thead>
                        <tbody id="productsFromStoreTable">
                            <tr>
                                <td colspan="4" class="text-center">Fetching products...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- add stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel"><i class="fa fa-boxes me-2"></i> Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="stockModalCloseButton"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="stockForm">
                    <!-- Select Product -->
                    <input type="hidden" name="client_id" id="client_id">
                    <div class="form-floating mb-3">
                        <select class="form-select" id="selectProduct" name="sku" required
                            onchange="fetchProductData(this.value)">
                            <option value="">Select Product</option>
                            <?php
                            $products = mysqli_query($connect, "SELECT * FROM products ORDER BY client_id ASC");
                            while ($product = mysqli_fetch_assoc($products)) {
                                echo "<option value='{$product['product_id']}'>{$product['product_id']} - {$product['product_name']}</option>";
                            }
                            ?>
                        </select>
                        <label for="selectProduct">Select Product</label>
                    </div>

                    <div id="productDetails">
                        <!-- Current Stock Quantity -->
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="current_stock" name="current_stock" readonly>
                            <label for="current_stock">Current Stock Quantity</label>
                        </div>
                        <!-- Stock Quantity -->
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity"
                                required>
                            <label for="stock_quantity">Stock Quantity</label>
                        </div>
                        <!-- Current Date & Time -->
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
                            <label>Current Date & Time</label>
                        </div>
                        <!-- Submit Button -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="add_stock" class="btn btn-success">
                                <i class="fa fa-plus me-1"></i> Add Stock
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
if (isset($_POST['add_product'])) {
    $client_id = mysqli_real_escape_string($connect, $_POST['client_id']);
    $product_name = mysqli_real_escape_string($connect, $_POST['product_name']);
    $product_id = mysqli_real_escape_string($connect, $_POST['product_id']);
    $price = mysqli_real_escape_string($connect, $_POST['price']);
    $stock_quantity = mysqli_real_escape_string($connect, $_POST['stock_quantity']);
    $weight = mysqli_real_escape_string($connect, $_POST['weight']);





    
    $query = "INSERT INTO products (product_id, client_id, product_name, price, stock_quantity, weight) 
              VALUES ('$product_id', '$client_id', '$product_name', '$price', '$stock_quantity', '$weight')";
    
    if (mysqli_query($connect, $query)) {
        $log_query = "INSERT INTO stock_logs (client_id, product_id, quantity_added, added_at) 
        VALUES ('$client_id', '$product_id', '$stock_quantity', NOW())";
        mysqli_query($connect, $log_query);
        echo "<script> showMessage('Product Added Successfully!', 'info');</script>";
        
        echo "<script>window.location = 'products.php';</script>";
    } else {
        echo "<script>alert('Error adding product. Try again.');</script>";
    }
}

if (isset($_POST['add_stock'])) {
    $product_id = mysqli_real_escape_string($connect, $_POST['sku']);
    $adding_stock = (int) mysqli_real_escape_string($connect, $_POST['stock_quantity']);

    // Get current stock and client_id
    $query = "SELECT stock_quantity, client_id FROM products WHERE product_id = '$product_id'";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        echo "<script>showMessage('Product not found!', 'danger');</script>";
        return;
    }

    $current_stock = (int) $row['stock_quantity'];
    $client_id = $row['client_id']; // Directly from DB

    // Calculate new stock
    $final_stock = $current_stock + $adding_stock;

    // Update stock in database
    $update_query = "UPDATE products 
                     SET stock_quantity = '$final_stock', updated_at = NOW() 
                     WHERE product_id = '$product_id'";

    if (mysqli_query($connect, $update_query)) {
        // Insert into stock_logs
        $log_query = "INSERT INTO stock_logs (client_id, product_id, quantity_added, added_at) 
                      VALUES ('$client_id', '$product_id', '$adding_stock', NOW())";
        mysqli_query($connect, $log_query);

        echo "<script> showMessage('Stock Updated Successfully!', 'info');</script>";
    } else {
        echo "<script>alert('Error updating stock: " . mysqli_error($connect) . "');</script>";
    }
}

?>

<?php require_once 'includes/footer.php'; ?>