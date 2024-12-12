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
        FullName, 
        Ssn, 
        DoB, 
        joinDate, 
        Shift
    FROM staffs";

// Apply search filter
if ($search_query) {
    $query .= " WHERE FullName LIKE '%" . mysqli_real_escape_string($conn, $search_query) . "%'";
}

// Apply alphabetical filter (A-Z or Z-A)
$query .= " ORDER BY FullName $filter_option";

// Apply pagination limit and offset
$query .= " LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query) or die("Query Failed: " . mysqli_error($conn));

// Fetch total staff for pagination
$total_query = "SELECT COUNT(*) AS total FROM staffs";
$total_result = mysqli_query($conn, $total_query) or die("Total Query Failed: " . mysqli_error($conn));
$total_staff = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_staff / $limit);

// EDIT STAFF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_staff'])) {
        $id = $_POST['ID'];
        $fullName = mysqli_real_escape_string($conn, $_POST['FullName']);
        $ssn = mysqli_real_escape_string($conn, $_POST['Ssn']);
        $dob = mysqli_real_escape_string($conn, $_POST['DoB']);
        $joinDate = mysqli_real_escape_string($conn, $_POST['joinDate']);
        $shift = mysqli_real_escape_string($conn, $_POST['Shift']);

        $update_query = "
            UPDATE staffs
            SET 
                FullName = '$fullName', 
                Ssn = '$ssn', 
                DoB = '$dob', 
                joinDate = '$joinDate', 
                Shift = '$shift'
            WHERE ID = $id";

        if (mysqli_query($conn, $update_query)) {
            $_SESSION['message'] = 'Staff updated successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to update staff.';
            $_SESSION['message_type'] = 'error';
        }
    }

    // DELETE STAFF
    if (isset($_POST['delete_staff'])) {
        $id = $_POST['ID'];

        $delete_query = "DELETE FROM staffs WHERE ID = $id";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['message'] = 'Staff deleted successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to delete staff.';
            $_SESSION['message_type'] = 'error';
        }
    }

    // ADD STAFF
    if (isset($_POST['add_staff'])) {
        // Retrieve form data
        $fullName = mysqli_real_escape_string($conn, $_POST['FullName']);
        $ssn = mysqli_real_escape_string($conn, $_POST['Ssn']);
        $dob = mysqli_real_escape_string($conn, $_POST['DoB']);
        $joinDate = mysqli_real_escape_string($conn, $_POST['joinDate']);
        $shift = mysqli_real_escape_string($conn, $_POST['Shift']);

        // Insert data into the `staffs` table
        $query = "INSERT INTO staffs (FullName, Ssn, DoB, joinDate, Shift) 
                  VALUES ('$fullName', '$ssn', '$dob', '$joinDate', '$shift')";
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'Staff added successfully';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to add staff';
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

// Assign Shift
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_shift'])) {
    $id = $_POST['ID'];
    $shift = mysqli_real_escape_string($conn, $_POST['Shift']);

    $update_shift_query = "
        UPDATE staffs
        SET Shift = '$shift'
        WHERE ID = $id";

    if (mysqli_query($conn, $update_shift_query)) {
        $_SESSION['message'] = 'Shift assigned successfully.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to assign shift.';
        $_SESSION['message_type'] = 'error';
    }
}

// Query to get the total number of customers
$sqlCustomers = "SELECT COUNT(*) AS total FROM person";
$resultCustomers = $conn->query($sqlCustomers);
if ($resultCustomers->num_rows > 0) {
    $customerData = $resultCustomers->fetch_assoc();
    $total_customers = $customerData['total'];
} else {
    $total_customers = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Staff Management</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/style.css" />
    <link rel="stylesheet" href="./styles/staffs.css" />
    <script src="./constants/staffs.js" defer></script>
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>

        <main>
            <h1>Staffs</h1>
            <div class="add-staff">
                <button class="add-btn" onclick="toggleModal('addStaffModal')">+ Add Staff</button>
            </div>
            <!-- Add Staff Section -->
            <div id="addStaffModal" class="modal hidden">
                <div class="modal-content">
                    <span class="close" onclick="toggleModal('addStaffModal')">&times;</span>
                    <h2>Add New Staff</h2>
                    <form action="" method="POST">
                        <input type="hidden" name="add_staff" value="true" />

                        <label for="FullName">Full Name</label>
                        <input type="text" name="FullName" required />

                        <label for="Ssn">SSN</label>
                        <input type="text" name="Ssn" required />

                        <!-- Date of Birth (DoB) -->
                        <div class="date-input-container">
                            <label for="dob">Date of Birth</label>
                            <div class="dob-tooltip">
                                <input type="date" id="dob" name="DoB" placeholder="MM/DD/YYYY" required>
                                <span class="tooltip-text">Please select your date of birth</span>
                            </div>
                        </div>

                        <!-- Join Date -->
                        <div class="date-input-container">
                            <label for="joinDate">Join Date</label>
                            <div class="dob-tooltip">
                                <input type="date" id="joinDate" name="joinDate" placeholder="MM/DD/YYYY" required>
                                <span class="tooltip-text">Please select your joining date</span>
                            </div>
                        </div>


                        <label for="Shift">Shift</label>
                        <input type="text" name="Shift" required />

                        <button type="submit">Add new</button>
                    </form>
                </div>
            </div>

            <div class="customer-summary">
                <div class="summary-card">
                    <h2>Total Staff</h2>
                    <h1><?php echo $total_staff; ?></h1>
                    <span class="percentage-change up">+20%</span>
                </div>
                <div class="summary-card">
                    <h2>Total Customers</h2>
                    <h1><?php echo $total_customers; ?></h1>
                    <span class="percentage-change up">+15%</span>
                </div>
                <div class="summary-card">
                    <h2>Active Now</h2>
                    <h1>316</h1>
                </div>
            </div>


            <!-- Staff Table -->
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Staff Name</th>
                        <th>SSN</th>
                        <th>Date of Birth</th>
                        <th>Join Date</th>
                        <th>Shift</th>
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
                        <td><?php echo htmlspecialchars($row['Ssn']); ?></td>
                        <td><?php echo htmlspecialchars($row['DoB']); ?></td>
                        <td><?php echo htmlspecialchars($row['joinDate']); ?></td>
                        <td><?php echo htmlspecialchars($row['Shift']); ?></td>
                        <td>

                            <button class="assign-shift-btn"
                                onclick="toggleModal('assignShiftModal-<?php echo $row['ID']; ?>')">Assign
                                Shift</button>
                            <button class="edit-btn"
                                onclick="toggleModal('editStaffModal-<?php echo $row['ID']; ?>')">Edit</button>
                            <button class="delete-btn"
                                onclick="toggleModal('deleteStaffModal-<?php echo $row['ID']; ?>')">Delete</button>
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

        <!-- Modals for edit and delete functionality -->
        <?php 
        // Reset the result pointer for modals
        mysqli_data_seek($result, 0); 
        while ($row = mysqli_fetch_assoc($result)): ?>
        <!-- Edit Staff Modal -->
        <div id="editStaffModal-<?php echo $row['ID']; ?>" class="modal hidden">
            <div class="modal-content">
                <span class="close" onclick="toggleModal('editStaffModal-<?php echo $row['ID']; ?>')">&times;</span>
                <h2>Edit Staff</h2>
                <form method="POST" action="">
                    <input type="hidden" name="edit_staff" value="1" />
                    <input type="hidden" name="ID" value="<?php echo $row['ID']; ?>" />

                    <label for="FullName">Full Name</label>
                    <input type="text" name="FullName" value="<?php echo htmlspecialchars($row['FullName']); ?>"
                        required />

                    <label for="Ssn">SSN</label>
                    <input type="text" name="Ssn" value="<?php echo htmlspecialchars($row['Ssn']); ?>" required />


                    <!-- Date of Birth (DoB) -->
                    <div class="date-input-container">
                        <label for="dob">Date of Birth</label>
                        <div class="dob-tooltip">
                            <input type="date" id="dob" name="DoB" placeholder="MM/DD/YYYY"
                                value="<?php echo htmlspecialchars($row['DoB']); ?>" required />
                            <span class="tooltip-text">Please select your date of birth</span>
                        </div>
                    </div>

                    <!-- Join Date -->
                    <div class="date-input-container">
                        <label for="joinDate">Join Date</label>
                        <div class="dob-tooltip">
                            <input type="date" id="joinDate" name="joinDate" placeholder="MM/DD/YYYY"
                                value="<?php echo htmlspecialchars($row['Shift']); ?>" required />
                            <span class="tooltip-text">Please select your joining date</span>
                        </div>
                    </div>

                    <label for="Shift">Shift</label>
                    <input type="text" name="Shift" value="<?php echo htmlspecialchars($row['Shift']); ?>" required />

                    <button type="submit">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Delete Staff Modal -->
        <div id="deleteStaffModal-<?php echo $row['ID']; ?>" class="modal hidden">
            <div class="modal-content">
                <span class="close" onclick="toggleModal('deleteStaffModal-<?php echo $row['ID']; ?>')">&times;</span>
                <h2>Delete Staff</h2>
                <p>Are you sure you want to delete this staff member?</p>
                <form method="POST" action="">
                    <input type="hidden" name="delete_staff" value="1" />
                    <input type="hidden" name="ID" value="<?php echo $row['ID']; ?>" />
                    <button type="submit">Yes, Delete</button>
                    <button type="button"
                        onclick="toggleModal('deleteStaffModal-<?php echo $row['ID']; ?>')">Cancel</button>
                </form>
            </div>
        </div>

        <!-- Assign Shift Modal -->
        <div id="assignShiftModal-<?php echo $row['ID']; ?>" class="modal hidden">
            <div class="modal-content">
                <span class="close" onclick="toggleModal('assignShiftModal-<?php echo $row['ID']; ?>')">&times;</span>
                <h2>Assign Shift to <?php echo htmlspecialchars($row['FullName']); ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="assign_shift" value="1" />
                    <input type="hidden" name="ID" value="<?php echo $row['ID']; ?>" />

                    <label for="Shift">Select Shift</label>
                    <select name="Shift" required>
                        <option value="Morning" <?php if ($row['Shift'] == 'Morning') echo 'selected'; ?>>Morning (8am -
                            1pm)</option>
                        <option value="Afternoon" <?php if ($row['Shift'] == 'Afternoon') echo 'selected'; ?>>Afternoon
                            (1pm - 6pm)</option>
                        <option value="Evening" <?php if ($row['Shift'] == 'Evening') echo 'selected'; ?>>Evening (6pm -
                            10pm)</option>
                    </select>

                    <button type="submit">Assign Shift</button>
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