<?php
@include 'config.php';
session_start();

// Check if the admin session exists; otherwise, redirect to login page
if (!isset($_SESSION['admin_name'])) {
    header('location:login_form.php');
}

// Get OrderID from the URL
$orderId = $_GET['id'];
$ngay = date("d");
$thang = date("m");
$nam = date("Y");

// Fetch customer information
$sqlCustomer = "SELECT od.CustomerID, od.Name, p.PhoneNo, p.Address 
                FROM order_details od 
                JOIN person p ON od.CustomerID = p.ID 
                WHERE od.OrderID = $orderId";

$resultCustomer = $conn->query($sqlCustomer);
if ($resultCustomer->num_rows > 0) {
    $customerData = $resultCustomer->fetch_assoc();
    $customerName = $customerData['Name'];
    $customerPhone = $customerData['PhoneNo'];
    $customerAddress = $customerData['Address'];
} else {
    $customerName = 'Unknown';
    $customerPhone = 'Not available';
    $customerAddress = 'Not available';
}
?>

<title>Order Details</title>
<div class="row">
    <div class="col-lg-2">
        <?php include_once 'admin_header.php'; ?>
    </div>
    <div class="col-lg-10">
        <div id="container1">
            <br>
            <h1>Hóa đơn <?php echo $orderId ?></h1>
            <br>
            <div class="phieu" style="border:1px solid black">
                <div class="title" style="display:flex;justify-content: center;">
                    <h2 style="color: red;">HÓA ĐƠN</h2>
                </div>
                <div class="day" style="display:flex;justify-content: center;">
                    <?php echo 'Ngày ' . $ngay . ' Tháng ' . $thang . ' Năm ' . $nam ?>
                </div>
                <div class="num" style="display:flex;justify-content: center;">Hóa đơn số: <?php echo $orderId ?></div>
                <div class="ten" style="display:flex;justify-content: center;">Họ tên Khách hàng: 
                    <?php echo $customerName; ?>
                </div>
                <div class="customer-info">
                    <div class="address" style="display:flex;justify-content: center;">
                        Địa chỉ Khách hàng: <?php echo $customerAddress; ?>
                    </div>
                    <div class="phone" style="display:flex;justify-content: center;">
                        Số điện thoại Khách hàng: <?php echo $customerPhone; ?>
                    </div>
                    <div class="employee" style="display:flex;justify-content: center;">
                        Nhân viên tạo hóa đơn: 
                        <?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : $_SESSION['employ_name']; ?>
                    </div>
                </div>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col">STT</th>
                        <th scope="col">TÊN SẢN PHẨM</th>
                        <th scope="col">SỐ LƯỢNG</th>
                        <th scope="col">THÀNH TIỀN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $tong = 0;
                    // Fetch order details (products) for this order ID
                    $sql = "SELECT od.ModelName, od.Quantity, od.TotalPrice, s.UnitPrice 
                            FROM order_details od
                            JOIN shoe s ON od.ModelName = s.ModelName
                            WHERE od.OrderID = $orderId";

                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $productName = $row['ModelName'];
                            $quantity = $row['Quantity'];
                            $totalPrice = $row['TotalPrice'];
                            $unitPrice = $row['UnitPrice'];  // You can use this if needed for additional calculations

                            $tong += $totalPrice;  // Accumulate total price
                            echo '
                            <tr>
                                <th scope="row">' . $i++ . '</th>
                                <td>' . $productName . '</td>
                                <td>' . $quantity . '</td>
                                <td>' . number_format($totalPrice, 2) . '</td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2"></th>
                        <th>TỔNG SỐ LƯỢNG</th>
                        <th><?php echo $i - 1; ?></th>
                    </tr>
                    <tr>
                        <th colspan="2"></th>
                        <th>TỔNG TIỀN</th>
                        <th><?php echo number_format($tong, 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
