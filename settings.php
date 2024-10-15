<?php
session_start();
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password (you should replace this with your actual admin authentication logic)
        if ($current_password == "admin_password") {
            if ($new_password == $confirm_password) {
                // Update password logic here
                // For demonstration, we'll just show a success message
                $_SESSION['message'] = "Password updated successfully.";
            } else {
                $_SESSION['error'] = "New passwords do not match.";
            }
        } else {
            $_SESSION['error'] = "Current password is incorrect.";
        }
    } elseif (isset($_POST['update_email'])) {
        $new_email = $_POST['new_email'];
        // Update email logic here
        // For demonstration, we'll just show a success message
        $_SESSION['message'] = "Email updated successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Assuming you have a separate CSS file -->
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div class="dashboard-header">
                <h2 class="dashboard-title">System Settings</h2>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="settings-section">
                <h3>Change Password</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                </form>
            </div>

            <div class="settings-section">
                <h3>Update Email</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="new_email">New Email:</label>
                        <input type="email" id="new_email" name="new_email" required>
                    </div>
                    <button type="submit" name="update_email" class="btn btn-primary">Update Email</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

<?php
$conn->close();
?>