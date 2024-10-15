<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'login_system');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['customerID'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerID = $_SESSION['customerID'];
    $shipperID = $_POST['shipper'];
    $total = $_POST['total'];

    // Insert order into orders table
    $sql = "INSERT INTO orders (customerID, shipperID, total) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iid", $customerID, $shipperID, $total);
    $stmt->execute();

    // Clear cart after placing order
    $sqlClearCart = "DELETE FROM cart WHERE customerID = ?";
    $stmtClear = $conn->prepare($sqlClearCart);
    $stmtClear->bind_param("i", $customerID);
    $stmtClear->execute();

    // Set success message
    $_SESSION['success_message'] = "Order placed successfully!";

    // Redirect to product dashboard
    header("Location: product_dashboard.php");
    exit();
}
?>
