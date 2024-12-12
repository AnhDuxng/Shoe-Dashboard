<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/style.css" />
</head>

<body>
    <div class="top">
        <button id="menu-btn">
            <span class="material-icons-sharp"> menu </span>
        </button>
        <div class="theme-toggler">
            <img src="./images/sun.png" alt="" />
            <img src="./images/moon.png" alt="" />
        </div>
        <div class="profile">
            <div class="info">
                <p>Hey, <b>Bruno</b></p>
                <small class="text-muted">Admin</small>
            </div>
            <div class="profile-photo">
                <img src="./images/profile-1.jpg" alt="Profile Picture" />
            </div>
        </div>
    </div>

    <div class="recent-updates">
        <h2>Recent Updates</h2>
        <!-- Add updates div here | JS insertion -->
    </div>

    <div class="sales-analytics">
        <h2>Sales Analytics</h2>
        <div id="analytics">
            <!-- Add items div here | JS insertion -->
        </div>
        <div class="item add-product">
            <div>
                <span class="material-icons-sharp"> add </span>
                <h3>Add Product</h3>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="./constants/recent-order-data.js"></script>
    <script src="./constants/update-data.js"></script>
    <script src="./constants/sales-analytics-data.js"></script>
    <script src="./index.js"></script>
</body>

</html>