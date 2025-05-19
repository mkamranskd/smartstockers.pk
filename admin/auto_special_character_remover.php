<?php
require_once '../php_action/db_connect.php';

if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

$table = 'orders';
$idCol = 'id'; // Replace with your actual primary key if different
$targetColumns = ['billing_address', 'delivery_address', 'shipping_address'];

// Step 1: Get only the target columns for each row
$sql = "SELECT `$idCol`, " . implode(', ', array_map(function($col) {
    return "`$col`";
}, $targetColumns)) . " FROM `$table`";

$data = $connect->query($sql);

if (!$data) {
    die("Error fetching data: " . $connect->error);
}

while ($row = $data->fetch_assoc()) {
    $id = $row[$idCol];
    $updates = [];

    foreach ($targetColumns as $col) {
        $original = $row[$col];
        $cleaned = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $original); // Replace special chars with space
        $cleaned = preg_replace('/\s+/', ' ', $cleaned); // Collapse multiple spaces
        $cleaned = trim($cleaned); // Remove leading/trailing spaces

        if ($original !== $cleaned) {
            $escaped = $connect->real_escape_string($cleaned);
            $updates[] = "`$col` = '$escaped'";
        }
    }

    if (!empty($updates)) {
        $updateSql = "UPDATE `$table` SET " . implode(', ', $updates) . " WHERE `$idCol` = '$id'";
        if ($connect->query($updateSql)) {
            echo "Updated row ID $id\n";
        } else {
            echo "Error updating row ID $id: " . $connect->error . "\n";
        }
    }
}

echo "\n\n Removed Special Symbols in billing_address', 'delivery_address', 'shipping_address' on Orders Table.\n";
$connect->close();
?>