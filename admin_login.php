<?php
session_start();
require_once 'db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check admin from database
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION["admin_username"] = $admin['username'];
            $_SESSION["admin_id"] = $admin['id'];
            $_SESSION["admin_role"] = $admin['role'];
            header("Location: admin_home.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-main {
            background-image: url('images/valorcrate.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="h-screen w-screen flex">

    <!-- Left Panel -->
    <div class="h-full flex flex-col justify-center bg-white p-10" style="width: 27.5%;">
        <div class="w-full flex flex-col items-center">
            <!-- Riot Logo -->
            <img src="images/logo.png" alt="Logo" class="w-36 mb-10" />

            <!-- Login Form -->
            <div class="w-full max-w-sm text-center">
                <h1 class="text-2xl font-bold text-black mb-6">Admin Login</h1>

                <?php if (!empty($error)): ?>
                    <div class="mb-4 text-red-600 text-sm font-semibold">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5 text-black" id="adminLoginForm">
                    <input type="text" name="username" placeholder="Username" required
                        class="w-full px-4 py-3 text-lg rounded-full bg-white placeholder-gray-400 border border-gray-300 focus:border-black"
                        autocomplete="username" />

                    <input type="password" name="password" placeholder="Password" required
                        class="w-full px-4 py-3 text-lg rounded-full bg-white placeholder-gray-400 border border-gray-300 focus:border-black"
                        autocomplete="current-password" />

                    <button type="submit" form="adminLoginForm" class="hidden"></button>
                </form>
            </div>

            <!-- Arrow Submit -->
            <div class="flex justify-center mt-6">
                <button type="submit" form="adminLoginForm"
                    class="bg-gray-200 hover:bg-gray-300 p-3 rounded-full shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
            </div>

            <!-- Footer -->
            <div class="text-xs text-center text-gray-500 mt-6">
                <a href="admin_register.php" class="hover:underline block mb-2">Don’t have an admin account?
                    Register</a>
                <p class="text-gray-400">v109.9.1</p>
            </div>
        </div>
    </div>

    <!-- Right Background Panel -->
    <div class="h-full bg-main" style="width: 72.5%;"></div>

</body>

</html>