<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'login_system');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Admin credentials
    $adminEmail = "admin@gmail.com"; // Change this to your admin email
    $adminPassword = "admin"; // Change this to your admin password (consider using hashed password in production)

    // Check if the input is for admin
    if ($email === $adminEmail && $password === $adminPassword) {
        // Admin login successful
        $_SESSION['admin'] = true;
        $_SESSION['admin_email'] = $adminEmail;
        header("Location: admin_panel.php");
        exit();
    }

    // Fetch customer data from the database
    $sql = "SELECT * FROM customers WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Password is correct
            $_SESSION['customername'] = $row['customername'];
            $_SESSION['customerID'] = $row['customerID'];
            header("Location: product_dashboard.php");
            exit();
        } else {
            $error_message = "Invalid password!";
        }
    } else {
        $error_message = "No user found with that email!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Your E-commerce Store</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            display: flex;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 850px;
            min-height: 550px;
            flex-direction: column;
            transition: transform 0.3s ease-in-out;
        }

        .form-container {
            padding: 50px;
            width: 100%;
        }

        .illustration-container {
            background: linear-gradient(135deg, #5ca9fb 0%, #6372ff 100%);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            width: 100%;
        }

        h1 {
            font-size: 2em;
            margin-bottom: 20px;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        input {
            background-color: #f0f0f0;
            border: none;
            padding: 12px 20px;
            margin: 8px 0;
            border-radius: 8px;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.2s;
        }

        input:focus {
            background-color: #e2e2e2;
            outline: none;
        }

        button {
            border-radius: 25px;
            border: none;
            background: linear-gradient(135deg, #6a82fb 0%, #fc5c7d 100%);
            color: #ffffff;
            font-size: 14px;
            font-weight: bold;
            padding: 12px 45px;
            margin-top: 20px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
        }

        button:hover {
            background: linear-gradient(135deg, #fc5c7d 0%, #6a82fb 100%);
        }

        button:active {
            transform: scale(0.97);
        }

        .social-container {
            margin: 20px 0;
        }

        .social-container a {
            border: 1px solid #dddddd;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 10px;
            height: 45px;
            width: 45px;
            text-decoration: none;
            color: #333;
            font-size: 18px;
            transition: background-color 0.3s;
        }

        .social-container a:hover {
            background-color: #f0f0f0;
        }

        .links {
            color: #3498db;
            font-size: 14px;
            text-decoration: none;
            margin: 15px 0;
            display: inline-block;
        }

        .error-message {
            color: #ff4d4f;
            margin-bottom: 10px;
        }

        .illustration-container h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .illustration-container p {
            font-size: 16px;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 20px;
            }

            .illustration-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Login</h1>
            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form action="index.php" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="social-container">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-google-plus-g"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <a href="register.php" class="links">Don't have an account? Sign Up</a>
        </div>
        <div class="illustration-container">
            <h1>Welcome Back!</h1>
            <p>Good to see you again! Let's get you logged in.</p>
        </div>
    </div>
</body>
</html>
