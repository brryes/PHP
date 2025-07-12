<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: #0f0f0f;
            color: #f1f5f9;
            font-family: 'Oswald', sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 80px;
            /* space for navbar */
        }

        .profile-card {
            background: #1a1a1a;
            border: 2px solid #ff4655;
            border-radius: 1.25rem;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 0 25px rgba(255, 70, 85, 0.5);
        }

        .title {
            font-size: 2rem;
            color: #ff4655;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: bold;
            text-shadow: 0 0 5px rgba(255, 70, 85, 0.4);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px dashed #333;
        }

        .label {
            color: #94a3b8;
            font-weight: bold;
        }

        .value {
            color: #facc15;
            font-weight: bold;
        }

        .back-btn {
            display: block;
            margin: 2.5rem auto 0;
            text-align: center;
            background: #0f0f0f;
            border: 2px solid #ff4655;
            color: #ff4655;
            padding: 0.6rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
            box-shadow: 0 0 10px rgba(255, 70, 85, 0.2);
        }

        .back-btn:hover {
            background: #ff4655;
            color: white;
        }

        .value.text-2xl {
            text-shadow: 0 0 5px rgba(255, 70, 85, 0.6);
        }


        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(17, 17, 17, 0.85);
            backdrop-filter: blur(8px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }

        nav .logo {
            font-size: 1.6rem;
            color: #ff4655;
            font-weight: bold;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(255, 70, 85, 0.6);
        }

        nav .nav-links a {
            margin-left: 1.5rem;
            text-decoration: none;
            color: #ccc;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        nav .nav-links a:hover {
            color: #ff4655;
        }

        nav .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 0%;
            height: 2px;
            background-color: #ff4655;
            transition: width 0.3s ease-in-out;
        }

        nav .nav-links a:hover::after {
            width: 100%;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="profile-card">
        <div class="title">User Profile</div>

        <div class="info-row">
            <div class="label text-lg">Username:</div>
            <div class="value text-2xl text-pink-400 font-extrabold tracking-wide">
                <?= htmlspecialchars($user['username']) ?>
            </div>
        </div>

        <div class="info-row">
            <div class="label">Email:</div>
            <div class="value"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></div>
        </div>

        <div class="info-row">
            <div class="label">Registered Since:</div>
            <div class="value"><?= date('F j, Y', strtotime($user['created_at'] ?? '')) ?></div>
        </div>

        <div class="info-row">
            <div class="label">Region:</div>
            <div class="value"><?= htmlspecialchars($user['region'] ?? 'N/A') ?></div>
        </div>

        <div class="info-row">
            <div class="label">Province:</div>
            <div class="value"><?= htmlspecialchars($user['province'] ?? 'N/A') ?></div>
        </div>

        <div class="info-row">
            <div class="label">City / Municipality:</div>
            <div class="value"><?= htmlspecialchars($user['city'] ?? 'N/A') ?></div>
        </div>

        <div class="info-row">
            <div class="label">Barangay:</div>
            <div class="value"><?= htmlspecialchars($user['barangay'] ?? 'N/A') ?></div>
        </div>

        <a href="home.php" class="back-btn">← Back to Home</a>
    </div>
</body>

</html>