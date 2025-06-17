<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        header("Location: index.php?error=Please fill in all fields.");
        exit;
    }

    if (!file_exists("users.json")) {
        header("Location: index.php?error=No users found.");
        exit;
    }

    $users = json_decode(file_get_contents("users.json"), true);

    foreach ($users as $user) {
        if ($user["username"] === $username && password_verify($password, $user["password"])) {
            $_SESSION["username"] = $username;
            header("Location: home.php");
            exit;
        }
    }

    header("Location: index.php?error=Invalid username or password.");
    exit;
}
?>
