<?php
include 'config.php';

header('Content-Type: application/json');

// update-order-status.php
// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['orderId']) && isset($data['newStatus'])) {
    $orderId = (int)$data['orderId']; // Cast to integer for safety
    $newStatus = mysqli_real_escape_string($conn, $data['newStatus']); // Sanitize

    // Validate OrderID (check if it's a positive integer)
    if ($orderId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid OrderID']);
        exit;
    }

    // Validate newStatus (check if it's one of the allowed statuses)
    $allowedStatuses = ['Pending', 'Shipped', 'Complete'];
    if (!in_array($newStatus, $allowedStatuses)) {
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        exit;
    }

    // Update the status in the database using prepared statement
    $sql = "UPDATE order_details SET Status = ? WHERE OrderID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newStatus, $orderId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]); //Use stmt->error
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Missing orderId or newStatus']);
}

$conn->close();
?>