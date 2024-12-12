<?php
require_once '../config.php';

$query = "SELECT ModelName, UnitPrice, Discount FROM shoe";
$result = $conn->query($query);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

header('Content-Type: application/json');
echo json_encode($products);
?>
