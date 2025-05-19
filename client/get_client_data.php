<?php
require_once '../php_action/db_connect.php';

$response = ['status' => 'error', 'message' => 'Invalid Request'];

// Default empty structure for Add Client
$defaultClient = [
    'client_id' => '',
    'vendor_name' => '',
    'phone_number' => '',
    'email' => '',
    'cnic' => '',
    'rate_of_product' => '',
    'address' => ''
];
$defaultDeliveries = [];
$defaultStores = [];

if (isset($_GET['client_id']) && !empty($_GET['client_id'])) {
    $client_id = $_GET['client_id'];

    $stmt = $connect->prepare("SELECT * FROM clients WHERE client_id = ?");
    $stmt->bind_param("s", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($client = $result->fetch_assoc()) {
        // Get delivery services
        $delivery_stmt = $connect->prepare("SELECT * FROM vendor_delivery_services WHERE client_id = ?");
        $delivery_stmt->bind_param("s", $client_id);
        $delivery_stmt->execute();
        $delivery_result = $delivery_stmt->get_result();
        $deliveries = $delivery_result->fetch_all(MYSQLI_ASSOC);

        // Get vendor stores
        $store_stmt = $connect->prepare("SELECT * FROM vendor_stores WHERE client_id = ?");
        $store_stmt->bind_param("s", $client_id);
        $store_stmt->execute();
        $store_result = $store_stmt->get_result();
        $stores = $store_result->fetch_all(MYSQLI_ASSOC);

        $response = [
            'status' => 'success',
            'client' => $client,
            'delivery_services' => $deliveries,
            'stores' => $stores
        ];
    } else {
        $response['message'] = 'Client not found';
    }
} else {
    // Return blank data for Add Client mode
    $response = [
        'status' => 'success',
        'client' => $defaultClient,
        'delivery_services' => $defaultDeliveries,
        'stores' => $defaultStores
    ];
}

header('Content-Type: application/json');
echo json_encode($response);