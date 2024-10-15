<?php
session_start();
if (!isset($_SESSION['riderID'])) {
    header("Location: rider_login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_system";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch rider information
$stmt = $conn->prepare("SELECT * FROM riders WHERE riderID = ?");
$stmt->bind_param("i", $_SESSION['riderID']);
$stmt->execute();
$rider = $stmt->get_result()->fetch_assoc();

// Fetch orders
$sql = "SELECT o.orderID, o.total, o.order_date, o.status, c.customername, c.address 
        FROM orders o
        JOIN customers c ON o.customerID = c.customerID
        WHERE o.riderID = ? AND o.status IN ('forwarded', 'otw')  
        ORDER BY o.order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['riderID']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_delivered'])) {
    $order_id = $_POST['order_id'];
    $upload_dir = 'uploads/';
    $errors = [];

    if (isset($_FILES['delivery_photo']) && $_FILES['delivery_photo']['error'] == 0) {
        $file_name = $_FILES['delivery_photo']['name'];
        $file_tmp = $_FILES['delivery_photo']['tmp_name'];
        $file_parts = explode('.', $file_name);
        $file_ext = strtolower(end($file_parts));

        $extensions = ["jpeg", "jpg", "png"];
        if (!in_array($file_ext, $extensions)) {
            $errors[] = "Extension not allowed, please choose a JPEG or PNG file.";
        }
        if ($_FILES['delivery_photo']['size'] > 2097152) {
            $errors[] = 'File size must be less than 2 MB';
        }

        $new_file_name = uniqid() . '.' . $file_ext;
        $file_path = $upload_dir . $new_file_name;

        if (empty($errors)) {
            if (move_uploaded_file($file_tmp, $file_path)) {
                $update_sql = "UPDATE orders SET status = 'delivered', delivery_date = NOW(), delivery_photo = ? WHERE orderID = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $file_path, $order_id);
                $update_stmt->execute();

                $success_message = "Order marked as delivered!";
            } else {
                $errors[] = "File upload failed.";
            }
        }
    } else {
        $errors[] = "No file uploaded or error occurred.";
    }

    if (!empty($errors)) {
        $error_message = implode(", ", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
        }
        h1, h2 {
            color: #2b6cb0;
        }
        p, td {
            color: #2d3748;
        }
        .btn-primary {
            background-color: #48bb78;
            color: white;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #38a169;
        }
        .modal-bg {
            background-color: rgba(0, 0, 0, 0.7);
        }
        .bg-card {
            background-color: #edf2f7;
        }
        .notification {
            background-color: #48bb78;
            color: white;
        }
        .error-notification {
            background-color: #f56565;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-6">
        <?php if (isset($success_message)): ?>
            <div class="notification p-4 rounded mb-4"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="error-notification p-4 rounded mb-4"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="bg-card p-6 rounded-lg shadow-lg">
            <h1 class="text-3xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($rider['name']); ?>!</h1>
            <p class="text-lg mb-4">Your Municipality: <?php echo htmlspecialchars($rider['municipality']); ?></p>

            <h2 class="text-2xl font-semibold mb-4">Assigned Orders:</h2>
            <?php if (empty($orders)): ?>
                <p>No orders assigned yet.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                    <?php foreach ($orders as $order): ?>
                        <div class="bg-white p-4 rounded-lg shadow-lg border border-gray-200">
                            <h3 class="text-xl font-semibold mb-2">Order ID: <?php echo htmlspecialchars($order['orderID']); ?></h3>
                            <p class="mb-1">Customer: <?php echo htmlspecialchars($order['customername']); ?></p>
                            <p class="mb-1">Address: <?php echo htmlspecialchars($order['address']); ?></p>
                            <p class="mb-1">Total: $<?php echo number_format($order['total'], 2); ?></p>
                            <p class="mb-1">Date: <?php echo htmlspecialchars($order['order_date']); ?></p>
                            <p class="mb-2">Status: <?php echo htmlspecialchars($order['status']); ?></p>
                            <?php if ($order['status'] == 'forwarded'): ?>
                                <a href="update_order_status.php?id=<?php echo $order['orderID']; ?>&status=shipped" class="btn-primary px-4 py-2 rounded">Mark as OTW</a>
                            <?php elseif ($order['status'] == 'shipped'): ?>
                                <button onclick="openDeliveryModal(<?php echo $order['orderID']; ?>)" class="btn-primary px-4 py-2 rounded">Mark as Delivered</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="deliveryModal" class="fixed inset-0 modal-bg hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-card p-6 rounded-lg shadow-lg">
                <form id="deliveryForm" method="POST" enctype="multipart/form-data">
                    <h3 class="text-xl font-medium text-gray-800 mb-2">Confirm Delivery</h3>
                    <p class="text-gray-600 mb-4">Upload a proof of delivery photo.</p>
                    <input type="hidden" name="order_id" id="modalOrderId">
                    <input type="file" name="delivery_photo" accept="image/*" class="block w-full mb-4 border border-gray-300 p-2 rounded">
                    <button type="submit" name="mark_delivered" class="btn-primary px-4 py-2 rounded">Confirm Delivery</button>
                    <button type="button" onclick="closeDeliveryModal()" class="px-4 py-2 rounded bg-gray-500 text-white">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeliveryModal(orderId) {
            document.getElementById('modalOrderId').value = orderId;
            document.getElementById('deliveryModal').classList.remove('hidden');
        }

        function closeDeliveryModal() {
            document.getElementById('deliveryModal').classList.add('hidden');
        }
    </script>
</body>
</html>
