<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'login_system');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['customerID'])) {
    header("Location: index.php");
    exit();
}

$customerID = $_SESSION['customerID'];

// Remove item from cart
if (isset($_POST['remove'])) {
    $productID = $_POST['product_id'];
    $deleteSql = "DELETE FROM cart WHERE customerID = ? AND productID = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("ii", $customerID, $productID);
    $deleteStmt->execute();
    $deleteStmt->close();
}

// Fetch cart items
$sql = "SELECT c.quantity, p.productname, p.price, p.productID, p.image_url 
        FROM cart c 
        JOIN products p ON c.productID = p.productID 
        WHERE c.customerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;

// Fetch shippers
$shipper_sql = "SELECT * FROM shippers";
$shipper_result = $conn->query($shipper_sql);

// Check if shippers are fetched successfully
if (!$shipper_result) {
    die("Error fetching shippers: " . $conn->error);
}

// If there are no shippers, you may want to handle that as well
if ($shipper_result->num_rows === 0) {
    echo "No shippers found.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-right: 20px;
        }
        .cart-item-details {
            flex-grow: 1;
        }
        .remove-form {
            margin-left: 20px;
        }
        #shipper_id {
            width: 100%;
            max-width: 300px;
            height: 40px;
            font-size: 16px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .checkout-button {
            padding: 10px 15px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .checkout-button:hover {
            background-color: #45a049;
        }
        .continue-shopping {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #008CBA;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .continue-shopping:hover {
            background-color: #007B9A;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Cart</h2>
        <?php while ($row = $result->fetch_assoc()) { 
            $subtotal = $row['price'] * $row['quantity'];
            $total += $subtotal;
        ?>
            <div class="cart-item">
                <img src="images/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['productname']); ?>">
                <div class="cart-item-details">
                    <h3><?php echo htmlspecialchars($row['productname']); ?></h3>
                    <p>Price: ₱<?php echo number_format($row['price'], 2); ?></p>
                    <p>Quantity: <?php echo $row['quantity']; ?></p>
                    <p>Subtotal: ₱<?php echo number_format($subtotal, 2); ?></p>
                </div>
                <form action="" method="POST" class="remove-form">
                    <input type="hidden" name="product_id" value="<?php echo $row['productID']; ?>">
                    <button type="submit" name="remove" class="remove-button">Remove</button>
                </form>
            </div>
        <?php } ?>
        <div class="cart-total">
            <h3>Total: ₱<?php echo number_format($total, 2); ?></h3>
        </div>

        <!-- Shipper Selection Form -->
        <form action="checkout.php" method="POST" class="checkout-form">
            <label for="shipper_id">Select a Shipper:</label>
            <select name="shipper_id" id="shipper_id" required>
                <?php
                // Populate shipper options
                while ($shipper_row = $shipper_result->fetch_assoc()) {
                    echo "<option value='" . $shipper_row['shipperID'] . "'>" . htmlspecialchars($shipper_row['shippername']) . "</option>";
                }
                ?>
            </select>
            <button type="submit" class="checkout-button">Proceed to Checkout</button>
        </form>
        
        <a href="product_dashboard.php" class="continue-shopping">Continue Shopping</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>