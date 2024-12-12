<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = mysqli_real_escape_string($conn, $_POST['Fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['Lname']);
    $sex = mysqli_real_escape_string($conn, $_POST['Sex']);
    $phone = mysqli_real_escape_string($conn, $_POST['PhoneNo']);
    $email = mysqli_real_escape_string($conn, $_POST['Email']);
    $ward = mysqli_real_escape_string($conn, $_POST['Ward']);
    $district = mysqli_real_escape_string($conn, $_POST['District']);
    $city = mysqli_real_escape_string($conn, $_POST['City']);

    $query = "INSERT INTO person (Fname, Lname, Sex, PhoneNo, Email, Ward, District, City) 
              VALUES ('$fname', '$lname', '$sex', '$phone', '$email', '$ward', '$district', '$city')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = 'Customer added successfully';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to add customer';
        $_SESSION['message_type'] = 'error';
    }

    header('Location: customers.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Customer</title>
    <link rel="stylesheet" href="./styles/style.css">
</head>

<body>
    <div class="container">
        <h1>Add New Customer</h1>
        <form action="" method="POST">
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
</body>

</html>
