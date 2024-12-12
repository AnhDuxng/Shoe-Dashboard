<?php
require_once '../config.php';

header('Content-Type: application/json');

$phone = $_GET['phone'];

if (empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Phone number is required']);
    exit();
}

$query = "SELECT * FROM person WHERE PhoneNumber = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
    exit();
}

$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $customer = $result->fetch_assoc();
    echo json_encode(['success' => true, 'customer' => $customer]);
} else {
    echo json_encode(['success' => false, 'message' => 'Customer not found']);
}

$stmt->close();
$conn->close();
?>
