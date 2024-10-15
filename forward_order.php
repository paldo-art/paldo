<?php
session_start();

// Check if user is admin
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

// Retrieve the order ID from the POST request
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

// Ensure that order_id is valid
if ($order_id <= 0) {
    $_SESSION['error'] = "Invalid order ID.";
    header("Location: admin_panel.php");
    exit();
}

// Fetch the order details to get the customer's address
$sql = "SELECT c.address FROM orders o JOIN customers c ON o.customerID = c.customerID WHERE o.orderID = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['error'] = "Database error. Please try again later.";
    header("Location: admin_panel.php");
    exit();
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $address = $row['address'];
    
    // Assuming address contains the municipality information.
    $municipality = $address; // Modify this if necessary to extract  the municipality

    // Fetch an available rider based on the municipality
    $rider_sql = "SELECT riderID FROM riders WHERE municipality = ?";
    $rider_stmt = $conn->prepare($rider_sql);

    if (!$rider_stmt) {
        $_SESSION['error'] = "Database error. Please try again later.";
        header("Location: admin_panel.php");
        exit();
    }

    $rider_stmt->bind_param("s", $municipality);
    $rider_stmt->execute();
    $rider_result = $rider_stmt->get_result();

    if ($rider_row = $rider_result->fetch_assoc()) {
        $riderID = $rider_row['riderID'];

        // Update the order status and assign the rider
        $update_sql = "UPDATE orders SET status = 'forwarded', riderID = ? WHERE orderID = ?";
        $update_stmt = $conn->prepare($update_sql);

        if (!$update_stmt) {
            $_SESSION['error'] = "Database error. Please try again later.";
            header("Location: admin_panel.php");
            exit();
        }

        $update_stmt->bind_param("ii", $riderID, $order_id);
        $update_stmt->execute();

        $_SESSION['message'] = "Order forwarded successfully.";
    } else {
        $_SESSION['error'] = "No available riders found for this municipality.";
    }
} else {
    $_SESSION['error'] = "Order not found.";
}

$stmt->close();
$conn->close();
header("Location: admin_panel.php");
exit();
?>
