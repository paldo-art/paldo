<?php
session_start();

// Debugging: Check session variables
if (!isset($_SESSION['customerID'])) {
    echo "Session variable 'customerID' is not set.";
    exit();
} else {
    $customer_id = $_SESSION['customerID'];
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

// Check if the user has submitted the shipper ID
if (isset($_POST['shipper_id'])) {
    $shipper_id = (int)$_POST['shipper_id']; // Ensure shipper_id is an integer

    // Create a new order
    $order_total = calculate_total_cart($conn); // Pass the connection to the function
    if ($order_total > 0) { // Ensure total is greater than zero
        $order_sql = "INSERT INTO orders (customerID, shipperID, total) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($order_sql);
        $stmt->bind_param("iid", $customer_id, $shipper_id, $order_total);

        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error; // Error handling
            exit();
        }

        $order_id = $stmt->insert_id; // Get the last inserted order ID
        $stmt->close();

        // Move items from cart to transactions
        $cart_sql = "SELECT cart.productID, cart.quantity, products.price FROM cart JOIN products ON cart.productID = products.productID WHERE cart.customerID = ?";
        $cart_stmt = $conn->prepare($cart_sql);
        $cart_stmt->bind_param("i", $customer_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();

        while ($cart_row = $cart_result->fetch_assoc()) {
            $transaction_sql = "INSERT INTO transactions (orderID, productID, quantity, total_price) VALUES (?, ?, ?, ?)";
            $transaction_stmt = $conn->prepare($transaction_sql);
            $total_price = $cart_row['quantity'] * $cart_row['price'];
            $transaction_stmt->bind_param("iiid", $order_id, $cart_row['productID'], $cart_row['quantity'], $total_price);
            
            if (!$transaction_stmt->execute()) {
                echo "Error: " . $transaction_stmt->error; // Error handling
                exit();
            }
            $transaction_stmt->close();
        }

        // Clear the cart
        $clear_cart_sql = "DELETE FROM cart WHERE customerID = ?";
        $clear_cart_stmt = $conn->prepare($clear_cart_sql);
        $clear_cart_stmt->bind_param("i", $customer_id);
        
        if (!$clear_cart_stmt->execute()) {
            echo "Error: " . $clear_cart_stmt->error; // Error handling
            exit();
        }
        $clear_cart_stmt->close();

        // Set success message and redirect with JavaScript alert
        echo "<script>alert('Order placed successfully!'); window.location.href='product_dashboard.php';</script>";
        exit();
    } else {
        echo "Your cart is empty or total is zero.";
    }
} else {
    echo "You must submit a shipper ID to checkout.";
}

// Function to calculate total cart price
function calculate_total_cart($conn) {
    $customer_id = $_SESSION['customerID'];
    $sql = "SELECT SUM(price * quantity) AS total FROM cart JOIN products ON cart.productID = products.productID WHERE cart.customerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?: 0; // Return 0 if total is null
}

// Close database connection
$conn->close();
?>
