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

// Get the order ID and rider ID from the request
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;

// Check if the order has already been forwarded
$order_check_sql = "SELECT * FROM orders WHERE orderID = ? AND riderID IS NOT NULL";
$stmt = $conn->prepare($order_check_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_check_result = $stmt->get_result();

if ($order_check_result->num_rows > 0) {
    // Order has already been forwarded
    $_SESSION['message'] = "This order has already been forwarded.";
} else {
    // Forward the order
    $forward_order_sql = "UPDATE orders SET riderID = ? WHERE orderID = ?";
    $stmt = $conn->prepare($forward_order_sql);
    $stmt->bind_param("ii", $rider_id, $order_id);

    if ($stmt->execute()) {
        // Success
        $_SESSION['message'] = "Order has been successfully forwarded.";
    } else {
        // Error
        $_SESSION['message'] = "Failed to forward the order. Please try again.";
    }
}

$stmt->close();
$conn->close();

// Redirect back to the orders page
header("Location: admin_panel.php");
exit();
?>