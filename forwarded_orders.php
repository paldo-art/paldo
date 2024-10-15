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

// Fetch forwarded orders
$sql = "
    SELECT o.orderID, o.total, o.order_date, o.status, c.customername, c.address, s.shipperName, r.name as riderName
    FROM orders o
    JOIN customers c ON o.customerID = c.customerID
    JOIN shippers s ON o.shipperID = s.shipperID
    LEFT JOIN riders r ON o.riderID = r.riderID
    WHERE o.status = 'forwarded'
    ORDER BY o.orderID DESC
";
$result = $conn->query($sql);

// Count total forwarded orders
$total_orders = $result->num_rows;

// Calculate total revenue of forwarded orders
$total_revenue = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total_revenue += $row['total'];
    }
    // Reset the result pointer
    $result->data_seek(0);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forwarded Orders - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --accent-color: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 2rem;
        }

        .sidebar h1 {
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }

        .sidebar ul {
            list-style-type: none;
        }

        .sidebar ul li {
            margin-bottom: 1rem;
        }

        .sidebar ul li a {
            color: #ecf0f1;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 0.5rem;
        }

        .main-content {
            flex-grow: 1;
            padding: 2rem;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .dashboard-title {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
            color: #7f8c8d;
        }

        .stat-card p {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .orders-table {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .orders-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th, .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .orders-table th {
            background-color: var(--primary-color);
            color: #fff;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }

        .btn-view {
            background-color: var(--primary-color);
            color: #fff;
        }

        .btn:hover {
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h1>Admin Dashboard</h1>
            <ul>
                <li><a href="admin_panel.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="admin_panel.php"><i class="fas fa-shopping-cart"></i> Pending Orders</a></li>
                <li><a href="forwarded_orders.php"><i class="fas fa-truck"></i> Forwarded Orders</a></li>
                <li><a href="delivered_orders.php"><i class="fas fa-check-circle"></i> Delivered Orders</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="#"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2 class="dashboard-title">Forwarded Orders</h2>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                    <a href="admin_logout.php" class="btn btn-logout">Logout</a>
                </div>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Forwarded Orders</h3>
                    <p><?php echo $total_orders; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Revenue (Forwarded)</h3>
                    <p><?php echo number_format($total_revenue, 2); ?></p>
                </div>
            </div>

            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Address</th>
                            <th>Total Amount</th>
                            <th>Order Date</th>
                            <th>Shipper</th>
                            <th>Assigned Rider</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['orderID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['customername']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td><?php echo number_format($row['total'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['shipperName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['riderName'] ?? 'Not assigned'); ?></td>
                                    <td>
                                        <form action="forwarded_details.php" method="GET">
                                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['orderID']); ?>">
                                            <button type="submit" class="btn btn-view">View</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No forwarded orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <?php
    $conn->close();
    ?>
</body>
</html>