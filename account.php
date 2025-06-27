<?php
session_start();
require_once 'db_connect.php';

$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE username = ? ORDER BY id DESC");
$stmt->execute([$username]);
$all_orders = $stmt->fetchAll();

function isDelivered($order) {
    if (!empty($order['pickup_date']) && !empty($order['delivery_date'])) {
        $now = time();
        return $now >= strtotime($order['delivery_date']);
    }
    return false;
}
$delivered_orders = array_filter($all_orders, 'isDelivered');
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
    </style>
</head>
<body>
    <div class="title"><i class="fas fa-box"></i> Delivered Orders</div>

    <?php if (count($delivered_orders) > 0): ?>
        <?php foreach ($delivered_orders as $order): ?>
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
            No delivered orders yet.
        </div>
    <?php endif; ?>

    <div class="text-center mt-10">
        <a href="home.php" class="back-btn"><i class="fas fa-chevron-left mr-2"></i>Back to Menu</a>
    </div>
</body>
</html>
