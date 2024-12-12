<?php
// Get the current file name to identify the active page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside>
    <div class="top">
        <a href="index.php">
            <div class="logo">
                <img src="./images/logo.png" alt="Logo" />
                <h2>SHOE<span class="danger">STORE</span></h2> 
            </div>
        </a>
        <div class="close" id="close-btn">
            <span class="material-icons-sharp"> close </span>
        </div>
    </div>

    <div class="sidebar">
        <!-- Add the "active" class conditionally based on the current page -->
        <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <img src="./images/dashboard.png" alt="" />
            <h3>Dashboard</h3>
        </a>
        <a href="customer.php" class="<?php echo ($current_page == 'customer.php') ? 'active' : ''; ?>">
            <img src="./images/customer.png" alt="" />
            <h3>Customers</h3>
        </a>
        <a href="orders.php" class="<?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">
            <img src="./images/order.png" alt="" />
            <h3>Create a Order</h3>
        </a>
        <a href="all-orders.php" class="<?php echo ($current_page == 'all-orders.php') ? 'active' : ''; ?>">
            <img src="./images/analysis.png" alt="" />
            <h3>All Orders</h3>
        </a>
        <a href="messages.php" class="<?php echo ($current_page == 'messages.php') ? 'active' : ''; ?>">
            <img src="./images/dashboard.png" alt="" />
            <h3>Messages</h3>
            <span class="message-count">26</span>
        </a>
        <a href="products.php" class="<?php echo ($current_page == 'products.php') ? 'active' : ''; ?>">
            <img src="./images/box.png" alt="" />
            <h3>Products</h3>
        </a>
        <a href="staffs.php" class="<?php echo ($current_page == 'staffs.php') ? 'active' : ''; ?>">
            <img src="./images/dashboard.png" alt="" />
            <h3>Staffs Management</h3>
        </a>
        <a href="logout.php">
            <img src="./images/dashboard.png" alt="" />
            <h3>Logout</h3>
        </a>
    </div>
</aside>
