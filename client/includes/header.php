<?php 
require_once '../php_action/core.php';
require_once '../php_action/db_connect.php';
$user_id = $_SESSION['userId'];
$sql = "SELECT * FROM users WHERE user_id = {$user_id}";
$query = $connect->query($sql);
$result = $query->fetch_assoc();
$userAs = $result['uas'];
if($userAs!=2){
     header("Location: ../logout.php");
    
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <style>
    .navtab {
        background-color: gray;
        border: none;

        padding: 6px 12px;
        font-weight: 500;
        border-radius: 6px;
    }



    .table-container {
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
        padding: 10px;
    }

    .filter-tabs button {
        border: none;

        padding: 6px 12px;
        font-weight: 500;
        border-radius: 6px;
    }

    .filter-tabs .active {
        background: #e9ecef;
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        padding: 10px 0;
    }

    .search-bar {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .search-bar input {
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 5px 10px;
        height: 36px;
    }

    .search-bar button {
        border: none;


        cursor: pointer;
    }

    .status-badge {
        background: #ddd;
        color: #333;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 14px;
    }
    </style>
    <style>
    /* HTML: <div class="loader"></div> */
    /* HTML: <div class="loader"></div> */
    .loader {
        width: 50px;
        padding: 8px;
        aspect-ratio: 1;
        border-radius: 50%;
        background: #25b09b;
        --_m:
            conic-gradient(#0000 10%, #000),
            linear-gradient(#000 0 0) content-box;
        -webkit-mask: var(--_m);
        mask: var(--_m);
        -webkit-mask-composite: source-out;
        mask-composite: subtract;
        animation: l3 1s infinite linear;
    }

    @keyframes l3 {
        to {
            transform: rotate(1turn)
        }
    }

    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #f8f9fa;
        /* Adjust color as needed */
        padding: 10px 0;
        text-align: center;
    }

    .zoom-hover {
        transition: transform 0.2s ease-in-out;
    }

    .zoom-hover:hover {
        transform: scale(1.1);
        /* Zoom in on hover */
    }

    .zoom-hover:active {
        transform: scale(0.9);
        /* Zoom out on click */
    }


    body {
        zoom: 90%;
        /* Adjust the percentage as needed */
    }


    .modal-backdrop {
        width: 100% !important;
        height: 100% !important;
        position: fixed !important;
        top: 0;
        left: 0;
        background-color: rgba(0, 0, 0, 0.5) !important;
        /* Adjust opacity if needed */
        z-index: 1040;
        /* Ensure it's above everything */
    }
    </style>
    <meta charset="utf-8">
    <title>Client Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <script src="../js/main.js"></script>
    <!-- Favicon -->
    <link href="../img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Libre Barcode 39' rel='stylesheet'>
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="../lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <script>
    function downloadTableDataInExcel(tableId, tableName) {
        let table = document.getElementById(tableId);
        if (!table) {
            console.error(`Table with ID '${tableId}' not found.`);
            return; // Exit if the table doesn't exist
        }

        let rows = table.querySelectorAll("tbody tr");

        let data = [];
        let headers = [];

        // Get table headers from the thead element
        let headerCells = table.querySelectorAll("thead th");
        headerCells.forEach(th => headers.push(th.innerText.trim())); // Trim to remove extra spaces
        data.push(headers); // Add headers as the first row of the data

        // Get filtered rows data from tbody
        rows.forEach(row => {
            if (row.style.display !== "none") { // Export only visible rows
                let rowData = [];
                row.querySelectorAll("td").forEach(td => rowData.push(td.innerText
                    .trim())); // Trim to remove extra spaces
                if (rowData.length > 0) { // Check if row has data before adding
                    data.push(rowData);
                }
            }
        });

        // Convert to Excel
        let ws = XLSX.utils.aoa_to_sheet(data);
        let wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Filtered Data");

        // Get current date in YYYY-MM-DD format
        let currentDate = new Date().toISOString().split('T')[0];

        // Download the file with dynamic name
        XLSX.writeFile(wb, `${tableName}-${currentDate}.xlsx`);
    }


    function reloadPageAfterDelay(delay = 2000) {
        setTimeout(() => {
            location.reload();
        }, delay);
    }

    function loadOrders(status) {
        console.log("Loading orders for status:", status); // Debugging log

        // Remove "active" from all tabs
        document.querySelectorAll("#order-tabs .tab-nav").forEach(tab => {
            tab.classList.remove("active");
        });

        // Add "active" class to the correct tab
        let newActiveTab = document.querySelector(`#order-tabs .tab-nav[data-status='${status}']`);
        if (newActiveTab) {
            newActiveTab.classList.add("active");
        }

        // Get selected date filter value
        let dateFilterElement = document.getElementById("dateFilter");
        let dateFilter = dateFilterElement ? dateFilterElement.value : "all";

        // Fetch orders
        fetch(`fetch_orders.php?status=${status}&date_filter=${dateFilter}`)
            .then(response => response.text())
            .then(data => {
                let ordersContent = document.getElementById("orders-content");
                if (ordersContent) {
                    ordersContent.innerHTML = data;
                }
            })
            .catch(error => console.error("Error fetching orders:", error));

        // Store active tab
        localStorage.setItem("activeOrderTab", status);
    }
    const style = document.createElement('style');
    style.innerHTML = `
    #message-container {
        position: fixed;
        bottom: 50px;
        right: 20px;
        z-index: 999999; /* Ensures it's on top of everything */
        max-width: 300px;
    }
    .custom-alert {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 15px;
        opacity: 0;
        transform: translateX(100%);
        transition: opacity 0.5s, transform 0.5s;
        position: relative;
        background: white;
        border: 1px solid #ddd;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }
    .custom-alert.show {
        opacity: 1;
        transform: translateX(0);
    }
    .custom-alert .close-btn {
        background: none;
        border: none;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        color: inherit;
        margin-left: 10px;
    }
`;
    document.head.appendChild(style);

    // Custom JavaScript for showing messages with close button
    function showMessage(message, type = 'success') {
        let messageContainer = document.getElementById('message-container');

        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.id = 'message-container';
            document.body.appendChild(messageContainer);
        }

        let alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} custom-alert`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `<span>${message}</span>`;

        // Close button
        let closeButton = document.createElement('button');
        closeButton.className = 'close-btn';
        closeButton.innerHTML = '&times;';
        closeButton.onclick = function() {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 500);
        };

        alertDiv.appendChild(closeButton);
        messageContainer.appendChild(alertDiv);

        // Show message with slide-in effect
        setTimeout(() => {
            alertDiv.classList.add('show');
        }, 100);

        // Auto remove after 3 seconds
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 500);
        }, 3000);

    }

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

    function showSpinner() {
        document.getElementById('spinner').classList.add('show');
    }

    function hideSpinner() {
        document.getElementById('spinner').classList.remove('show');
    }
    </script>
</head>

<body class="bg-light">
    <div class="container-xxl position-relative  d-flex bg-light">

        <div id="spinner"
            class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="index.php" class="navbar-brand mx-4 mb-3">
                    <h5 class="text-primary">
                        SMART STOCKERS</h5>
                </a>
                <div class="navbar-nav w-100">
                    <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

                    <a href="index.php"
                        class="nav-item nav-link <?= ($currentPage == 'index.php') ? 'active' : '' ?>"><i
                            class="fa fa-tachometer-alt me-2"></i>Dashboard</a>

                    <a href="place_new_order.php"
                        class="nav-item nav-link <?= ($currentPage == 'place_new_order.php') ? 'active' : '' ?>"><i
                            class="fa fa-plus me-2"></i>Order Manually</a>
                    <a href="orders.php"
                        class="nav-item nav-link <?= ($currentPage == 'orders.php') ? 'active' : '' ?>"><i
                            class="fa fa-shopping-cart me-2"></i>Orders</a>

                    <a href="products.php"
                        class="nav-item nav-link <?= ($currentPage == 'products.php') ? 'active' : '' ?>"><i
                            class="fa fa-box-open me-2"></i>Products</a>
                    <a href="client.php"
                        class="nav-item nav-link <?= ($currentPage == 'client.php') ? 'active' : '' ?>"><i
                            class="fa fa-user me-2"></i>My Profile</a>


                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i
                                class="fa fa-user me-2"></i> My Account</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="change_password.php" class="nav-item nav-link"><i class="fa fa-key me-1"></i>Change
                                Password</a>
                            <a href="../logout.php" class="nav-item nav-link"><i
                                    class="fas fa-sign-out-alt me-1"></i>Logout</a>

                            <!-- <a href="button.html" class="dropdown-item">Change Password</a> -->

                        </div>
                    </div>

                </div>
            </nav>
        </div>
        <!-- Sidebar End -->


        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="index.php" class="navbar-brand d-flex d-lg-none me-4">
                    <img src="../img/logo.png" alt="" height="30px" width="30px">
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <!-- <script>
                window.onload = function() {
                    document.querySelector('.sidebar-toggler').click();
                };
                </script> -->

                <div class="d-flex align-items-center ms-3">
                    <span class="fw-bold text-dark">Welcome, <?php echo strtoupper($result['username']);?></span>
                </div>

                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <span class="d-none d-lg-inline-flex">Logged In As &nbsp; <strong> Client</strong></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">

                            <a href="../logout.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Navbar End -->