<?php
require_once 'db_connect.php';
session_start();

// Redirect if not admin
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}

// Get user ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}

$user_id = (int) $_GET['id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $region = trim($_POST['region']);
    $province = trim($_POST['province']);
    $city = trim($_POST['city']);
    $barangay = trim($_POST['barangay']);

    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, region = ?, province = ?, city = ?, barangay = ? WHERE id = ?");
    $stmt->execute([$username, $email, $region, $province, $city, $barangay, $user_id]);

    header("Location: admin_home.php?success=User updated");
    exit();
}

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center">
    <div class="bg-gray-800 p-8 rounded shadow-md w-full max-w-lg">
        <h2 class="text-2xl font-bold text-red-400 mb-6">Edit User</h2>

        <form method="POST">
            <div class="mb-4">
                <label class="block mb-1">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required
                    class="w-full px-3 py-2 bg-gray-700 text-white rounded">
            </div>
            <div class="mb-4">
                <label class="block mb-1">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                    class="w-full px-3 py-2 bg-gray-700 text-white rounded">
            </div>
            <div class="mb-4">
                <label class="block mb-1">Region</label>
                <input type="text" name="region" value="<?= htmlspecialchars($user['region']) ?>"
                    class="w-full px-3 py-2 bg-gray-700 text-white rounded">
            </div>
            <div class="mb-4">
                <label class="block mb-1">Province</label>
                <input type="text" name="province" value="<?= htmlspecialchars($user['province']) ?>"
                    class="w-full px-3 py-2 bg-gray-700 text-white rounded">
            </div>
            <div class="mb-4">
                <label class="block mb-1">City</label>
                <input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>"
                    class="w-full px-3 py-2 bg-gray-700 text-white rounded">
            </div>
            <div class="mb-4">
                <label class="block mb-1">Barangay</label>
                <input type="text" name="barangay" value="<?= htmlspecialchars($user['barangay']) ?>"
                    class="w-full px-3 py-2 bg-gray-700 text-white rounded">
            </div>

            <div class="flex justify-between items-center">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white">Update</button>
                <a href="admin_home.php" class="text-gray-400 hover:text-gray-200">Cancel</a>
            </div>
        </form>
    </div>
</body>

</html>