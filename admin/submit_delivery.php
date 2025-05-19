<?php
require_once '../php_action/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];
    $deliveryServiceId = $_POST['delivery_service'];
    $trackingId = trim($_POST['tracking_id']);

    // Get service name from DB using delivery service ID
    $stmt = $connect->prepare("SELECT service_name FROM delivery_services_list WHERE id = ?");
    $stmt->bind_param("i", $deliveryServiceId);
    $stmt->execute();
    $stmt->bind_result($serviceName);
    $stmt->fetch();
    $stmt->close();

    if (!$serviceName) {
        die("Invalid delivery service selected.");
    }

    // Format: ServiceName-TrackingId
    $combinedTracking = $serviceName . '-' . $trackingId;

    // Update the order_tracking_id
    $update = $connect->prepare("UPDATE orders SET order_tracking_id = ?, status = 7 WHERE order_id = ?");
    $update->bind_param("ss", $combinedTracking, $orderId);

    if ($update->execute()) {
        // Send a success response
        echo "<script>window.location.href = 'orders.php'</script>";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating order tracking info.']);
    }

    $update->close();
    $connect->close();
}
?>