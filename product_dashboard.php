<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch products
function fetch_products() {
    global $conn;
    $sql = "SELECT * FROM products";
    return $conn->query($sql);
}

// Function to get cart quantity
function get_cart_quantity() {
    global $conn;
    $customer_id = $_SESSION['customerID'];
    $sql = "SELECT SUM(quantity) as total FROM cart WHERE customerID = $customer_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ? $row['total'] : 0;
}

$products = fetch_products();
$cart_quantity = get_cart_quantity();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>jhay-k   - Product Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --background-color: #ecf0f1;
            --text-color: #34495e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--secondary-color);
            color: #fff;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .sidebar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 2rem;
            text-align: center;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }

        .sidebar nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar nav a i {
            margin-right: 0.75rem;
        }

        .main-content {
            flex-grow: 1;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .welcome-section {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .welcome-section h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
        }

        .product-price {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .product-description {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 1rem;
        }

        .add-to-cart {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .add-to-cart:hover {
            background-color: #2980b9;
        }

        .cart-icon {
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--accent-color);
            color: #fff;
            font-size: 0.75rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: var(--primary-color);
            color: #fff;
            padding: 1rem;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">JD TechZone</div>
            <nav>
                <a href="product_dashboard.php"><i class="fas fa-home"></i> Home</a>
                <a href="orders.php"><i class="fas fa-file-invoice"></i> My Orders</a>
                <a href="view_cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i> View Cart
                    <?php if ($cart_quantity > 0): ?>
                        <span class="cart-count"><?php echo $cart_quantity; ?></span>
                    <?php endif; ?>
                </a>
                <form action="logout.php" method="POST">
                    <button type="submit" style="background: none; border: none; color: #fff; width: 100%; text-align: left; padding: 0.75rem 1rem; cursor: pointer;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </nav>
        </aside>

        <main class="main-content">
            <section class="welcome-section">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['customername']); ?></h2>
                <p>Explore our latest tech products and find the perfect gadget for you!</p>
            </section>

            <div class="product-grid">
                <?php
                if ($products->num_rows > 0) {
                    while ($row = $products->fetch_assoc()) {
                        echo "<div class='product-card'>";
                        echo "<img src='images/" . htmlspecialchars($row["image_url"]) . "' alt='" . htmlspecialchars($row["productname"]) . "' class='product-image'>";
                        echo "<div class='product-info'>";
                        echo "<h3 class='product-title'>" . htmlspecialchars($row["productname"]) . "</h3>";
                        echo "<p class='product-price'>₱" . number_format($row["price"], 2) . "</p>";
                        echo "<p class='product-description'>" . htmlspecialchars($row["description"]) . "</p>";
                        echo "<form action='' method='POST'>";
                        echo "<input type='hidden' name='product_id' value='" . $row["productID"] . "'>";
                        echo "<button type='submit' name='add_to_cart' class='add-to-cart'><i class='fas fa-cart-plus'></i> Add to Cart</button>";
                        echo "</form>";
                        echo "</div>"; // Close product-info
                        echo "</div>"; // Close product-card
                    }
                } else {
                    echo "<p>No products found.</p>";
                }
                ?>
            </div>
        </main>
    </div>

    <div class="notification" id="notification">Product added to cart.</div>

    <script>
    function showNotification() {
        const notification = document.getElementById('notification');
        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    // Add to Cart Logic
    <?php
    if (isset($_POST['add_to_cart'])) {
        $product_id = $_POST['product_id'];
        $customer_id = $_SESSION['customerID'];

        // Check if the product is already in the cart
        $check_cart = "SELECT * FROM cart WHERE customerID = $customer_id AND productID = $product_id";
        $check_result = $conn->query($check_cart);

        if ($check_result->num_rows > 0) {
            // Update quantity if already exists
            $update_quantity = "UPDATE cart SET quantity = quantity + 1 WHERE customerID = $customer_id AND productID = $product_id";
            $conn->query($update_quantity);
        } else {
            // Insert new product into cart
            $insert_cart = "INSERT INTO cart (customerID, productID, quantity) VALUES ($customer_id, $product_id, 1)";
            $conn->query($insert_cart);
        }

        // Refresh cart quantity
        $cart_quantity = get_cart_quantity();
        echo "document.querySelector('.cart-count').textContent = '" . $cart_quantity . "';";
        echo "showNotification();";
    }
    ?>
    </script>
</body>
</html>

<?php
$conn->close();
?>