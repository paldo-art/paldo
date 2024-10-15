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

// Check if customer ID is set in the session
if (!isset($_SESSION['customerID'])) {
    echo "You need to log in to view your orders.";
    exit();
}

$customer_id = $_SESSION['customerID'];

// Fetch all orders for the logged-in customer
$sql = "
    SELECT o.orderID, o.total, o.order_date, o.status, c.customername, c.address, 
           p.productname, t.quantity, t.total_price AS total_amount, 
           s.shipperName
    FROM orders o
    JOIN customers c ON o.customerID = c.customerID
    JOIN transactions t ON o.orderID = t.orderID
    JOIN products p ON t.productID = p.productID
    JOIN shippers s ON o.shipperID = s.shipperID
    WHERE c.customerID = ?
    ORDER BY o.order_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .details-panel {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #1f2937;
            font-weight: 600;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
        }
        table th {
            background-color: #f9fafb;
            color: #1f2937;
            font-weight: 600;
        }
        table tr:nth-child(even) {
            background-color: #f4f6f8;
        }
        table tr:hover {
            background-color: #e2e8f0;
        }
        table td {
            border-bottom: 1px solid #e5e7eb;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #1f2937;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin-top: 20px;
            display: block;
            width: 200px;
            margin: 20px auto 0;
        }
        .btn:hover {
            background-color: #4b5563;
        }
        @media (max-width: 768px) {
            .details-panel {
                padding: 20px;
            }
            table th, table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>

<div class="details-panel">
    <h1>Your Orders</h1>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Total Amount</th>
                    <th>Shipper</th>
                    <th>Status</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['orderID']) ?></td>
                    <td><?= htmlspecialchars($row['customername']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['productname']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td><?= number_format($row['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['shipperName']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['order_date']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No orders found for your account.</p>
    <?php endif; ?>

    <a href="product_dashboard.php" class="btn">Back to Dashboard</a>
</div>

<?php
$stmt->close();
$conn->close();
?>

</body>
</html>
