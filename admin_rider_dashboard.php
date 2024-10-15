<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to handle file upload
function uploadFile($file) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        return "File is not an image.";
    }

    // Check file size
    if ($file["size"] > 500000) {
        return "Sorry, your file is too large.";
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        return "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }

    // if everything is ok, try to upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        return "Sorry, there was an error uploading your file.";
    }
}

// Handle order delivery
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_delivered'])) {
    $order_id = intval($_POST['order_id']);
    $delivery_photo = uploadFile($_FILES["delivery_photo"]);

    if (strpos($delivery_photo, 'uploads/') === 0) {
        $update_query = "UPDATE orders SET status = 'delivered', delivery_date = NOW(), delivery_photo = ? WHERE orderID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $delivery_photo, $order_id);
        $stmt->execute();
        $delivery_message = "Order marked as delivered successfully.";
    } else {
        $delivery_message = $delivery_photo; // Error message from file upload
    }
}

// Fetch all riders
$riders_query = "SELECT * FROM riders ORDER BY name";
$riders_result = $conn->query($riders_query);

// Fetch orders for a specific rider if riderID is provided
$selected_rider = null;
$orders = [];
$delivered_orders = [];
if (isset($_GET['riderID'])) {
    $rider_id = intval($_GET['riderID']);
    $rider_query = "SELECT * FROM riders WHERE riderID = ?";
    $stmt = $conn->prepare($rider_query);
    $stmt->bind_param("i", $rider_id);
    $stmt->execute();
    $selected_rider = $stmt->get_result()->fetch_assoc();

    // Fetch active orders (packed or forwarded)
    $orders_query = "SELECT o.*, c.customername, c.address 
                     FROM orders o 
                     JOIN customers c ON o.customerID = c.customerID 
                     WHERE o.riderID = ? AND o.status IN ('packed', 'forwarded')
                     ORDER BY o.order_date DESC";
    $stmt = $conn->prepare($orders_query);
    $stmt->bind_param("i", $rider_id);
    $stmt->execute();
    $orders_result = $stmt->get_result();
    while ($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }

    // Fetch delivered orders
    $delivered_query = "SELECT o.*, c.customername, c.address 
                        FROM orders o 
                        JOIN customers c ON o.customerID = c.customerID 
                        WHERE o.riderID = ? AND o.status = 'delivered'
                        ORDER BY o.delivery_date DESC";
    $stmt = $conn->prepare($delivered_query);
    $stmt->bind_param("i", $rider_id);
    $stmt->execute();
    $delivered_result = $stmt->get_result();
    while ($row = $delivered_result->fetch_assoc()) {
        $delivered_orders[] = $row;
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
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Rider Dashboard</h1>
        
        <?php if (isset($delivery_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $delivery_message; ?></span>
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Registered Riders</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Municipality</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($rider = $riders_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($rider['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($rider['username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($rider['municipality']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="?riderID=<?php echo $rider['riderID']; ?>" class="text-indigo-600 hover:text-indigo-900">View Orders</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($selected_rider): ?>
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Orders for <?php echo htmlspecialchars($selected_rider['name']); ?></h2>
                    <?php if (empty($orders)): ?>
                        <p>No active orders found for this rider.</p>
                    <?php else: ?>
                        <h3 class="text-lg font-semibold mb-2">Active Orders</h3>
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['orderID']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['customername']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">₱<?php echo number_format($order['total'], 2); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?php echo $order['status'] === 'packed' ? 'yellow' : 'green'; ?>-100 text-<?php echo $order['status'] === 'packed' ? 'yellow' : 'green'; ?>-800">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['order_date']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <form action="" method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['orderID']; ?>">
                                                    <input type="file" name="delivery_photo" accept="image/*" required class="mb-2">
                                                    <button type="submit" name="mark_delivered" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                                        Mark as Delivered
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <h3 class="text-lg font-semibold mb-2">Delivered Orders</h3>
                    <?php if (empty($delivered_orders)): ?>
                        <p>No delivered orders found for this rider.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proof of Delivery</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($delivered_orders as $order): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['orderID']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['customername']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">₱<?php echo number_format($order['total'], 2); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['order_date']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($order['delivery_date']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="<?php echo htmlspecialchars($order['delivery_photo']); ?>" target="_blank" class="text-blue-600 hover:text-blue-900">View Photo</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>