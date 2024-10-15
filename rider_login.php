<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "login_system";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute query to check username and password
    $stmt = $conn->prepare("SELECT riderID, password FROM riders WHERE username = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($riderID, $hashed_password);
        $stmt->fetch();
        // Verify password
        if (password_verify($_POST['password'], $hashed_password)) {
            $_SESSION['riderID'] = $riderID; // Store rider ID in session
            header("Location: rider_ui.php"); // Redirect to orders page
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Invalid credentials.";
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Login</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(120deg, #f4f4f4, #dfe9f3); 
            height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
        }
        .form-container { 
            width: 320px; 
            padding: 40px 30px; 
            background-color: #fff; 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
            text-align: center;
        }
        .form-container h2 { 
            margin-bottom: 20px; 
            color: #333; 
        }
        .form-container input { 
            width: 100%; 
            padding: 12px 15px; 
            margin: 8px 0; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            transition: border-color 0.3s;
        }
        .form-container input:focus {
            border-color: #4CAF50; 
            outline: none;
        }
        .btn { 
            background-color: #4CAF50; 
            color: white; 
            padding: 12px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            width: 100%; 
            font-size: 16px; 
            transition: background-color 0.3s;
        }
        .btn:hover { 
            background-color: #45a049; 
        }
        .register-link, .signup-link { 
            margin-top: 15px; 
            color: #4CAF50; 
        }
        .register-link a, .signup-link a { 
            text-decoration: none; 
            color: #4CAF50; 
        }
        .register-link a:hover, .signup-link a:hover { 
            text-decoration: underline; 
        }
        .error { 
            color: red; 
            font-size: 14px; 
            margin-bottom: 10px; 
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Rider Login</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn">Login</button>
    </form>
    <div class="register-link">
        <p>New rider? <a href="rider_registration.php">Register here</a></p>
    </div>
    <div class="signup-link">
        <p>Don't have an account? <a href="rider_registration.php">Sign up</a></p>
    </div>
</div>

</body>
</html>