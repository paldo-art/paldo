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

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $orderId = intval($_GET['id']);
    $newStatus = $_GET['status'];
    
    $allowedStatuses = ['otw', 'delivered'];
    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE orderID = ? AND riderID = ?");
        $stmt->bind_param("sii", $newStatus, $orderId, $_SESSION['riderID']);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Order status updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update order status.";
        }
    } else {
        $_SESSION['error'] = "Invalid status.";
    }
}

header("Location: rider_ui.php");
exit();
?>