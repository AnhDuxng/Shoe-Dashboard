<?php
// Start the session at the beginning
session_start();
// Include the database connection
include 'config.php';

// Handle form submission for adding a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    // Extract form data
    $model_name = mysqli_real_escape_string($conn, $_POST['ModelName']);
    $category = mysqli_real_escape_string($conn, $_POST['Category']);
    $brand = mysqli_real_escape_string($conn, $_POST['Brand']);
    $size = (float) $_POST['Size'];
    $color = mysqli_real_escape_string($conn, $_POST['Color']);
    $unit_in_stock = (int) $_POST['UnitInStock'];
    $unit_price = (float) $_POST['UnitPrice'];
    $discount = (float) $_POST['Discount'];

    // Insert the product into the database
    $insert_query = "
        INSERT INTO shoe (ModelName, Category, Brand, Size, Color, UnitInStock, UnitPrice, Discount)
        VALUES ('$model_name', '$category', '$brand', $size, '$color', $unit_in_stock, $unit_price, $discount)
    ";

    if (mysqli_query($conn, $insert_query)) {
        // Redirect to prevent form resubmission
        header('Location: products.php?success=Product added successfully');
        exit();
    } else {
        // Handle error
        $error_message = 'Error adding product: ' . mysqli_error($conn);
    }
}

// Handle edit product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // EDIT PRODUCT
    if (isset($_POST['edit_product'])) {
        $model_name = mysqli_real_escape_string($conn, $_POST['ModelName']);
        $category = mysqli_real_escape_string($conn, $_POST['Category']);
        $brand = mysqli_real_escape_string($conn, $_POST['Brand']);
        $size = (float)$_POST['Size'];
        $color = mysqli_real_escape_string($conn, $_POST['Color']);
        $unit_in_stock = (int)$_POST['UnitInStock'];
        $unit_price = (float)$_POST['UnitPrice'];
        $discount = (float)$_POST['Discount'];

        $update_query = "
            UPDATE shoe
            SET 
                Category = '$category',
                Brand = '$brand',
                Size = $size,
                Color = '$color',
                UnitInStock = $unit_in_stock,
                UnitPrice = $unit_price,
                Discount = $discount
            WHERE ModelName = '$model_name'
        ";

        if (mysqli_query($conn, $update_query)) {
            $_SESSION['message'] = 'Product updated successfully.';
            $_SESSION['message_type'] = 'success';
            header('Location: products.php');
            exit();
        } else {
            $_SESSION['message'] = 'Failed to update product: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
        }
    }

    // DELETE PRODUCT
    if (isset($_POST['delete_product'])) {
        $model_name = mysqli_real_escape_string($conn, $_POST['ModelName']);

        $delete_query = "DELETE FROM shoe WHERE ModelName = '$model_name'";

        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['message'] = 'Product deleted successfully.';
            $_SESSION['message_type'] = 'success';
            header('Location: products.php');
            exit();
        } else {
            $_SESSION['message'] = 'Failed to delete product: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
        }
    }
}

// Handle search and filters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : ''; // Optional filter for category

// Pagination logic
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Base query
$query = "SELECT ModelName, Category, Brand, Size, Color, UnitInStock, UnitPrice, Discount FROM shoe";

// Apply search filter for ModelName
if ($search_query) {
    $query .= " WHERE ModelName LIKE '%" . mysqli_real_escape_string($conn, $search_query) . "%'";
}

// Apply category filter
if ($filter_category) {
    $query .= " AND Category = '" . mysqli_real_escape_string($conn, $filter_category) . "'";
}

// Apply pagination limit and offset
$query .= " LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query) or die("Query Failed: " . mysqli_error($conn));

// Fetch total products for pagination
$total_query = "SELECT COUNT(*) AS total FROM shoe";
$total_result = mysqli_query($conn, $total_query) or die("Total Query Failed: " . mysqli_error($conn));
$total_products = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_products / $limit);

// Define the filter option for sorting
$filter_option = isset($_GET['filter']) ? $_GET['filter'] : 'asc';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products Management</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./styles/style.css" />
    <link rel="stylesheet" href="./styles/products.css" />
    <script src="./constants/customer.js" defer></script>
    <script src="./constants/products.js" defer></script>
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <main>
            <h1>Products</h1>
            <!-- Add product button -->
            <div class="add-product">
                <button class="add-btn" onclick="toggleModal('addProductModal')">+ Add Products</button>
            </div>

            <div id="addProductModal" class="modal hidden">
                <div class="modal-content">
                    <span class="close" onclick="toggleModal('addProductModal')">&times;</span>
                    <h2>Add New Product</h2>
                    <form action="" method="POST">
                        <!-- Hidden field to identify the add product form -->
                        <input type="hidden" name="add_product" value="true" />

                        <label for="ModelName">Model Name</label>
                        <input type="text" name="ModelName" required />

                        <label for="Category">Category</label>
                        <input type="text" name="Category" required list="category-options"
                            placeholder="Type or select category" />
                        <datalist id="category-options">
                            <option value="Sneakers"></option>
                            <option value="Running"></option>
                            <option value="Training"></option>
                            <option value="Casual"></option>
                            <option value="Loafers"></option>
                        </datalist>

                        <label for="Brand">Brand</label>
                        <input type="text" name="Brand" required list="brand-options"
                            placeholder="Type or select brand" />
                        <datalist id="brand-options">
                            <option value="Nike"></option>
                            <option value="Adidas"></option>
                            <option value="Puma"></option>
                            <option value="Converse"></option>
                            <option value="Reebok"></option>
                            <option value="Clarks"></option>
                            <option value="Skechers"></option>
                            <option value="Asics"></option>
                            <option value="Everlast"></option>
                            <option value="K-Swiss"></option>
                        </datalist>

                        <label for="Size">Size</label>
                        <input type="number" name="Size" step="0.5" min="35" max="50" required />

                        <label for="Color">Color</label>
                        <input type="text" name="Color" required />

                        <label for="UnitInStock">Units in Stock</label>
                        <input type="number" name="UnitInStock" min="0" required />

                        <label for="UnitPrice">Unit Price</label>
                        <input type="number" name="UnitPrice" step="0.01" min="0" required />

                        <label for="Discount">Discount (%)</label>
                        <input type="number" name="Discount" step="0.01" min="0" max="100" required />

                        <button type="submit">Add Product</button>
                    </form>
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
                        <input type="text" name="search" placeholder="Search for products..."
                            value="<?php echo htmlspecialchars($search_query); ?>" />
                        <button type="submit">Search</button>
                    </form>
                </div>
            </div>




            <!-- Product table -->
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ModelName']); ?></td>
                        <td><?php echo htmlspecialchars($row['Category']); ?></td>
                        <td><?php echo htmlspecialchars($row['Brand']); ?></td>
                        <td><?php echo htmlspecialchars($row['UnitInStock']); ?></td>
                        <td>$<?php echo number_format($row['UnitPrice'], 2); ?></td>
                        <td><?php echo $row['Discount'] . '%'; ?></td>
                        <td>
                            <button class="edit-btn"
                                onclick="editProduct('<?php echo htmlspecialchars($row['ModelName']); ?>')">Edit</button>
                            <button class="delete-btn"
                                onclick="deleteProduct('<?php echo htmlspecialchars($row['ModelName']); ?>')">Delete</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <button <?php if ($page <= 1) echo 'disabled'; ?> class="btn-prev">
                    <a
                        href="?page=<?php echo max(1, $page - 1); ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo urlencode($filter_category); ?>&filter=<?php echo $filter_option; ?>">Previous</a>
                </button>
                <h2>Page <?php echo $page; ?> of <?php echo $total_pages; ?></h2>
                <button <?php if ($page >= $total_pages) echo 'disabled'; ?> class="btn-next">
                    <a
                        href="?page=<?php echo min($total_pages, $page + 1); ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo urlencode($filter_category); ?>&filter=<?php echo $filter_option; ?>">Next</a>
                </button>
            </div>
        </main>

        <?php
// Reset the result pointer for rendering modals
mysqli_data_seek($result, 0);
while ($row = mysqli_fetch_assoc($result)): ?>

        <!-- Edit Product Modal -->
        <div id="editProductModal-<?php echo $row['ModelName']; ?>" class="modal hidden">
            <div class="modal-content">
                <span class="close"
                    onclick="toggleModal('editProductModal-<?php echo $row['ModelName']; ?>')">&times;</span>
                <h2>Edit Product</h2>
                <form method="POST" action="">
                    <input type="hidden" name="edit_product" value="1" />
                    <input type="hidden" name="ModelName" value="<?php echo htmlspecialchars($row['ModelName']); ?>" />

                    <label for="Category">Category</label>
                    <input type="text" name="Category" list="category-options"
                        value="<?php echo htmlspecialchars($row['Category']); ?>" required />
                    <datalist id="category-options">
                        <option value="Sneakers"></option>
                        <option value="Running"></option>
                        <option value="Training"></option>
                        <option value="Casual"></option>
                        <option value="Loafers"></option>
                    </datalist>

                    <label for="Brand">Brand</label>
                    <input type="text" name="Brand" list="brand-options"
                        value="<?php echo htmlspecialchars($row['Brand']); ?>" required />
                    <datalist id="brand-options">
                        <option value="Nike"></option>
                        <option value="Adidas"></option>
                        <option value="Puma"></option>
                        <option value="Converse"></option>
                        <option value="Reebok"></option>
                        <option value="Clarks"></option>
                        <option value="Skechers"></option>
                        <option value="Asics"></option>
                        <option value="Everlast"></option>
                        <option value="K-Swiss"></option>
                    </datalist>

                    <label for="Size">Size</label>
                    <input type="number" name="Size" step="0.5" min="35" max="50"
                        value="<?php echo htmlspecialchars($row['Size']); ?>" required />

                    <label for="Color">Color</label>
                    <input type="text" name="Color" value="<?php echo htmlspecialchars($row['Color']); ?>" required />

                    <label for="UnitInStock">Units in Stock</label>
                    <input type="number" name="UnitInStock" min="0"
                        value="<?php echo htmlspecialchars($row['UnitInStock']); ?>" required />

                    <label for="UnitPrice">Unit Price</label>
                    <input type="number" name="UnitPrice" step="0.01" min="0"
                        value="<?php echo htmlspecialchars($row['UnitPrice']); ?>" required />

                    <label for="Discount">Discount (%)</label>
                    <input type="number" name="Discount" step="0.01" min="0" max="100"
                        value="<?php echo htmlspecialchars($row['Discount']); ?>" required />

                    <button type="submit">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Delete Product Modal -->
        <div id="deleteProductModal-<?php echo $row['ModelName']; ?>" class="modal hidden">
            <div class="modal-content">
                <span class="close"
                    onclick="toggleModal('deleteProductModal-<?php echo $row['ModelName']; ?>')">&times;</span>
                <h2>Delete Product</h2>
                <p>Are you sure you want to delete this product?</p>
                <form method="POST" action="">
                    <input type="hidden" name="delete_product" value="1" />
                    <input type="hidden" name="ModelName" value="<?php echo htmlspecialchars($row['ModelName']); ?>" />
                    <button type="submit">Yes, Delete</button>
                    <button type="button"
                        onclick="toggleModal('deleteProductModal-<?php echo $row['ModelName']; ?>')">Cancel</button>
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