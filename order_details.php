<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if order ID is set
if (!isset($_GET['order_id'])) {
    echo "Order ID not specified.";
    exit();
}

$order_id = intval($_GET['order_id']);

// Handle status update if the "Packed" button is clicked
if (isset($_POST['packed'])) {
    $update_status_sql = "UPDATE orders SET status = 'Packed' WHERE orderID = ?";
    $stmt = $conn->prepare($update_status_sql);
    $stmt->bind_param("i", $order_id);
    if ($stmt->execute()) {
        $_SESSION['order_status'][$order_id] = 'Packed'; // Store the packed status in session
        echo "<script>
            alert('Order status updated to Packed.');
            window.location.href = 'order_details.php?order_id=" . $order_id . "';
        </script>";
    } else {
        echo "<script>
            alert('Failed to update order status.');
            window.location.href = 'admin_panel.php'; // Change this to your admin panel URL
        </script>";
    }
    $stmt->close();
}

// Fetch order details for the specific order ID, including shipper and status
$sql = "
    SELECT o.orderID, o.total, o.order_date, c.customername, c.address, 
           p.productname, t.quantity, t.total_price AS total_amount, 
           s.shipperName, o.status
    FROM orders o
    JOIN customers c ON o.customerID = c.customerID
    JOIN transactions t ON o.orderID = t.orderID
    JOIN products p ON t.productID = p.productID
    JOIN shippers s ON o.shipperID = s.shipperID
    WHERE o.orderID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$order_status = null; // Initialize order status variable

// Check session for the order status
if (isset($_SESSION['order_status'][$order_id])) {
    $order_status = $_SESSION['order_status'][$order_id];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Add styles similar to your admin panel */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .details-panel {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .packed-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .packed-button:hover {
            background-color: #218838;
        }
        .already-packed {
            background-color: #6c757d; /* Grey color */
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div class="details-panel">
    <h1>Order Details</h1>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Address</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Total Amount</th>
                <th>Shipper</th>
                <th>Order Date</th>
            </tr>
            <?php 
            while ($row = $result->fetch_assoc()): 
                // Store the status from the first row
                if ($order_status === null) {
                    $order_status = $row['status'];
                }
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['orderID']) ?></td>
                    <td><?= htmlspecialchars($row['customername']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['productname']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td><?= number_format($row['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['shipperName']) ?></td>
                    <td><?= htmlspecialchars($row['order_date']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Show the packed button or the already packed message -->
        <?php if ($order_status === 'Packed'): ?>
            <button class="packed-button already-packed" disabled>Already Packed</button>
        <?php else: ?>
            <form method="POST" style="text-align: center;">
                <button type="submit" name="packed" class="packed-button">Mark as Packed</button>
            </form>
        <?php endif; ?>

    <?php else: ?>
        <p>No order details found for this order ID.</p>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
?>

</body>
</html>
