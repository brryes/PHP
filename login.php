<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        header("Location: index.php?error=Please fill in all fields.");
        exit;
    }

    // Fetch user from database using PDO
$stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION["username"] = $user['username'];
    $_SESSION["user_id"] = $user['id'];
    header("Location: home.php");
    exit;
}

    header("Location: index.php?error=Invalid username or password.");
    exit;
}
?>