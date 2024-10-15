<?php
// Include your database connection file
require 'db_connection.php'; // Ensure this file contains your $conn variable for the database connection

$error_message = ""; // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customername = $_POST['customername'];
    $contactname = $_POST['contactname'];
    $address = $_POST['address']; // Captures the selected municipality
    $contactnumber = $_POST['contactnumber'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Password hashing

    // Debugging output
    var_dump($address); // Check the value being inserted

    // Make sure the SQL statement includes the address
    $sql = "INSERT INTO customers (customername, contactname, address, contactnumber, email, password) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $customername, $contactname, $address, $contactnumber, $email, $password);

    if ($stmt->execute()) {
        echo "<script>
                alert('Registration successful! Redirecting to login page...');
                window.location.href = 'index.php'; // Redirect to login page
              </script>";
    } else {
        $error_message = "Error: " . $stmt->error . " (SQLSTATE: " . $stmt->sqlstate . ")"; // Capture error message
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file for styles -->
</head>
<body>
    <div class="container">
        <h2>Registration Form</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="customername">Customer Name:</label>
            <input type="text" name="customername" required>

            <label for="contactname">Contact Name:</label>
            <input type="text" name="contactname" required>

            <label for="address">Address:</label>
            <select name="address" required>
                <option value="">Select Municipality</option>
                <option value="Adams">Adams</option>
                <option value="Bacarra">Bacarra</option>
                <option value="Badoc">Badoc</option>
                <option value="Bangui">Bangui</option>
                <option value="Batac">Batac</option>
                <option value="Burgos">Burgos</option>
                <option value="Carasi">Carasi</option>
                <option value="Currimao">Currimao</option>
                <option value="Dingras">Dingras</option>
                <option value="Laoag City">Laoag City</option>
                <option value="Nina">Nina</option>
                <option value="Paoay">Paoay</option>
                <option value="Pasuquin">Pasuquin</option>
                <option value="Pagudpud">Pagudpud</option>
                <option value="San Nicolas">San Nicolas</option>
                <option value="Sarrat">Sarrat</option>
                <option value="Solsona">Solsona</option>
                <option value="Vintar">Vintar</option>
            </select>

            <label for="contactnumber">Contact Number:</label>
            <input type="text" name="contactnumber" required>

            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <input type="submit" value="Register">
        </form>
    </div>
</body>
</html>
