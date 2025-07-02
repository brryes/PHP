<?php
session_start();
require_once 'db_connect.php';

$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE username = ? ORDER BY id DESC");
$stmt->execute([$username]);
$all_orders = $stmt->fetchAll();

$selected_date = isset($_GET['date']) ? $_GET['date'] : null;

// Filter orders delivered on the selected date (if set)
$filtered_orders = [];
if ($selected_date) {
    foreach ($all_orders as $order) {
        if (!empty($order['delivery_date']) && date('Y-m-d', strtotime($order['delivery_date'])) === $selected_date) {
            $filtered_orders[] = $order;
        }
    }
} else {
    // Default: Show all delivered orders
    function isDelivered($order) {
        if (!empty($order['pickup_date']) && !empty($order['delivery_date'])) {
            $now = time();
            return $now >= strtotime($order['delivery_date']);
        }
        return false;
    }
    $filtered_orders = array_filter($all_orders, 'isDelivered');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Delivered Orders</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <style>
        body {
            background: #1a1a1a;
            color: #fff;
            font-family: 'Oswald', sans-serif;
            padding: 2rem;
        }
        .title {
            font-size: 2.4rem;
            text-align: center;
            color: #ff4655;
            font-weight: bold;
            margin-bottom: 2rem;
        }
        .order-card {
            background: #2a2a2a;
            border: 1px solid #444;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0 10px #000;
        }
        .order-section-title {
            font-weight: 600;
            color: #b0b0b0;
            margin-bottom: 0.5rem;
        }
        .order-content {
            margin-bottom: 1rem;
            color: #fcd34d;
        }
        .back-btn {
            display: inline-block;
            background: transparent;
            border: 2px solid #ff4655;
            color: #ff4655;
            padding: 0.6rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: bold;
            transition: 0.2s;
        }
        .back-btn:hover {
            background: #ff4655;
            color: #fff;
        }
        .calendar-form {
            text-align: center;
            margin-bottom: 2rem;
        }
        .calendar-input {
            background: #2a2a2a;
            color: #fff;
            border: 1px solid #ff4655;
            border-radius: 0.5rem;
            padding: 0.5rem;
        }
        .calendar-button {
            background: #ff4655;
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: bold;
            cursor: pointer;
            margin-left: 1rem;
        }
        .calendar-button:hover {
            background: #e53e3e;
        }
    </style>
</head>
<body>
    <div class="title">📦 Delivered Parcels</div>

    <div class="calendar-form">
        <form method="GET">
            <label for="date">📅 Select Delivery Date:</label>
            <input type="date" id="date" name="date" class="calendar-input" value="<?= htmlspecialchars($selected_date ?? '') ?>">
            <button type="submit" class="calendar-button">View</button>
        </form>
    </div>

    <?php if (count($filtered_orders) > 0): ?>
        <?php foreach ($filtered_orders as $order): ?>
            <div class="order-card">
                <div class="order-section-title">Delivery Date:</div>
                <div class="order-content"><?= htmlspecialchars($order['delivery_date']) ?></div>

                <div class="order-section-title">Recipient:</div>
                <div class="order-content">
                    <?= htmlspecialchars($order['recipient_name']) ?><br>
                    <?= htmlspecialchars($order['recipient_contact']) ?><br>
                    <?= htmlspecialchars($order['recipient_address']) ?>
                </div>

                <div class="order-section-title">Package:</div>
                <div class="order-content">
                    <?= htmlspecialchars($order['item_category']) ?> - <?= htmlspecialchars($order['weight']) ?>kg<br>
                    Value: ₱<?= htmlspecialchars($order['value']) ?>
                </div>

                <div class="order-section-title">Sender:</div>
                <div class="order-content">
                    <?= htmlspecialchars($order['sender_name']) ?> - <?= htmlspecialchars($order['sender_contact']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center text-gray-400 text-lg">
            <?= $selected_date ? "No parcels found for $selected_date." : "No delivered parcels yet." ?>
        </div>
    <?php endif; ?>

    <div class="text-center mt-10">
        <a href="home.php" class="back-btn">← Back to Menu</a>
    </div>
</body>
</html>
