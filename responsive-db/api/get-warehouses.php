<?php
require_once '../config.php';

$query = "SELECT StoreAddress, City FROM store";
$result = $conn->query($query);

$warehouses = [];
while ($row = $result->fetch_assoc()) {
    $warehouses[] = $row;
}

header('Content-Type: application/json');
echo json_encode($warehouses);
?>
