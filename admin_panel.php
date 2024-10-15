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

// Determine which page to display
$page = isset($_GET['page']) ? $_GET['page'] : 'pending_orders';

// Fetch order details excluding forwarded and delivered orders
$sql = "
    SELECT o.orderID, o.total, o.order_date, o.status, c.customername, c.address, s.shipperName 
    FROM orders o
    JOIN customers c ON o.customerID = c.customerID
    JOIN shippers s ON o.shipperID = s.shipperID
    WHERE o.status NOT IN ('forwarded', 'delivered')
    ORDER BY o.orderID DESC
";
$result = $conn->query($sql);

// Count total orders (excluding forwarded and delivered)
$total_orders = $result->num_rows;

// Calculate total revenue (excluding forwarded and delivered)
$total_revenue = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total_revenue += $row['total'];
    }
    // Reset the result pointer
    $result->data_seek(0);
}

// Get current month's orders (excluding forwarded and delivered)
$current_month_orders = 0;
$current_month = date('Y-m');
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (strpos($row['order_date'], $current_month) === 0) {
            $current_month_orders++;
        }
    }
    // Reset the result pointer
    $result->data_seek(0);
}

// Function to get page title
function getPageTitle($page) {
    switch ($page) {
        case 'pending_orders':
            return 'Pending Orders Overview';
        case 'customers':
            return 'Customer Management';
        case 'products':
            return 'Product Management';
        case 'analytics':
            return 'Analytics Dashboard';
        case 'settings':
            return 'System Settings';
        default:
            return 'Admin Dashboard';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo getPageTitle($page); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
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
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar h1 {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: var(--primary-color);
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
            padding: 0.5rem;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .sidebar ul li a:hover, .sidebar ul li a.active {
            background-color: var(--primary-color);
        }

        .sidebar ul li a i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex-grow: 1;
            padding: 2rem;
            margin-left: 250px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background-color: #fff;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dashboard-title {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info span {
            margin-right: 1rem;
        }

        .btn-logout {
            background-color: var(--accent-color);
            color: #fff;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-logout:hover {
            background-color: #c0392b;
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
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
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

        .orders-table tr:nth-child(even) {
            background-color: #f8f9fa;
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

        .btn-forward {
            background-color: var(--success-color);
            color: #fff;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                padding: 1rem;
            }

            .main-content {
                margin-left: 0;
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
                <li><a href="?page=pending_orders" <?php echo $page == 'pending_orders' ? 'class="active"' : ''; ?>><i class="fas fa-shopping-cart"></i> Pending Orders</a></li>
                <li><a href="forwarded_orders.php"><i class="fas fa-truck"></i> Forwarded Orders</a></li>
                <li><a href="delivered_orders.php"><i class="fas fa-check-circle"></i> Delivered Orders</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="products.php" <?php echo $page == 'products' ? 'class="active"' : ''; ?>><i class="fas fa-box"></i> Products</a></li>
                <li><a href="analytics.php" <?php echo $page == 'analytics' ? 'class="active"' : ''; ?>><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li><a href="settings.php" <?php echo $page == 'settings' ? 'class="active"' : ''; ?>><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h2 class="dashboard-title"><?php echo getPageTitle($page); ?></h2>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                    <a href="admin_logout.php" class="btn-logout">Logout</a>
                </div>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['message']); ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if ($page == 'pending_orders'): ?>
                <div class="stats-container">
                    <div class="stat-card">
                        <h3>Pending Orders</h3>
                        <p><?php echo $total_orders; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Revenue</h3>
                        <p><?php echo number_format($total_revenue, 2); ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>This Month's Pending Orders</h3>
                        <p><?php echo $current_month_orders; ?></p>
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
                                <th>Status</th>
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
                                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                                        <td>
                                            <a href="order_details.php?order_id=<?php echo htmlspecialchars($row['orderID']); ?>" class="btn btn-view">View</a>
                                            <form action="forward_order.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['orderID']); ?>">
                                                <button type="submit" class="btn btn-forward">Forward</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">No pending orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($page == 'customers'): ?>
                <h3>Customer Management</h3>
                <p>This section is under development. It will display customer information and management tools.</p>
            <?php elseif ($page == 'products'): ?>
                <h3>Product Management</h3>
                <p>This section is under development. It will  display product information and management tools.</p>
            <?php elseif ($page == 'analytics'): ?>
                <h3>Analytics Dashboard</h3>
                <p>This section is under development. It will display various analytics and reports.</p>
            <?php elseif ($page == 'settings'): ?>
                <h3>System Settings</h3>
                <p>This section is under development. It will allow you to configure system settings.</p>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Add any JavaScript functionality here
        // For example, you could add client-side filtering or sorting of the orders table
    </script>

    <?php
    $conn->close();
    ?>
</body>
</html>