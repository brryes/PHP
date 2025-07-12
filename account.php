<?php
session_start();
require_once 'db_connect.php';

$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

if ($order_id) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND username = ?");
    $stmt->execute([$order_id, $username]);
    $order = $stmt->fetch();

    if (!$order) {
        echo "<h2 style='color:#ff4655;text-align:center;margin-top:2rem;'>Order not found or access denied.</h2>";
        echo "<div style='text-align:center;'><a href='account.php' style='color:#ff4655;text-decoration:underline;'>Back to Orders Overview</a></div>";
        exit;
    }

    $deliveryDate = $order['delivery_date'];
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE username = ? AND delivery_date = ?");
    $countStmt->execute([$username, $deliveryDate]);
    $totalOrders = $countStmt->fetchColumn();
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Order Details</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body {
                background: #1a1a1a;
                color: #fff;
                font-family: 'Oswald', sans-serif;
                padding: 2rem;
                padding-top: 6rem;
            }
            .order-card {
                background: #2a2a2a;
                border: 1px solid #444;
                border-radius: 1rem;
                padding: 2rem;
                margin: 2rem auto;
                max-width: 500px;
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
        <div class="order-card text-center" style="background:#1f1f1f;">
            <div class="order-section-title text-lg">Total Orders Placed</div>
            <div class="order-content text-2xl text-green-400 font-bold">
                <?= $totalOrders ?>
            </div>
        </div>

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
                Value: ₱<?= htmlspecialchars($order['value']) ?><br>
                <strong>Item:</strong> <?= isset($order['item_name']) ? htmlspecialchars($order['item_name']) : 'N/A' ?><br>
                <strong>Quantity:</strong> <?= isset($order['quantity']) ? htmlspecialchars($order['quantity']) : 'N/A' ?>
            </div>

            <div class="order-section-title">Sender:</div>
            <div class="order-content">
                <?= htmlspecialchars($order['sender_name']) ?> - <?= htmlspecialchars($order['sender_contact']) ?>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="account.php" class="back-btn">Go to Orders Overview</a>
            <a href="calendar.php" class="back-btn ml-4"> ← Back to Calendar</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE username = ? ORDER BY id DESC");
$stmt->execute([$username]);
$all_orders = $stmt->fetchAll();

// --- Status helpers ---
function isDelivered($order)
{
    if (!empty($order['pickup_date']) && !empty($order['delivery_date'])) {
        $now = time();
        return $now >= strtotime($order['delivery_date']) && strtolower($order['status']) !== 'cancelled';
    }
    return false;
}
function isCancelled($order)
{
    return strtolower($order['status']) === 'cancelled';
}
$delivered_orders = array_filter($all_orders, 'isDelivered');
$cancelled_orders = array_filter($all_orders, 'isCancelled');
$undelivered_orders = array_filter($all_orders, function ($order) {
    return (empty($order['delivery_date']) || (time() < strtotime($order['delivery_date']))) && strtolower($order['status']) !== 'cancelled';
});
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Parcels Overview</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <style>
        body {
            background: #1a1a1a;
            color: #fff;
            font-family: 'Oswald', sans-serif;
            padding: 2rem;
            padding-top: 6rem;
        }
        .title {
            font-size: 2.4rem;
            text-align: center;
            color: #ff4655;
            font-weight: bold;
            margin-bottom: 2rem;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
        }
        .column {
            flex: 1;
            min-width: 350px;
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

    <div class="title">Parcels Overview</div>

    <div class="container">
        <!-- Delivered Orders -->
        <div class="column">
            <h2 class="text-xl font-bold text-green-400 mb-4 text-center">✅ Delivered Parcels</h2>
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
                            Value: ₱<?= htmlspecialchars($order['value']) ?><br>
                            <strong>Item:</strong>
                            <?= isset($order['item_name']) ? htmlspecialchars($order['item_name']) : 'N/A' ?><br>
                            <strong>Quantity:</strong>
                            <?= isset($order['quantity']) ? htmlspecialchars($order['quantity']) : 'N/A' ?>
                        </div>
                        <div class="order-section-title">Sender:</div>
                        <div class="order-content">
                            <?= htmlspecialchars($order['sender_name']) ?> - <?= htmlspecialchars($order['sender_contact']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-gray-400 text-lg">No delivered parcels yet.</div>
            <?php endif; ?>
        </div>

        <!-- Undelivered Orders -->
        <div class="column">
            <h2 class="text-xl font-bold text-yellow-400 mb-4 text-center">🚚 Undelivered Parcels</h2>
            <?php if (count($undelivered_orders) > 0): ?>
                <?php foreach ($undelivered_orders as $order): ?>
                    <div class="order-card">
                        <div class="order-section-title">Expected Delivery Date:</div>
                        <div class="order-content"><?= htmlspecialchars($order['delivery_date'] ?? 'Not Set') ?></div>
                        <div class="order-section-title">Recipient:</div>
                        <div class="order-content">
                            <?= htmlspecialchars($order['recipient_name']) ?><br>
                            <?= htmlspecialchars($order['recipient_contact']) ?><br>
                            <?= htmlspecialchars($order['recipient_address']) ?>
                        </div>
                        <div class="order-section-title">Package:</div>
                        <div class="order-content">
                            <?= htmlspecialchars($order['item_category']) ?> - <?= htmlspecialchars($order['weight']) ?>kg<br>
                            Value: ₱<?= htmlspecialchars($order['value']) ?><br>
                            <strong>Item:</strong>
                            <?= isset($order['item_name']) ? htmlspecialchars($order['item_name']) : 'N/A' ?><br>
                            <strong>Quantity:</strong>
                            <?= isset($order['quantity']) ? htmlspecialchars($order['quantity']) : 'N/A' ?>
                        </div>
                        <div class="order-section-title">Sender:</div>
                        <div class="order-content">
                            <?= htmlspecialchars($order['sender_name']) ?> - <?= htmlspecialchars($order['sender_contact']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-gray-400 text-lg">All parcels are delivered.</div>
            <?php endif; ?>
        </div>

        <!-- Cancelled Orders -->
        <div class="column">
            <h2 class="text-xl font-bold text-red-400 mb-4 text-center">❌ Cancelled Parcels</h2>
            <?php if (count($cancelled_orders) > 0): ?>
                <?php foreach ($cancelled_orders as $order): ?>
                    <div class="order-card" style="opacity:0.7;">
                        <div class="order-section-title">Cancelled On:</div>
                        <div class="order-content"><?= htmlspecialchars($order['delivery_date'] ?? 'N/A') ?></div>
                        <div class="order-section-title">Recipient:</div>
                        <div class="order-content">
                            <?= htmlspecialchars($order['recipient_name']) ?><br>
                            <?= htmlspecialchars($order['recipient_contact']) ?><br>
                            <?= htmlspecialchars($order['recipient_address']) ?>
                        </div>
                        <div class="order-section-title">Package:</div>
                        <div class="order-content">
                            <?= htmlspecialchars($order['item_category']) ?> - <?= htmlspecialchars($order['weight']) ?>kg<br>
                            Value: ₱<?= htmlspecialchars($order['value']) ?><br>
                            <strong>Item:</strong>
                            <?= isset($order['item_name']) ? htmlspecialchars($order['item_name']) : 'N/A' ?><br>
                            <strong>Quantity:</strong>
                            <?= isset($order['quantity']) ? htmlspecialchars($order['quantity']) : 'N/A' ?>
                        </div>
                        <div class="order-section-title">Sender:</div>
                        <div class="order-content">
                            <?= htmlspecialchars($order['sender_name']) ?> - <?= htmlspecialchars($order['sender_contact']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-gray-400 text-lg">No cancelled parcels.</div>
            <?php endif; ?>
        </div>
    </div>

     <div class="text-center mt-10">
        <a href="home.php" class="back-btn">← Back to Home</a>
    </div>
</body>
</html>