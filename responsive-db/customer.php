<?php
// Start the session at the beginning
session_start();

// Include the database connection
include 'config.php';

// Handle search and filters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_option = isset($_GET['filter']) ? $_GET['filter'] : 'asc'; // Default to 'asc' for alphabetical order

// Pagination logic
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Base query
$query = "
    SELECT 
        ID, 
        CONCAT(Fname, ' ', Lname) AS FullName, 
        CONCAT(Ward,' , ', District, ' , ', City) AS Address, 
        Fname,
        Lname,
        PhoneNo,  
        Email,
        Sex,
        Ward,
        District,
        City 
    FROM person";

// Apply search filter
if ($search_query) {
    $query .= " WHERE CONCAT(Fname, ' ', Lname) LIKE '%" . mysqli_real_escape_string($conn, $search_query) . "%'";
}

// Apply alphabetical filter (A-Z or Z-A)
$query .= " ORDER BY FullName $filter_option";

// Apply pagination limit and offset
$query .= " LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query) or die("Query Failed: " . mysqli_error($conn));

// Fetch total customers for pagination
$total_query = "SELECT COUNT(*) AS total FROM person";
$total_result = mysqli_query($conn, $total_query) or die("Total Query Failed: " . mysqli_error($conn));
$total_customers = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_customers / $limit);

// EDIT CUSTOMER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_customer'])) {
        $id = $_POST['ID'];
        $fname = mysqli_real_escape_string($conn, $_POST['Fname']);
        $lname = mysqli_real_escape_string($conn, $_POST['Lname']);
        $sex = mysqli_real_escape_string($conn, $_POST['Sex']);
        $phone = mysqli_real_escape_string($conn, $_POST['PhoneNo']);
        $email = mysqli_real_escape_string($conn, $_POST['Email']);
        $ward = mysqli_real_escape_string($conn, $_POST['Ward']);
        $district = mysqli_real_escape_string($conn, $_POST['District']);
        $city = mysqli_real_escape_string($conn, $_POST['City']);

        $update_query = "
            UPDATE person
            SET 
                Fname = '$fname', 
                Lname = '$lname', 
                Sex = '$sex', 
                PhoneNo = '$phone', 
                Email = '$email', 
                Ward = '$ward', 
                District = '$district', 
                City = '$city'
            WHERE ID = $id";

        if (mysqli_query($conn, $update_query)) {
            $_SESSION['message'] = 'Customer updated successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to update customer.';
            $_SESSION['message_type'] = 'error';
        }
    }

    // DELETE CUSTOMER
    if (isset($_POST['delete_customer'])) {
        $id = $_POST['ID'];

        $delete_query = "DELETE FROM person WHERE ID = $id";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['message'] = 'Customer deleted successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to delete customer.';
            $_SESSION['message_type'] = 'error';
        }
    }

    // ADD CUSTOMER
    if (isset($_POST['add_customer'])) {
        // Retrieve form data
        $fname = mysqli_real_escape_string($conn, $_POST['Fname']);
        $lname = mysqli_real_escape_string($conn, $_POST['Lname']);
        $sex = mysqli_real_escape_string($conn, $_POST['Sex']);
        $phone = mysqli_real_escape_string($conn, $_POST['PhoneNo']);
        $email = mysqli_real_escape_string($conn, $_POST['Email']);
        $ward = mysqli_real_escape_string($conn, $_POST['Ward']);
        $district = mysqli_real_escape_string($conn, $_POST['District']);
        $city = mysqli_real_escape_string($conn, $_POST['City']);

        // Insert data into the `person` table
        $query = "INSERT INTO person (Fname, Lname, Sex, PhoneNo, Email, Ward, District, City) 
                  VALUES ('$fname', '$lname', '$sex', '$phone', '$email', '$ward', '$district', '$city')";
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'Customer added successfully';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to add customer';
            $_SESSION['message_type'] = 'error';
        }
    }
}

// Display the session message using an alert
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type']; // success or error
    echo "<script>alert('$message');</script>";
    unset($_SESSION['message']); // Clear the message
    unset($_SESSION['message_type']); // Clear the message type
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer Management</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/style.css" />
    <link rel="stylesheet" href="./styles/customer.css" />
    <script src="./constants/customer.js" defer></script>
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>

        <main>
            <h1>Customers</h1>
            <div class="add-product">
                <button class="add-btn" onclick="toggleModal('addCustomerModal')">+ Add Customer</button>
            </div>
            <!-- Add Customer Section -->
            <div id="addCustomerModal" class="modal hidden">
                <div class="modal-content">
                    <span class="close" onclick="toggleModal('addCustomerModal')">&times;</span>
                    <h2>Add New Customer</h2>
                    <form action="" method="POST">
                        <!-- Hidden field to identify the add customer form -->
                        <input type="hidden" name="add_customer" value="true" />

                        <label for="Fname">First Name</label>
                        <input type="text" name="Fname" required />

                        <label for="Lname">Last Name</label>
                        <input type="text" name="Lname" required />

                        <label for="Sex">Gender</label>
                        <input type="text" name="Sex" list="gender-options" required
                            placeholder="Type or select gender" />
                        <datalist id="gender-options">
                            <option value="Male"></option>
                            <option value="Female"></option>
                            <option value="Other"></option>
                        </datalist>

                        <label for="PhoneNo">Phone Number</label>
                        <input type="text" name="PhoneNo" required />

                        <label for="Email">Email</label>
                        <input type="email" name="Email" />

                        <label for="Ward">Ward</label>
                        <input type="text" name="Ward" required />

                        <label for="District">District</label>
                        <input type="text" name="District" required />

                        <label for="City">City</label>
                        <input type="text" name="City" required />

                        <button type="submit">Add new</button>
                    </form>
                </div>
            </div>


            <!-- Customer Summary Section -->
            <div class="customer-summary">
                <div class="summary-card">
                    <h2>Total Customers</h2>
                    <h1><?php echo $total_customers; ?></h1>
                    <span class="percentage-change up">+20%</span>
                </div>
                <div class="summary-card">
                    <h2>Members</h2>
                    <h1>1,210</h1>
                    <span class="percentage-change up">+15%</span>
                </div>
                <div class="summary-card">
                    <h2>Active Now</h2>
                    <h1>316</h1>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-product">
                <!-- Alphabetical filter for customer names -->
                <select class="filter-select" onchange="updateQueryString('filter', this.value)">
                    <option value="asc" <?php if ($filter_option == 'asc') echo 'selected'; ?>>A to Z</option>
                    <option value="desc" <?php if ($filter_option == 'desc') echo 'selected'; ?>>Z to A</option>
                </select>

                <!-- Search field to filter customers by name -->
                <div class="search-field">
                    <form method="get" action="">
                        <input type="text" name="search" placeholder="Search for customers..."
                            value="<?php echo htmlspecialchars($search_query); ?>" />
                        <button type="submit">Search</button>
                    </form>
                </div>
            </div>

            <!-- Customer Table -->
            <table class="customer-table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Initialize a counter for the numbering
                    $row_number = $offset + 1; 
                    while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row_number++; ?></td>
                        <td><?php echo htmlspecialchars($row['FullName']); ?></td>
                        <td><?php echo htmlspecialchars($row['Address']); ?></td>
                        <td><?php echo htmlspecialchars($row['PhoneNo']); ?></td>
                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                        <td>
                            <!-- Edit Button -->
                            <button class="edit-btn"
                                onclick="toggleModal('editCustomerModal-<?php echo $row['ID']; ?>')">Edit</button>
                            <!-- Delete Button -->
                            <button class="delete-btn"
                                onclick="toggleModal('deleteCustomerModal-<?php echo $row['ID']; ?>')">Delete</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <button <?php if ($page <= 1) echo 'disabled'; ?> class="btn-prev">
                    <a
                        href="?page=<?php echo max(1, $page - 1); ?>&search=<?php echo urlencode($search_query); ?>&filter=<?php echo $filter_option; ?>">Previous</a>
                </button>
                <h2>Page <?php echo $page; ?> of <?php echo $total_pages; ?></h2>
                <button <?php if ($page >= $total_pages) echo 'disabled'; ?> class="btn-next">
                    <a
                        href="?page=<?php echo min($total_pages, $page + 1); ?>&search=<?php echo urlencode($search_query); ?>&filter=<?php echo $filter_option; ?>">Next</a>
                </button>
            </div>

        </main>
        <!-- Modals: Defined once outside of the table -->
        <?php 
        // Reset the result pointer for modals
        mysqli_data_seek($result, 0); 
        while ($row = mysqli_fetch_assoc($result)): ?>
        <!-- Edit Customer Modal -->
        <div id="editCustomerModal-<?php echo $row['ID']; ?>" class="modal hidden">
            <div class="modal-content">
                <span class="close" onclick="toggleModal('editCustomerModal-<?php echo $row['ID']; ?>')">&times;</span>
                <h2>Edit Customer</h2>
                <form method="POST" action="">
                    <input type="hidden" name="edit_customer" value="1" />
                    <input type="hidden" name="ID" value="<?php echo $row['ID']; ?>" />

                    <label for="Fname">First Name</label>
                    <input type="text" name="Fname" value="<?php echo htmlspecialchars($row['Fname']); ?>" required />

                    <label for="Lname">Last Name</label>
                    <input type="text" name="Lname" value="<?php echo htmlspecialchars($row['Lname']); ?>" required />

                    <label for="Sex">Gender</label>
                    <input type="text" name="Sex" list="gender-options"
                        value="<?php echo htmlspecialchars($row['Sex']); ?>" required />
                    <datalist id="gender-options">
                        <option value="Male"></option>
                        <option value="Female"></option>
                        <option value="Other"></option>
                    </datalist>

                    <label for="PhoneNo">Phone Number</label>
                    <input type="text" name="PhoneNo" value="<?php echo htmlspecialchars($row['PhoneNo']); ?>"
                        required />

                    <label for="Email">Email</label>
                    <input type="email" name="Email" value="<?php echo htmlspecialchars($row['Email']); ?>" />

                    <label for="Ward">Ward</label>
                    <input type="text" name="Ward" value="<?php echo htmlspecialchars($row['Ward']); ?>" required />

                    <label for="District">District</label>
                    <input type="text" name="District" value="<?php echo htmlspecialchars($row['District']); ?>"
                        required />

                    <label for="City">City</label>
                    <input type="text" name="City" value="<?php echo htmlspecialchars($row['City']); ?>" required />

                    <button type="submit">Save Changes</button>
                </form>

            </div>
        </div>

        <!-- Delete Customer Modal -->
        <div id="deleteCustomerModal-<?php echo $row['ID']; ?>" class="modal hidden">
            <div class="modal-content">
                <span class="close"
                    onclick="toggleModal('deleteCustomerModal-<?php echo $row['ID']; ?>')">&times;</span>
                <h2>Delete Customer</h2>
                <p>Are you sure you want to delete this customer?</p>
                <form method="POST" action="">
                    <input type="hidden" name="delete_customer" value="1" />
                    <input type="hidden" name="ID" value="<?php echo $row['ID']; ?>" />
                    <button type="submit">Yes, Delete</button>
                    <button type="button"
                        onclick="toggleModal('deleteCustomerModal-<?php echo $row['ID']; ?>')">Cancel</button>
                </form>

            </div>
        </div>
        <?php endwhile; ?>

        <div class="right">
            <?php include 'sub-sidebar.php'; ?>
        </div>
    </div>
</body>

</html>