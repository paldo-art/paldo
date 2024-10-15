<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: index.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch customers with barangay and street
$sql = "SELECT customerID, customername, contactname, address, barangay, street, contactnumber, email FROM customers ORDER BY customerID DESC";
$result = $conn->query($sql);

// Handle customer deletion
if (isset($_POST['delete_customer'])) {
    $customer_id = $_POST['customer_id'];
    $delete_sql = "DELETE FROM customers WHERE customerID = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $customer_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Customer deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting customer.";
    }
    header("Location: customers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Assuming you have a separate CSS file -->
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div class="dashboard-header">
                <h2 class="dashboard-title">Customer Management</h2>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="customer-list">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Name</th>
                            <th>Address</th>
                            <th>Street</th>
                            <th>Barangay</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['customerID']); ?></td>
                                <td><?php echo htmlspecialchars($row['customername']); ?></td>
                                <td><?php echo htmlspecialchars($row['contactname']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td><?php echo htmlspecialchars($row['street']); ?></td>
                                <td><?php echo htmlspecialchars($row['barangay']); ?></td>
                                <td><?php echo htmlspecialchars($row['contactnumber']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <a href="edit_customer.php?id=<?php echo $row['customerID']; ?>" class="btn btn-edit">Edit</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="customer_id" value="<?php echo $row['customerID']; ?>">
                                        <button type="submit" name="delete_customer" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this customer?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>

<?php
$conn->close();
?>
