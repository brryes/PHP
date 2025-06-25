<?php
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
require_once 'db_connect.php';

// Fetch orders for this user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE username = ? ORDER BY id DESC");
$stmt->execute([$username]);
$user_orders = $stmt->fetchAll();

// Status steps for shipping
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
            background: #181a20 url('images/storebg.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Oswald', Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
        }
        .tracker-title { font-size: 2.2rem; color: #ff4655; font-weight: 700; text-shadow: 0 2px 8px #000a; }
        .tracker-grid { display: flex; flex-wrap: wrap; gap: 1.5rem; justify-content: center; max-width: 100vw; padding-bottom: 1rem; margin: 0 auto; }
        .tracker-card { background: rgba(24, 24, 24, 0.92); border: 1.5px solid #23272f; border-radius: 1.1rem; box-shadow: 0 2px 16px 0 #0008; padding: 2rem 1.5rem 1.5rem 1.5rem; min-width: 340px; max-width: 370px; margin: 0 auto; flex-shrink: 0; transition: box-shadow 0.2s, transform 0.2s; position: relative; cursor: pointer; }
        .tracker-card:hover, .tracker-card:focus { box-shadow: 0 0 24px 4px #ff4655, 0 2px 16px 0 #0008; transform: scale(1.04); z-index: 10; outline: none; }
        .tracker-section-title { color: #b0b0b0; font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; letter-spacing: 0.08em; }
        .tracker-steps { margin-top: 2rem; }
        .tracker-step { display: flex; align-items: center; margin-bottom: 1.2rem; }
        .tracker-circle { width: 32px; height: 32px; border-radius: 50%; background: #23272f; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; margin-right: 1rem; border: 2px solid #ff4655; transition: background 0.2s, border 0.2s; }
        .tracker-step.active .tracker-circle { background: #ff4655; border-color: #ff4655; }
        .tracker-label { font-size: 1.1rem; color: #fff; letter-spacing: 0.08em; }
        .tracker-step.completed .tracker-circle { background: #2ecc71; border-color: #2ecc71; }
        .tracker-step.completed .tracker-label { color: #2ecc71; }
        .no-orders { background: rgba(24, 24, 24, 0.92); border: 1.5px dashed #ff4655; border-radius: 1.1rem; color: #b0b0b0; font-size: 1.2rem; padding: 2rem 1rem; text-align: center; max-width: 350px; margin: 0 auto; }
        .back-btn { display: inline-block; margin-top: 1.5rem; padding: 0.7rem 2rem; border-radius: 0.7rem; background: #23272f; color: #ff4655; font-size: 1.1rem; font-weight: 700; letter-spacing: 0.12em; box-shadow: 0 2px 12px #0006; border: 1.5px solid #23272f; transition: background 0.18s, color 0.18s, border 0.18s; text-decoration: none; }
        .back-btn:hover { background: #ff4655; color: #fff; border-color: #ff4655; }
        .tracker-address { color: #ffbb00 !important; font-size: 1.02rem; font-weight: 500; margin-bottom: 1rem; white-space: pre-line; }
        .print-summary { background: #23272f; border-radius: 0.7rem; padding: 1rem; margin-top: 1rem; color: #fff; font-size: 0.98rem; }
        .print-btn { background: #ff4655; color: #fff; padding: 0.5rem 1.2rem; border-radius: 0.5rem; margin-top: 0.7rem; border: none; font-weight: bold; cursor: pointer; }
        .print-btn:hover { background: #b02a2a; }
    </style>
</head>
<body class="py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8 text-center">
            <div class="tracker-title flex items-center justify-center gap-4">
                <i class="fas fa-shipping-fast"></i>
                Shipping Order Tracker
            </div>
            <div class="text-center">
                <a href="home.php" class="back-btn"><i class="fas fa-chevron-left mr-2"></i>Back to Menu</a>
            </div>
        </div>
        <?php if (count($user_orders) > 0): ?>
            <div class="tracker-grid">
                <?php foreach ($user_orders as $order): ?>
                    <?php
                    // Simulate status progression based on pickup/delivery dates
                    $status_idx = 0;
                    if (!empty($order['pickup_date'])) {
                        $pickup_time = strtotime($order['pickup_date']);
                        $now = time();
                        if ($now >= $pickup_time) $status_idx = 1; // Picked Up
                        if ($now >= strtotime($order['pickup_date'] . ' +1 day')) $status_idx = 2; // In Hub
                        if ($now >= strtotime($order['pickup_date'] . ' +2 days')) $status_idx = 3; // Out for Delivery
                        if (!empty($order['delivery_date']) && $now >= strtotime($order['delivery_date'])) $status_idx = 4; // Delivered
                    }
                    ?>
                    <div class="tracker-card" tabindex="0" onclick="showOrderModal(this)">
                        <div class="tracker-section-title mb-1"><i class="fas fa-calendar-alt"></i>
                            Pickup Date: <span class="text-[#ff4655]"><?= htmlspecialchars($order['pickup_date'] ?? '') ?></span>
                        </div>
                        <div class="tracker-section-title mb-1"><i class="fas fa-calendar-check"></i>
                            Delivery Date: <span class="text-[#b0b0b0]"><?= htmlspecialchars($order['delivery_date'] ?? '') ?></span>
                        </div>
                        <div class="tracker-section-title mb-1"><i class="fas fa-user"></i> Recipient:</div>
                        <div class="tracker-address">
                            <?= htmlspecialchars($order['recipient_name'] ?? '') ?><br>
                            <?= htmlspecialchars($order['recipient_contact'] ?? '') ?><br>
                            <?= htmlspecialchars($order['recipient_address'] ?? '') ?><br>
                            <?= htmlspecialchars($order['recipient_barangay'] ?? '') ?><?= $order['recipient_barangay'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['recipient_city'] ?? '') ?><?= $order['recipient_city'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['recipient_province'] ?? '') ?><?= $order['recipient_province'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['recipient_region'] ?? '') ?>
                        </div>
                        <div class="tracker-section-title mb-1"><i class="fas fa-box"></i> Package:</div>
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
                            <?= htmlspecialchars($order['sender_address'] ?? '') ?>,
                            <?= htmlspecialchars($order['sender_barangay'] ?? '') ?><?= $order['sender_barangay'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['sender_city'] ?? '') ?><?= $order['sender_city'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['sender_province'] ?? '') ?><?= $order['sender_province'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['sender_region'] ?? '') ?><br>
                            Recipient: <?= htmlspecialchars($order['recipient_name'] ?? '') ?>, <?= htmlspecialchars($order['recipient_contact'] ?? '') ?><br>
                            <?= htmlspecialchars($order['recipient_address'] ?? '') ?>,
                            <?= htmlspecialchars($order['recipient_barangay'] ?? '') ?><?= $order['recipient_barangay'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['recipient_city'] ?? '') ?><?= $order['recipient_city'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['recipient_province'] ?? '') ?><?= $order['recipient_province'] ? ', ' : '' ?>
                            <?= htmlspecialchars($order['recipient_region'] ?? '') ?><br>
                            Package: <?= htmlspecialchars($order['item_category'] ?? '') ?>,
                            <?= htmlspecialchars($order['weight'] ?? '') ?>kg,
                            ₱<?= htmlspecialchars($order['value'] ?? '') ?>,
                            <?= htmlspecialchars($order['pickup_time'] ?? '') ?>,
                            <?= htmlspecialchars($order['pickup_date'] ?? '') ?>,
                            Delivery: <?= htmlspecialchars($order['delivery_date'] ?? '') ?><br>
                            Remarks: <?= htmlspecialchars($order['remarks'] ?? '') ?>
                            <br>
                            <button class="print-btn" onclick="printOrder(this)">🖨️ Print</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-orders col-span-full">
                <div class="mb-2"><i class="fas fa-box-open fa-2x text-[#ff4655]"></i></div>
                No shipping orders found.<br>
                <a href="place_order.php" class="text-[#ff4655] underline hover:text-[#b02a2a]">Go to Place Order</a>
            </div>
        <?php endif; ?>
    </div>
    <script>
    // Print only the summary of the selected order
    function printOrder(btn) {
        event.stopPropagation();
        let summary = btn.parentElement.innerHTML;
        let win = window.open('', '', 'width=700,height=600');
        win.document.write('<html><head><title>Order Print Summary</title>');
        win.document.write('<style>body{font-family:sans-serif;padding:2em;} strong{color:#ff4655;}</style>');
        win.document.write('</head><body>');
        win.document.write('<h2>Order Print Summary</h2>');
        win.document.write(summary.replace(/<button.*<\/button>/, ''));
        win.document.write('</body></html>');
        win.document.close();
        win.print();
    }

    // Show fullscreen modal with order details
    function showOrderModal(card) {
        var modal = document.getElementById('orderModal');
        var content = document.getElementById('modalContent');
        content.innerHTML = '<button onclick="closeModal()" style="position:absolute; top:1.2rem; right:1.2rem; background:#ff4655; color:#fff; border:none; border-radius:50%; width:2.2rem; height:2.2rem; font-size:1.3rem; cursor:pointer;">&times;</button>' + card.innerHTML;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        document.getElementById('orderModal').style.display = 'none';
        document.body.style.overflow = '';
    }
    </script>
    <!-- Modal for fullscreen order details -->
    <div id="orderModal" style="display:none; position:fixed; z-index:1000; top:0; left:0; width:100vw; height:100vh; background:rgba(24,24,24,0.98); overflow:auto; align-items:center; justify-content:center; display:flex;">
        <div id="modalContent" style="max-width:700px; width:95vw; margin:3rem auto; background:rgba(30,30,30,1); border-radius:1.2rem; box-shadow:0 4px 32px #000a; padding:2.5rem 2rem; color:#fff; position:relative;">
            <button onclick="closeModal()" style="position:absolute; top:1.2rem; right:1.2rem; background:#ff4655; color:#fff; border:none; border-radius:50%; width:2.2rem; height:2.2rem; font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>
    </div>
</body>
</html>