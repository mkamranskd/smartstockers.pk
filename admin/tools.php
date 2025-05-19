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

                                Tools

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
            <p class="h5 mb-4">Refresh Stores</p>
            <div class="row g-4">
                <!-- Delivered Orders Report -->

                <!-- Stock Invoice -->
                <div class="col-sm-6 col-xl-4">
                    <a href="fetch_all_order_from_all_woocommerce_stores.php" target="">
                        <div class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">
                            <div class="ms-3">
                                <p class="mb-2">Woocomerce</p>
                            </div>
                            <img src="../img/woo.png" class="img-fluid" style=" width: 100px;"
                                alt="Clipboard List Icon">

                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <a href="" target="">
                        <div class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">

                            <div class="ms-3">
                                <p class="mb-2">Shopify</p>
                            </div>
                            <img src="../img/shopify.png" class="img-fluid" style=" width: 100px;"
                                alt="Clipboard List Icon">
                        </div>
                    </a>
                </div>

            </div>
        </div>
        <div class="container-fluid pt-4 px-4">
            <p class="h5 mb-4">Refresh Delivery Services</p>
            <div class="row g-4">
                <!-- Delivered Orders Report -->
                <div class="col-sm-6 col-xl-4">
                    <a href="fetch_all_order_from_all_postex.php" target="_blank">
                        <div class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">

                            <div class="ms-3">
                                <p class="mb-2">Postex</p>
                            </div>
                            <img src="../img/postEx.png" class="img-fluid" style=" width: 100px;"
                                alt="Clipboard List Icon">
                        </div>
                    </a>
                </div>

                <!-- Stock Invoice -->
                <div class="col-sm-6 col-xl-4">
                    <a href="" target="">
                        <div class="bg-white rounded d-flex align-items-center justify-content-between p-4 zoom-hover">

                            <div class="ms-3">
                                <p class="mb-2">Leopards</p>
                            </div>
                            <img src="../img/lcs.png" class="img-fluid" style=" width: 100px;"
                                alt="Clipboard List Icon">
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>




<?php require_once 'includes/footer.php'; ?>