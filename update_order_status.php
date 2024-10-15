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
    $order_id = $_GET['id'];
    $status = $_GET['status'];

    // Update the status of the order
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE orderID = ?");
    $stmt->bind_param("si", $status, $order_id);
    if ($stmt->execute()) {
        header("Location: rider_dashboard.php");  // Redirect back to the dashboard after successful update
    } else {
        echo "Failed to update status";
    }
    $stmt->close();
}

$conn->close();
?>
