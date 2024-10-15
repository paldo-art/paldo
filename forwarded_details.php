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

    
</div>

<?php
$stmt->close();
$conn->close();
?>
<?php endif; ?>
</body>
</html>
