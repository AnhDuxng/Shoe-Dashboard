<?php
// Start the session
session_start();

// Include the database connection
include 'config.php';

// Add Customer Logic

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
    // Insert data into the person table
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
    
    // Handle search functionality
    if (isset($_GET['search_customer'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search_customer']);
    $search_query = "SELECT ID, CONCAT(Fname, ' ', Lname) AS FullName, PhoneNo, Email
    FROM person
    WHERE Fname LIKE '%$search_term%'  
    OR Lname LIKE '%$search_term%'  
    OR PhoneNo LIKE '%$search_term%'  
    OR Email LIKE '%$search_term%'";  
    $search_result = mysqli_query($conn, $search_query); 
    $customers = mysqli_fetch_all($search_result, MYSQLI_ASSOC);   
    echo json_encode($customers);   
    exit;
    }

// Modify the save order logic section in PHP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {
    // Retrieve form data
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $products = json_decode($_POST['products'], true);
    $warehouse = mysqli_real_escape_string($conn, $_POST['warehouse']);

    // Debug output
    error_log("Customer ID: " . $customer_id);
    error_log("Products: " . print_r($products, true));

    // Validate required fields
    if (empty($customer_id) || empty($products)) {
        $_SESSION['message'] = 'Missing required fields';
        $_SESSION['message_type'] = 'error';
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    // Start transaction
    mysqli_begin_transaction($conn);
    try {
        // Fetch customer details
        $customer_query = "SELECT CONCAT(Fname, ' ', Lname) AS Name, PhoneNo FROM person WHERE ID = ?";
        $stmt = mysqli_prepare($conn, $customer_query);
        mysqli_stmt_bind_param($stmt, "s", $customer_id);
        mysqli_stmt_execute($stmt);
        $customer_result = mysqli_stmt_get_result($stmt);
        $customer_data = mysqli_fetch_assoc($customer_result);

        if (!$customer_data) {
            throw new Exception('Customer not found');
        }

        // Get the last order ID
        $last_order_query = "SELECT OrderID FROM order_details ORDER BY OrderID DESC LIMIT 1";
        $last_order_result = mysqli_query($conn, $last_order_query);
        $last_order = mysqli_fetch_assoc($last_order_result);

        // Set new order ID
        $order_id = $last_order ? ($last_order['OrderID'] + 1) : 1;
        
// Prepare the order details insert statement
$details_query = "INSERT INTO order_details (OrderID, CustomerID, Name, PhoneNo, ModelName, Quantity, Discount, Price, TotalPrice, Status, PaymentMethod, StoreCity) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $details_query);

foreach ($products as $product) {
    mysqli_stmt_bind_param($stmt, "sssssddddsss",
        $order_id,
        $customer_id,
        $customer_data['Name'],
        $customer_data['PhoneNo'],
        $product['model_name'],
        $product['quantity'],
        $product['discount'],
        $product['price'],
        $product['total'],
        $status,
        $payment_method,
        $warehouse // Add the warehouse value to StoreCity
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_error($conn));
    }
}

        mysqli_commit($conn);
        $_SESSION['message'] = 'Order created successfully';
        $_SESSION['message_type'] = 'success';
        echo json_encode(['status' => 'success']);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'] = 'Failed to create order: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
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
    <link rel="stylesheet" href="./styles/orders.css" />
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <main>
            <div class="create-order">
                <h1>Create New Order</h1>
                <div class="add-product">
                    <button class="add-btn" onclick="toggleModal('addCustomerModal')">+ Add Customer</button>
                </div>

                <!-- Add Customer Modal -->
                <div id="addCustomerModal" class="modal hidden">
                    <div class="modal-content">
                        <span class="close" onclick="toggleModal('addCustomerModal')">&times;</span>
                        <h2>Add New Customer</h2>
                        <form action="" method="POST">
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
                            <button type="submit">Add Customer</button>
                        </form>
                    </div>
                </div>

                <!-- Order Form -->
                <form id="order-form" action="" method="POST">
                    <input type="hidden" name="save_order" value="true">
                    <div class="order-form-header">
                        <!-- Customer Search -->
                        <div class="form-group">
                            <label for="customer-search">Customer</label>
                            <input type="text" id="customer-search" name="customer"
                                placeholder="Search by name, phone, or email" autocomplete="off" required />
                        </div>
                        <input type="hidden" id="selected-customer-id" name="customer_id" />

                        <!-- Warehouse Selection -->
                        <div class="form-group">
                            <label for="warehouse">From Warehouse</label>
                            <select id="warehouse" name="warehouse"></select>
                        </div>
                    </div>

                    <!-- Products Section -->
                    <div class="form-group">
                        <label for="products">Products</label>
                        <table id="product-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Discount (%)</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <!-- Total Price Row will be added dynamically -->
                        </table>
                        <button type="button" id="add-product" class="btn btn-secondary">Add Product</button>
                    </div>

                    <!-- Extra Info Section -->
                    <div class="extra-info">
                        <!-- Order Status -->
                        <div class="form-group">
                            <label for="status">Status</label>
                            <div class="status-buttons">
                                <button type="button" class="status-btn" id="status-completed"
                                    data-value="Completed">Completed</button>
                                <button type="button" class="status-btn" id="status-shipped"
                                    data-value="Shipped">Shipped</button>
                                <button type="button" class="status-btn" id="status-pending"
                                    data-value="Pending">Pending</button>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-group">
                            <label for="payment-method">Payment Method</label>
                            <div class="payment-buttons">
                                <button type="button" class="payment-btn" id="payment-credit-card"
                                    data-value="Credit Card">Credit Card</button>
                                <button type="button" class="payment-btn" id="payment-cash"
                                    data-value="Cash">Cash</button>
                                <button type="button" class="payment-btn" id="payment-bank-transfer"
                                    data-value="Bank Transfer">Bank Transfer</button>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Inputs to Store the Selected Values -->
                    <input type="hidden" id="selected-status" name="status" value="completed" />
                    <input type="hidden" id="selected-payment-method" name="payment_method" value="credit_card" />

                    <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        // Order Status Button Click Handler
                        const statusButtons = document.querySelectorAll(".status-btn");
                        statusButtons.forEach(button => {
                            button.addEventListener("click", () => {
                                // Remove the selected class from all buttons
                                statusButtons.forEach(btn => btn.classList.remove("selected"));
                                // Add selected class to the clicked button
                                button.classList.add("selected");

                                // Store the selected value in a hidden input
                                document.getElementById("selected-status").value = button
                                    .dataset.value;
                            });
                        });

                        // Payment Method Button Click Handler
                        const paymentButtons = document.querySelectorAll(".payment-btn");
                        paymentButtons.forEach(button => {
                            button.addEventListener("click", () => {
                                // Remove the selected class from all buttons
                                paymentButtons.forEach(btn => btn.classList.remove("selected"));
                                // Add selected class to the clicked button
                                button.classList.add("selected");

                                // Store the selected payment method value in a hidden input
                                document.getElementById("selected-payment-method").value =
                                    button.dataset.value;
                            });
                        });
                    });
                    </script>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Create Order</button>
                        <button type="reset" class="btn btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        </main>

        <div class="right">
            <?php include 'sub-sidebar.php'; ?>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const customerSearchInput = document.getElementById("customer-search");
        const suggestionsBox = document.createElement("div");
        suggestionsBox.id = "customer-suggestions";
        customerSearchInput.parentNode.insertBefore(suggestionsBox, customerSearchInput.nextSibling);

        const warehouseSelect = document.getElementById("warehouse");
        const productTable = document.getElementById("product-table").querySelector("tbody");
        const addProductButton = document.getElementById("add-product");
        let productRowIdCounter = 1;

        // Add this to your existing JavaScript, inside the DOMContentLoaded event listener
        const orderForm = document.getElementById("order-form");

        orderForm.addEventListener("submit", function(e) {
            e.preventDefault();

            // Validate customer selection
            const customerId = document.getElementById("selected-customer-id").value;
            if (!customerId) {
                alert("Please select a customer");
                return;
            }

            // Get all product rows
            const productRows = document.querySelectorAll(
                "#product-table tbody tr:not(#total-price-row)");
            const products = [];

            // Validate and collect product data
            for (const row of productRows) {
                const modelName = row.querySelector(".product-select").value;
                const quantity = row.querySelector("td:nth-child(2) input").value;
                const price = row.querySelector("td:nth-child(3) input").value;
                const discount = row.querySelector("td:nth-child(4) input").value;
                const total = row.querySelector("td:nth-child(5) input").value;

                if (!modelName || !quantity) {
                    alert("Please fill in all product details");
                    return;
                }

                products.push({
                    model_name: modelName,
                    quantity: quantity,
                    price: price,
                    discount: discount,
                    total: total
                });
            }

            if (products.length === 0) {
                alert("Please add at least one product");
                return;
            }

            // Create FormData object
            const formData = new FormData(orderForm);
            formData.append('products', JSON.stringify(products));
            formData.append('warehouse', warehouse); 

            // Submit form using fetch
            fetch(orderForm.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // Redirect or show success message
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving the order');
                });
        });

        // Helper function to fetch data with caching for repeated calls
        const cache = {};
        async function fetchData(url) {
            if (cache[url]) return cache[url];
            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error(
                    `HTTP error! Status: ${response.status} - ${response.statusText}`);
                const data = await response.json();
                cache[url] = data;
                return data;
            } catch (error) {
                console.error("Error fetching data:", error);
                return [];
            }
        }

        // Populate Warehouses Dropdown
        fetchData("./api/get-warehouses.php").then(warehouses => {
            warehouses.forEach(warehouse => {
                const option = document.createElement("option");
                option.value = warehouse.ID;
                option.textContent = `${warehouse.City} - ${warehouse.StoreAddress}`;
                warehouseSelect.appendChild(option);
            });
        });

        // Customer Search Logic
        customerSearchInput.addEventListener("input", async () => {
            const query = customerSearchInput.value.trim();
            if (query.length > 2) {
                const customers = await fetchData(
                    `orders.php?search_customer=${encodeURIComponent(query)}`);
                suggestionsBox.innerHTML = customers.length ?
                    customers.map(customer =>
                        `<div class="suggestion" data-id="${customer.ID}">${customer.FullName} - ${customer.PhoneNo}</div>`
                    ).join("") :
                    '<div class="no-suggestions">No customers found</div>';
                suggestionsBox.classList.remove("hidden");
            } else {
                suggestionsBox.classList.add("hidden");
            }
        });

        // Handle Customer Suggestion Click
        suggestionsBox.addEventListener("click", (event) => {
            const suggestion = event.target.closest(".suggestion");
            if (suggestion) {
                customerSearchInput.value = suggestion.textContent.split(" - ")[0];
                document.getElementById("selected-customer-id").value = suggestion.dataset.id;
                suggestionsBox.classList.add("hidden");
                event.stopPropagation();
            }
        });

        // Modal Toggle
        window.toggleModal = (id) => {
            const modal = document.getElementById(id);
            modal.classList.toggle("hidden");
        };

        // Add Product Row to the Table
        function createProductRow() {
            const row = document.createElement("tr");
            row.id = `product-row-${productRowIdCounter++}`;

            const productCell = createProductSelectCell();
            const quantityCell = createNumberInputCell("Quantity", 1);
            const priceCell = createNumberInputCell("Price", 0, true); // Readonly
            const discountCell = createNumberInputCell("Discount (%)", 0);
            const totalCell = createNumberInputCell("Total", 0, true); // Readonly
            const actionCell = createActionCell();

            row.appendChild(productCell);
            row.appendChild(quantityCell);
            row.appendChild(priceCell);
            row.appendChild(discountCell);
            row.appendChild(totalCell);
            row.appendChild(actionCell);

            // Event Listeners for dynamic calculation and product selection
            const productSelect = productCell.querySelector("select");
            const quantityInput = quantityCell.querySelector("input");
            const priceInput = priceCell.querySelector("input");
            const discountInput = discountCell.querySelector("input");

            productSelect.addEventListener("change", () => {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                priceInput.value = parseFloat(selectedOption.dataset.price || 0);
                discountInput.value = parseFloat(selectedOption.dataset.discount || 0);
                calculateTotal(row);
            });

            quantityInput.addEventListener("input", () => calculateTotal(row));
            priceInput.addEventListener("input", () => calculateTotal(row));
            discountInput.addEventListener("input", () => calculateTotal(row));

            return row;
        }

        // Update Total Price Row to always be at the bottom
        function updateTotalPrice() {
            let totalPrice = 0;

            const rows = productTable.querySelectorAll("tr");
            rows.forEach(row => {
                const totalCell = row.querySelector("td:nth-child(5) input");
                if (totalCell) { // Check if the input exists before accessing its value
                    const total = parseFloat(totalCell.value || 0);
                    totalPrice += total;
                }
            });

            let totalRow = document.getElementById("total-price-row");

            if (totalRow) {
                const totalInput = totalRow.querySelector("td:nth-child(2) input");
                if (totalInput) {
                    totalInput.value = totalPrice.toFixed(2); // Update the total price input
                }
            } else if (totalPrice > 0) {
                // Create total price row if it doesn't exist
                totalRow = document.createElement("tr");
                totalRow.id = "total-price-row";
                totalRow.innerHTML = `
            <td colspan="4">Total Price</td>
            <td><input type="text" readonly value="${totalPrice.toFixed(2)}" /></td>
            <td></td>
        `;
                productTable.appendChild(totalRow);
            }

            // If totalPrice is 0, remove the total row (optional)
            if (totalPrice === 0) {
                if (totalRow) {
                    totalRow.remove();
                }
            }
        }

        // Calculate the total price for each row
        function calculateTotal(row) {
            const quantity = parseFloat(row.querySelector("td:nth-child(2) input").value || 0);
            const price = parseFloat(row.querySelector("td:nth-child(3) input").value || 0);
            const discount = parseFloat(row.querySelector("td:nth-child(4) input").value || 0);
            const total = quantity * price * ((100 - discount) / 100);
            row.querySelector("td:nth-child(5) input").value = total.toFixed(2);

            updateTotalPrice(); // Update the total price after each calculation
        }

        // Ensure Total Price row stays at the bottom when a product is added
        addProductButton.addEventListener("click", () => {
            const newRow = createProductRow();
            productTable.appendChild(newRow);

            // After adding a product, ensure the total price row is at the bottom
            updateTotalPrice();
        });

        // Create individual table cells for product, quantity, etc.
        function createProductSelectCell() {
            const cell = document.createElement("td");
            const select = document.createElement("select");
            select.classList.add("product-select");
            select.innerHTML = '<option value="">Select Product</option>';

            fetchData("./api/get-products.php").then(products => {
                products.forEach(product => {
                    const option = document.createElement("option");
                    option.value = product.ModelName;
                    option.textContent = product.ModelName;
                    option.dataset.price = product.UnitPrice;
                    option.dataset.discount = product.Discount;
                    select.appendChild(option);
                });
            });

            cell.appendChild(select);
            return cell;
        }

        // Create a number input cell with placeholder, min, and optional readonly
        function createNumberInputCell(placeholder, min, readOnly = false) {
            const cell = document.createElement("td");
            const input = document.createElement("input");
            input.type = "number";
            input.placeholder = placeholder;
            input.min = min;
            input.readOnly = readOnly;
            cell.appendChild(input);
            return cell;
        }

        // When a product row is removed, recalculate and update total price
        function createActionCell() {
            const cell = document.createElement("td");
            const removeButton = document.createElement("button");
            removeButton.textContent = "Remove";
            removeButton.classList.add("remove-product");
            removeButton.addEventListener("click", () => {
                productTable.deleteRow(removeButton.closest('tr'));
                updateTotalPrice(); // Update total price after removing a product
            });
            cell.appendChild(removeButton);
            return cell;
        }
    });
    </script>

</body>

</html>