<?php
require_once '../php_action/db_connect.php';
require_once 'includes/header.php';

?>


<!-- Clients Table Column -->
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

                                Reports

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
        <!-- Sale & Revenue Start -->
        <div class="container-fluid pt-4 px-4">
            <p class="h5 mb-4">Reports</p>
            <div class="row g-4">
                <!-- Delivered Orders Report -->
                <div class="col-sm-6 col-xl-4">
                    <a href="delivered_order_report_by_ss.php">
                        <div class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                            <i class="fa fa-clipboard-list fa-2x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Delivered Orders Report</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Stock Invoice -->
                <div class="col-sm-6 col-xl-4">
                    <a href="stock_invoice.php">
                        <div class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                            <i class="fa fa-clipboard-list fa-2x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Stock Invoice</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Inventory & Shipments Report -->
                <div class="col-sm-6 col-xl-4">
                    <a href="inventory_and_shipments_report.php">
                        <div class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                            <i class="fa fa-archive fa-2x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Inventory and Shipments Report</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <br>
    </div>
</div>




<?php require_once 'includes/footer.php'; ?>