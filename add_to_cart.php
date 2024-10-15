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
    $productID = $_POST['productID'];

    
    $sql = "INSERT INTO cart (customerID, productID, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $customerID, $productID);
    $stmt->execute();
    
    
    header("Location: product_dashboard.php");
    exit();
}
?>
