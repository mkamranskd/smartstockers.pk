<?php
require_once '../php_action/db_connect.php';

// Ensure DB is connected
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Query to get delivered orders in the last 7 days
$sql = "
    SELECT DATE(updated_at) as date, COUNT(*) as count
    FROM orders
    WHERE status = 4 AND updated_at >= CURDATE() - INTERVAL 7 DAY
    GROUP BY DATE(updated_at)
    ORDER BY DATE(updated_at) ASC
";

$result = $connect->query($sql);

$data = [];

// Pre-fill last 7 days to ensure all days are present (even with 0 orders)
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $data[$day] = 0;
}

// Fill in actual counts
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[$row['date']] = (int)$row['count'];
    }
}

// Output JSON for the chart
header('Content-Type: application/json');
echo json_encode($data);
?>