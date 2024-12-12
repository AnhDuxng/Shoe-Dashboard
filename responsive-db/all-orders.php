<?php
include 'config.php';

// Query to get the total number of customers
$sqlCustomers = "SELECT COUNT(*) AS total FROM person";
$resultCustomers = $conn->query($sqlCustomers);
if ($resultCustomers->num_rows > 0) {
    $customerData = $resultCustomers->fetch_assoc();
    $total_customers = $customerData['total'];
} else {
    $total_customers = 0;
}

// Query to get the total number of orders
$sqlOrders = "SELECT COUNT(*) AS total_orders FROM order_details";
$resultOrders = $conn->query($sqlOrders);
if ($resultOrders->num_rows > 0) {
    $ordersData = $resultOrders->fetch_assoc();
    $total_orders = $ordersData['total_orders'];
} else {
    $total_orders = 0;
}

// Query to get the total price of all orders
$sqlTotalPrice = "SELECT SUM(TotalPrice) AS total_price FROM order_details";
$resultTotalPrice = $conn->query($sqlTotalPrice);
if ($resultTotalPrice->num_rows > 0) {
    $priceData = $resultTotalPrice->fetch_assoc();
    $total_price = $priceData['total_price'];
} else {
    $total_price = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Orders</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/style.css" />
    <link rel="stylesheet" href="./styles/all-orders.css" />
    <script src="./constants/customer.js" defer></script>
    <script src="./constants/products.js" defer></script>
    <script>
        // JavaScript to handle AJAX status update
        document.addEventListener("DOMContentLoaded", () => {
            const statusSelects = document.querySelectorAll(".status-select");

            statusSelects.forEach(select => {
                select.addEventListener("change", async (event) => {
                    const orderId = event.target.dataset.orderId;
                    const newStatus = event.target.value;

                    try {
                        const response = await fetch("api/update-order-status.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({ orderId, newStatus }),
                        });

                        if (response.ok) {
                            const data = await response.json();
                            if (data.success) {
                                alert("Status updated successfully!");
                            } else {
                                alert("Failed to update status. Please try again.");
                            }
                        } else {
                            throw new Error("Network response was not ok");
                        }
                    } catch (error) {
                        console.error("Error updating status:", error);
                        alert("An error occurred while updating the status.");
                    }
                });
            });
        });
    </script>
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>

        <main>
            <div class="row">
                <div class="col-lg-10">
                    <div id="container1">
                        <br>
                        <h1 style="color:black;">ALL ORDERS</h1>
                        <br>

                        <!-- Customer Summary Section -->
                        <div class="customer-summary">
                            <div class="summary-card">
                                <h2>Total Orders</h2>
                                <h1><?php echo $total_orders; ?></h1>
                                <span class="percentage-change up">+20%</span>
                            </div>
                            <div class="summary-card">
                                <h2>Total Revenue</h2>
                                <h1><?php echo number_format($total_price, 2); ?> USD</h1>
                                <span class="percentage-change up">+15%</span>
                            </div>
                            <div class="summary-card">
                                <h2>Total Customers</h2>
                                <h1><?php echo $total_customers; ?></h1>
                            </div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col" style="background-color: #338ec9; color: white;">Order ID</th>
                                    <th scope="col" style="background-color: #338ec9; color: white;">Customer Name</th>
                                    <th scope="col" style="background-color: #338ec9; color: white;">Phone Number</th>
                                    <th scope="col" style="background-color: #338ec9; color: white;">Products</th>
                                    <th scope="col" style="background-color: #338ec9; color: white;">Quantity</th>
                                    <th scope="col" style="background-color: #338ec9; color: white;">Total Price</th>
                                    <th scope="col" style="background-color: #338ec9; color: white;">Status</th>
                                    <th scope="col" style="background-color: #338ec9; color: white;">Payment Method</th>
                                    <th scope="col" style="background-color: #338ec9; color: white;">Store</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // SQL query to get the order details and customer info
                                $sql = "SELECT od.OrderID, od.CustomerID, od.Name, od.PhoneNo, od.ModelName, od.Quantity, od.Discount, 
                                               od.Price, od.TotalPrice, od.Status, od.PaymentMethod, od.StoreCity 
                                        FROM order_details od
                                        INNER JOIN person p ON od.CustomerID = p.ID";

                                $result = $conn->query($sql);
                                $i = 1;

                                // Check if any records are found
                                if ($result->num_rows > 0) {
                                    // Loop through each row and display order details
                                    while ($row = $result->fetch_assoc()) {
                                        $OrderID = $row['OrderID'];
                                        $CustomerName = $row['Name'];
                                        $PhoneNo = $row['PhoneNo'];
                                        $ModelName = $row['ModelName'];
                                        $Quantity = $row['Quantity'];
                                        $TotalPrice = $row['TotalPrice'];
                                        $Status = $row['Status'];
                                        $PaymentMethod = $row['PaymentMethod'];
                                        $StoreCity = $row['StoreCity'];
                                        echo '
                                        <tr>
                                            <td>' . $OrderID . '</td>
                                            <td>' . $CustomerName . '</td>
                                            <td>' . $PhoneNo . '</td>
                                            <td>' . $ModelName . '</td>
                                            <td>' . $Quantity . '</td>
                                            <td>' . number_format($TotalPrice, 2) . '</td>
                                            <td>
                                                <select class="status-select" data-order-id="' . $OrderID . '">
                                                    <option value="Pending" ' . ($Status === 'Pending' ? 'selected' : '') . '>Pending</option>
                                                    <option value="Shipped" ' . ($Status === 'Shipped' ? 'selected' : '') . '>Shipped</option>
                                                    <option value="Complete" ' . ($Status === 'Complete' ? 'selected' : '') . '>Complete</option>
                                                </select>
                                            </td>
                                            <td>' . $PaymentMethod . '</td>
                                            <td>' . $StoreCity . '</td>
                                        </tr>
                                        ';
                                    }
                                } else {
                                    echo '<tr><td colspan="11" style="text-align: center;">No orders found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <div class="right">
            <?php include 'sub-sidebar.php'; ?>
        </div>
    </div>
</body>

</html>
