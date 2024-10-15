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

// Fetch total sales
$sales_sql = "SELECT SUM(total) as total_sales FROM orders WHERE status = 'delivered'";
$sales_result = $conn->query($sales_sql);
$total_sales = $sales_result->fetch_assoc()['total_sales'];

// Fetch total orders
$orders_sql = "SELECT COUNT(*) as total_orders FROM orders";
$orders_result = $conn->query($orders_sql);
$total_orders = $orders_result->fetch_assoc()['total_orders'];

// Fetch top selling products
$top_products_sql = "SELECT p.productname, SUM(t.quantity) as total_sold
                     FROM transactions t
                     JOIN products p ON t.productID = p.productID
                     GROUP BY t.productID
                     ORDER BY total_sold DESC
                     LIMIT 5";
$top_products_result = $conn->query($top_products_sql);

// Fetch recent orders
$recent_orders_sql = "SELECT o.orderID, c.customername, o.total, o.order_date, o.status
                      FROM orders o
                      JOIN customers c ON o.customerID = c.customerID
                      ORDER BY o.order_date DESC
                      LIMIT 10";
$recent_orders_result = $conn->query($recent_orders_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Assuming you have a separate CSS file -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div class="dashboard-header">
                <h2 class="dashboard-title">Analytics Dashboard</h2>
            </div>

            <div class="analytics-overview">
                <div class="stat-card">
                    <h3>Total Sales</h3>
                    <p>$<?php echo number_format($total_sales, 2); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p><?php echo $total_orders; ?></p>
                </div>
            </div>

            <div class="analytics-charts">
                <div class="chart-container">
                    <h3>Top Selling Products</h3>
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>

            <div class="recent-orders">
                <h3>Recent Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_orders_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['orderID']); ?></td>
                                <td><?php echo htmlspecialchars($row['customername']); ?></td>
                                <td>$<?php echo number_format($row['total'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Chart for top selling products
        var ctx = document.getElementById('topProductsChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php
                    $labels = [];
                    $data = [];
                    while ($row = $top_products_result->fetch_assoc()) {
                        $labels[] = "'" . $row['productname'] . "'";
                        $data[] = $row['total_sold'];
                    }
                    echo implode(',', $labels);
                ?>],
                datasets: [{
                    label: 'Units Sold',
                    data: [<?php echo implode(',', $data); ?>],
                    backgroundColor: 'rgba(75, 192, 192, 0.6)'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>