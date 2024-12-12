<?php
// Start the session
session_start();

// Include the database connection
include 'config.php';

// Fetch the necessary data for the dashboard
$total_sales_query = "SELECT SUM(TotalPrice) AS TotalSales FROM order_details WHERE Status = 'completed'";
$total_sales_result = mysqli_query($conn, $total_sales_query);
$total_sales_data = mysqli_fetch_assoc($total_sales_result);
$total_sales = $total_sales_data['TotalSales'] ?? 0;

// $total_expenses_query = "SELECT SUM(ExpenseAmount) AS TotalExpenses FROM expenses"; // Assuming you have an expenses table
// $total_expenses_result = mysqli_query($conn, $total_expenses_query);
// $total_expenses_data = mysqli_fetch_assoc($total_expenses_result);
// $total_expenses = $total_expenses_data['TotalExpenses'] ?? 0;

// $total_income_query = "SELECT SUM(IncomeAmount) AS TotalIncome FROM income"; // Assuming you have an income table
// $total_income_result = mysqli_query($conn, $total_income_query);
// $total_income_data = mysqli_fetch_assoc($total_income_result);
// $total_income = $total_income_data['TotalIncome'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main>
            <h1>Dashboard</h1>
            <div class="date">
                <input type="date">
            </div>

            <div class="insights">
                <div class="sales">
                    <img src="./images/analysis.png" alt="" />
                    <div class="middle">
                        <div class="left">
                            <h3>Total Sales</h3>
                            <h1>$<?php echo number_format($total_sales, 2); ?></h1>
                        </div>
                        <div class="progress">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="number">
                                <p>81%</p>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">Last 24 hours</small>
                </div>

                <div class="expenses">
                    <img src="./images/analysis.png" alt="" />
                    <div class="middle">
                        <div class="left">
                            <h3>Total Expenses</h3>
                            <h1>2000$</h1>
                        </div>
                        <div class="progress">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="number">
                                <p>62%</p>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">Last 24 hours</small>
                </div>

                <div class="income">
                    <img src="./images/analysis.png" alt="" />
                    <div class="middle">
                        <div class="left">
                            <h3>Total Income</h3>
                            <h1>1291$</h1>
                        </div>
                        <div class="progress">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="number">
                                <p>44%</p>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">Last 24 hours</small>
                </div>
            </div>

            <div class="recent-orders">
                <h2>Recent Orders</h2>
                <table id="recent-orders--table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Product Number</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Fetch recent orders
                    $recent_orders_query = "SELECT ModelName, OrderID, PaymentMethod, Status FROM order_details ORDER BY OrderID DESC LIMIT 5";
                    $recent_orders_result = mysqli_query($conn, $recent_orders_query);

                    if (mysqli_num_rows($recent_orders_result) > 0) {
                        while ($order = mysqli_fetch_assoc($recent_orders_result)) {
                            echo "<tr>
                                    <td>{$order['ModelName']}</td>
                                    <td>{$order['OrderID']}</td>
                                    <td>{$order['PaymentMethod']}</td>
                                    <td>{$order['Status']}</td>
                                    <td><a href='#'>View</a></td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No recent orders</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <a href="#">Show All</a>
            </div>
        </main>

        <!-- Right Panel -->
        <div class="right">
            <?php include 'sub-sidebar.php'; ?>
        </div>
    </div>
</body>
</html>
