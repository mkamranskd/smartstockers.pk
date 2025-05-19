<?php
function getOrderStatus($statusId) {
    $statuses = [
        0 => "New Order Placed",
        1 => "Confirmed",
        2 => "Ready to Shipped",
        3 => "Shipped",
        4 => "Delivered",
        5 => "Pending",
        6 => "Cancelled/Returned",
        7 => "Ready to Shipped",


    ];
    
    return $statuses[$statusId] ?? "Unknown"; 
}
function getOrderStatusBadge($statusId) {
    $statuses = [
        0 => ["label" => "New Order Placed", "class" => "badge bg-warning"],
        1 => ["label" => "Confirmed", "class" => "badge bg-primary"],
        2 => ["label" => "Ready to Shipped", "class" => "badge bg-success"],
        3 => ["label" => "Shipped", "class" => "badge bg-danger"],
        4 => ["label" => "Delivered", "class" => "badge bg-secondary"],
        5 => ["label" => "Pending", "class" => "badge bg-info"],
        6 => ["label" => "Cancelled/Returned", "class" => "badge bg-info"],
        7 => ["label" => "Ready to Shipped", "class" => "badge bg-success"]
    ];

    $status = $statuses[$statusId] ?? ["label" => "Unknown", "class" => "badge bg-dark"];
    return "<span class='{$status['class']}'>{$status['label']}</span>";
}
?>