<?php
// Start the session at the beginning
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

// Handle search functionality directly
$customers = [];
if (isset($_GET['search_customer'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search_customer']);
    $search_query = "SELECT ID, CONCAT(Fname, ' ', Lname) AS FullName, PhoneNo, Email 
                     FROM person WHERE Fname LIKE '%$search_term%' 
                     OR Lname LIKE '%$search_term%' 
                     OR PhoneNo LIKE '%$search_term%' 
                     OR Email LIKE '%$search_term%'";
    $search_result = mysqli_query($conn, $search_query);
    $customers = mysqli_fetch_all($search_result, MYSQLI_ASSOC);
    echo json_encode($customers);
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
                            <input type="text" name="Sex" list="gender-options" required placeholder="Type or select gender" />
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
                <form id="order-form">
                    <div class="order-form-header">
                        <!-- Customer Search -->
                        <div class="form-group">
                            <label for="customer-search">Customer</label>
                            <input type="text" id="customer-search" name="customer" placeholder="Search by name, phone, or email" autocomplete="off" required />
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
                        </table>
                        <button type="button" id="add-product" class="btn btn-secondary">Add Product</button>
                    </div>

                    <!-- Total Price Section -->
                    <div class="form-group">
                        <label for="total-price">Total Price</label>
                        <div id="total-price" class="total-price">0.00 USD</div>
                    </div>

                    <!-- Order Status -->
                    <div class="form-group">
                        <label for="status">Status</label>
                        <div>
                            <input type="radio" id="status-completed" name="status" value="completed" checked />
                            <label for="status-completed">Completed</label>
                            <input type="radio" id="status-shipped" name="status" value="shipped" />
                            <label for="status-shipped">Shipped</label>
                            <input type="radio" id="status-pending" name="status" value="pending" />
                            <label for="status-pending">Pending</label>
                        </div>
                    </div>

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

        // Helper function to fetch data with caching for repeated calls
        const cache = {};
        async function fetchData(url) {
            if (cache[url]) return cache[url];
            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status} - ${response.statusText}`);
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
                const customers = await fetchData(`orders.php?search_customer=${encodeURIComponent(query)}`);
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

        // Update Total Price Function
        function updateTotalPrice() {
            let totalPrice = 0;

            const rows = productTable.querySelectorAll("tr");
            rows.forEach(row => {
                const totalCell = row.querySelector("td:nth-child(5) input");
                const total = parseFloat(totalCell.value || 0);
                totalPrice += total;
            });

            document.getElementById("total-price").textContent = totalPrice.toFixed(2) + " USD";
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

        // Add a product row when button is clicked
        addProductButton.addEventListener("click", () => {
            const newRow = createProductRow();
            productTable.appendChild(newRow);

            updateTotalPrice(); // Update the total price after adding a new product
        });

        // Create individual table cells for product, quantity, price, etc.
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

        // Create an action cell with a remove button
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


.edit-btn, .delete-btn {
    background-color: #008CFF;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
    width: auto;
    text-align: center;
    font-size: 16px;
    display: inline-block;
  }
  
  .edit-btn:hover, .delete-btn:hover {
    background-color: #005f99;
  }