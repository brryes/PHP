<?php
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
require_once 'db_connect.php';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE username = ? ORDER BY id DESC");
$stmt->execute([$username]);
$user_orders = $stmt->fetchAll();

$status_steps = [
    "Pending Pickup",
    "Picked Up",
    "In Hub",
    "Out for Delivery",
    "Delivered"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shipping Order Tracker</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <style>
        body {
            background: #0f1117 url('images/storebg.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Oswald', Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            color: #e0e0e0;
            position: relative;
        }
        header {
            text-align: center;
            padding: 4rem 1rem 2rem;
        }
        .tracker-title {
            font-size: 2.5rem;
            color: #ff4655;
            font-weight: 800;
            text-shadow: 0 2px 8px #000a;
        }
        .tracker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            justify-content: center;
            padding: 1rem;
        }
        .tracker-card {
            background: rgba(24, 24, 24, 0.95);
            border: 1px solid #2c2f36;
            border-radius: 1rem;
            box-shadow: 0 0 12px #000a;
            padding: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .tracker-card:hover {
            transform: scale(1.02);
            box-shadow: 0 0 20px #ff4655;
        }
        .tracker-section-title {
            color: #b0b0b0;
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }
        .tracker-address {
            font-size: 0.95rem;
            color: #fcd34d;
            margin-bottom: 1rem;
        }
        .tracker-steps {
            margin-top: 1.5rem;
        }
        .tracker-step {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        .tracker-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #2c2f36;
            color: #fff;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            border: 2px solid #ff4655;
        }
        .tracker-step.active .tracker-circle {
            background: #ff4655;
        }
        .tracker-step.completed .tracker-circle {
            background: #22c55e;
            border-color: #22c55e;
        }
        .tracker-step.completed .tracker-label {
            color: #22c55e;
        }
        .tracker-label {
            font-size: 1rem;
            color: #fff;
        }
        .print-summary {
            background: #1e1e1e;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.95rem;
            color: #f1f1f1;
        }
        .print-btn {
            background: #ff4655;
            color: #fff;
            padding: 0.4rem 1rem;
            margin-top: 0.5rem;
            border-radius: 0.3rem;
            border: none;
            cursor: pointer;
        }
        .print-btn:hover {
            background: #b02a2a;
        }
        .back-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: transparent;
            color: #ff4655;
            font-weight: bold;
            padding: 0.6rem 1.4rem;
            border: 2px solid #ff4655;
            border-radius: 0.5rem;
            transition: 0.2s;
            z-index: 50;
        }
        .back-btn:hover {
            background: #ff4655;
            color: #fff;
        }
    </style>
</head>
<body>
    <header>
        <div class="tracker-title"><i class="fas fa-shipping-fast"></i> Shipping Order Tracker</div>
    </header>

    <div class="px-4">
        <?php if (count($user_orders) > 0): ?>
            <div class="tracker-grid">
                <!-- Cards -->
                <?php foreach ($user_orders as $order): ?>
                    <!-- Logic for status -->
                    <?php
                    $status_idx = 0;
                    if (!empty($order['pickup_date'])) {
                        $pickup_time = strtotime($order['pickup_date']);
                        $now = time();
                        if ($now >= $pickup_time) $status_idx = 1;
                        if ($now >= strtotime($order['pickup_date'] . ' +1 day')) $status_idx = 2;
                        if ($now >= strtotime($order['pickup_date'] . ' +2 days')) $status_idx = 3;
                        if (!empty($order['delivery_date']) && $now >= strtotime($order['delivery_date'])) $status_idx = 4;
                    }
                    ?>
                    <div class="tracker-card">
                        <div class="tracker-section-title">Pickup Date: <span class="text-red-500"><?= htmlspecialchars($order['pickup_date'] ?? '') ?></span></div>
                        <div class="tracker-section-title">Delivery Date: <span class="text-gray-300"><?= htmlspecialchars($order['delivery_date'] ?? '') ?></span></div>
                        <div class="tracker-section-title">Recipient:</div>
                        <div class="tracker-address">
                            <?= htmlspecialchars($order['recipient_name'] ?? '') ?><br>
                            <?= htmlspecialchars($order['recipient_contact'] ?? '') ?><br>
                            <?= htmlspecialchars($order['recipient_address'] ?? '') ?><br>
                            <?= htmlspecialchars($order['recipient_barangay'] ?? '') ?><?= $order['recipient_barangay'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['recipient_city'] ?? '') ?><?= $order['recipient_city'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['recipient_province'] ?? '') ?><?= $order['recipient_province'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['recipient_region'] ?? '') ?>
                        </div>
                        <div class="tracker-section-title">Package:</div>
                        <div class="tracker-address">
                            Category: <?= htmlspecialchars($order['item_category'] ?? '') ?><br>
                            Weight: <?= htmlspecialchars($order['weight'] ?? '') ?> kg<br>
                            Value: ₱<?= htmlspecialchars($order['value'] ?? '') ?><br>
                            Pickup Time: <?= htmlspecialchars($order['pickup_time'] ?? '') ?><br>
                            Remarks: <?= htmlspecialchars($order['remarks'] ?? '') ?>
                        </div>
                        <div class="tracker-steps">
                            <?php foreach ($status_steps as $i => $step): ?>
                                <div class="tracker-step <?= $i < $status_idx ? 'completed' : ($i == $status_idx ? 'active' : '') ?>">
                                    <div class="tracker-circle">
                                        <?= $i < $status_idx ? '<i class="fas fa-check"></i>' : $i + 1 ?>
                                    </div>
                                    <div class="tracker-label"><?= $step ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="print-summary">
                            <strong>Print Summary:</strong><br>
                            Sender: <?= htmlspecialchars($order['sender_name'] ?? '') ?>, <?= htmlspecialchars($order['sender_contact'] ?? '') ?><br>
                            <?= htmlspecialchars($order['sender_address'] ?? '') ?>,<br>
                            <?= htmlspecialchars($order['sender_barangay'] ?? '') ?><?= $order['sender_barangay'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['sender_city'] ?? '') ?><?= $order['sender_city'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['sender_province'] ?? '') ?><?= $order['sender_province'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['sender_region'] ?? '') ?><br>
                            Package: <?= htmlspecialchars($order['item_category'] ?? '') ?>,
                            <?= htmlspecialchars($order['weight'] ?? '') ?>kg,
                            ₱<?= htmlspecialchars($order['value'] ?? '') ?>,<br>
                            Pickup: <?= htmlspecialchars($order['pickup_date'] ?? '') ?> <?= htmlspecialchars($order['pickup_time'] ?? '') ?>,
                            Delivery: <?= htmlspecialchars($order['delivery_date'] ?? '') ?><br>
                            Remarks: <?= htmlspecialchars($order['remarks'] ?? '') ?>
                            <br>
                            <button class="print-btn" onclick="printOrder(this)">🖨️ Print</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center text-lg text-gray-300 mt-8">
                <i class="fas fa-box-open fa-2x text-red-500"></i><br>
                No shipping orders found.<br>
                <a href="place_order.php" class="text-red-500 underline hover:text-red-400">Go to Place Order</a>
            </div>
        <?php endif; ?>
    </div>

    <a href="home.php" class="back-btn"><i class="fas fa-chevron-left"></i> Back to Menu</a>
</body>
</html>
