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

$municipalities = [
    'Adams', 'Bacarra', 'Badoc', 'Bangui', 'Banna', 'Batac', 
    'Burgos', 'Carasi', 'Currimao', 'Dingras', 'Dumalneg', 
    'Laoag City', 'Marcos', 'Nueva Era', 'Pagudpud', 'Paoay', 
    'Pasuquin', 'Piddig', 'Pinili', 'San Nicolas', 'Sarrat', 
    'Solsona', 'Vintar'
];

// Fetch already taken municipalities
$stmt = $conn->prepare("SELECT DISTINCT municipality FROM riders");
$stmt->execute();
$result = $stmt->get_result();
$taken_municipalities = $result->fetch_all(MYSQLI_ASSOC);
$taken_municipalities = array_column($taken_municipalities, 'municipality');

// Available municipalities
$available_municipalities = array_diff($municipalities, $taken_municipalities);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $municipality = $_POST['municipality'];

    // Check if username already exists
    $stmt = $conn->prepare("SELECT username FROM riders WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<script>alert('Username already exists. Please choose a different username.');</script>";
    } else {
        // Check if municipality is already taken
        $stmt = $conn->prepare("SELECT municipality FROM riders WHERE municipality = ?");
        $stmt->bind_param("s", $municipality);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<script>alert('This municipality is already assigned to another rider. Please choose a different municipality.');</script>";
        } else {
            // Insert new rider
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO riders (username, password, name, municipality) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashed_password, $name, $municipality);
            
            if ($stmt->execute()) {
                echo "<script>alert('Registration successful!'); window.location.href='rider_login.php';</script>";
            } else {
                echo "<script>alert('Registration failed: " . $stmt->error . "');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4">
                <h2 class="text-2xl font-bold text-center mb-4">Rider Registration</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" id="username" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="municipality" class="block text-sm font-medium text-gray-700">Municipality</label>
                        <select name="municipality" id="municipality" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <?php foreach ($available_municipalities as $muni): ?>
                                <option value="<?php echo htmlspecialchars($muni); ?>"><?php echo htmlspecialchars($muni); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>