<?php
session_start();
require_once 'db_connect.php';
$file = __DIR__ . '/purchases.json';
$purchases = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Fetch user details from database
$user = null;
if ($username) {
    $stmt = $pdo->prepare("SELECT username, email, region, province, city, barangay FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Valorant Inventory</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&display=swap');
        body {
            background: #181a20 url('images/storebg.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Oswald', Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
        }
        .inventory-title {
            font-family: 'Oswald', Arial, sans-serif;
            font-size: 2.2rem;
            color: #ff4655;
            letter-spacing: 0.12em;
            font-weight: 700;
            text-shadow: 0 2px 8px #000a;
        }
        .inventory-subtitle {
            color: #b0b0b0;
            font-size: 1.1rem;
            letter-spacing: 0.08em;
            margin-bottom: 2rem;
        }
        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .inventory-card {
            background: rgba(24,24,24,0.92);
            border: 1.5px solid #23272f;
            border-radius: 1.1rem;
            box-shadow: 0 2px 16px 0 #0008;
            transition: transform 0.18s, box-shadow 0.18s, border 0.18s;
            padding: 1rem 0.7rem 0.7rem 0.7rem;
            min-height: 110px;
            max-width: 270px;
            margin: 0 auto;
            cursor: pointer;
        }
        .inventory-card:hover {
            transform: translateY(-6px) scale(1.025);
            border-color: #ff4655;
            box-shadow: 0 8px 32px 0 #ff4655aa;
        }
        .purchase-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #b0b0b0;
            font-size: 0.98rem;
            margin-bottom: 0.7rem;
            font-weight: 500;
        }
        .purchase-meta i {
            color: #ff4655;
            margin-right: 0.4em;
        }
        .item-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .item-list li {
            margin-bottom: 0.4rem;
            background: rgba(34,34,44,0.7);
            border-radius: 0.5rem;
            padding: 0.4rem 0.7rem;
            color: #fff;
            font-size: 1.02rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            letter-spacing: 0.04em;
        }
        .item-list li .item-name {
            color: #ff4655;
            font-weight: 600;
        }
        .item-list li .item-section {
            color: #b0b07a;
            font-weight: 500;
            font-size: 0.98rem;
        }
        .item-list li .item-qty {
            color: #b0b0b0;
            font-size: 0.98rem;
            font-weight: 400;
        }
        .no-purchases {
            background: rgba(24,24,24,0.92);
            border: 1.5px dashed #ff4655;
            border-radius: 1.1rem;
            color: #b0b0b0;
            font-size: 1.2rem;
            padding: 2rem 1rem;
            text-align: center;
            max-width: 350px;
            margin: 0 auto;
        }
        .back-btn {
            display: inline-block;
            margin-top: 2.5rem;
            padding: 0.7rem 2rem;
            border-radius: 0.7rem;
            background: #23272f;
            color: #ff4655;
            font-family: 'Oswald', Arial, sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            box-shadow: 0 2px 12px #0006;
            border: 1.5px solid #23272f;
            transition: background 0.18s, color 0.18s, border 0.18s;
            text-decoration: none;
        }
        .back-btn:hover {
            background: #ff4655;
            color: #fff;
            border-color: #ff4655;
        }
        /* Fullscreen modal styles */
        .fullscreen-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.92);
            z-index: 50;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .fullscreen-modal.active {
            display: flex;
        }
        .fullscreen-content {
            background: #181a20;
            border-radius: 1.2rem;
            border: 2px solid #ff4655;
            box-shadow: 0 8px 32px 0 #ff4655aa;
            padding: 2.2rem 2rem 2rem 2rem;
            max-width: 370px;
            width: 95vw;
            position: relative;
            animation: fadeIn 0.2s;
        }
        .fullscreen-close {
            position: absolute;
            top: 1.1rem;
            right: 1.3rem;
            font-size: 2rem;
            color: #ff4655;
            background: none;
            border: none;
            cursor: pointer;
            transition: color 0.18s;
        }
        .fullscreen-close:hover {
            color: #fff;
        }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.97);} to { opacity: 1; transform: scale(1);} }
        /* Account details styles */
        .account-details {
            background: rgba(24,24,24,0.92);
            border: 1.5px solid #ff4655;
            border-radius: 1.1rem;
            color: #fff;
            font-size: 1.1rem;
            padding: 1.5rem 1.2rem 1.2rem 1.2rem;
            margin-bottom: 2.5rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 2px 16px 0 #0008;
        }
        .account-details h2 {
            color: #ff4655;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.7rem;
        }
        .account-details div {
            margin-bottom: 0.3rem;
        }
    </style>
</head>
<body class="py-12 px-4">
    <div class="max-w-2xl mx-auto">
        <?php if ($user): ?>
        <div class="account-details">
            <h2>Account Details</h2>
            <div><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></div>
            <div><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
            <div><strong>Region:</strong> <?= htmlspecialchars($user['region']) ?></div>
            <div><strong>Province:</strong> <?= htmlspecialchars($user['province']) ?></div>
            <div><strong>City:</strong> <?= htmlspecialchars($user['city']) ?></div>
            <div><strong>Barangay:</strong> <?= htmlspecialchars($user['barangay']) ?></div>
        </div>
        <?php endif; ?>
        <div class="mb-8 text-center">
            <div class="inventory-title flex items-center justify-center gap-4">
                <i class="fas fa-archive"></i>
                <?= htmlspecialchars($username) ?>'s Inventory
            </div>
            <div class="inventory-subtitle">Inventory of your orders</div>
            <div class="text-center">
                <a href="home.php" class="back-btn"><i class="fas fa-chevron-left mr-2"></i>Back to Menu</a>
            </div>
        </div>
        <div class="inventory-grid">
            <?php
            $hasPurchases = false;
            // Show most recent purchase first
            $purchases = array_reverse($purchases);
            foreach ($purchases as $index => $purchase):
                if ($username && $purchase['username'] !== $username) continue;
                $hasPurchases = true;
            ?>
            <div class="inventory-card" onclick="showFullscreen(<?= $index ?>)">
                <div class="purchase-meta">
                    <span><i class="fas fa-calendar-alt"></i><?= htmlspecialchars($purchase['date']) ?></span>
                    <span><i class="fas fa-credit-card"></i><?= htmlspecialchars(ucfirst($purchase['payment_method'])) ?></span>
                </div>
                <ul class="item-list">
                    <?php foreach ($purchase['cart'] as $item): ?>
                        <li>
                            <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                            <span class="item-section"><?= htmlspecialchars($item['section']) ?></span>
                            <span class="item-qty">x<?= intval($item['quantity']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <!-- Fullscreen Modal for this card -->
            <div id="fullscreen-<?= $index ?>" class="fullscreen-modal">
                <div class="fullscreen-content">
                    <button onclick="closeFullscreen(<?= $index ?>)" class="fullscreen-close" aria-label="Close"><i class="fas fa-times"></i></button>
                    <div class="inventory-title flex items-center gap-3 mb-2"><i class="fas fa-archive"></i> Purchase Details</div>
                    <div class="purchase-meta mb-4">
                        <span><i class="fas fa-calendar-alt"></i><?= htmlspecialchars($purchase['date']) ?></span>
                        <span><i class="fas fa-credit-card"></i><?= htmlspecialchars(ucfirst($purchase['payment_method'])) ?></span>
                    </div>
                    <ul class="item-list mb-4">
                        <?php foreach ($purchase['cart'] as $item): ?>
                            <li>
                                <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                                <span class="item-section"><?= htmlspecialchars($item['section']) ?></span>
                                <span class="item-qty">x<?= intval($item['quantity']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="text-center">
                        <button onclick="closeFullscreen(<?= $index ?>)" class="back-btn mt-2"><i class="fas fa-chevron-left mr-2"></i>Back</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (!$hasPurchases): ?>
                <div class="no-purchases col-span-full">
                    <div class="mb-2"><i class="fas fa-box-open fa-2x text-[#ff4655]"></i></div>
                    You haven't bought anything yet.<br>
                    <a href="store.php" class="text-[#ff4655] underline hover:text-[#b02a2a]">Go to Store</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function showFullscreen(idx) {
            document.getElementById('fullscreen-' + idx).classList.add('active');
        }
        function closeFullscreen(idx) {
            document.getElementById('fullscreen-' + idx).classList.remove('active');
        }
        // ESC key closes any open modal
        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") {
                document.querySelectorAll('.fullscreen-modal').forEach(el => el.classList.remove('active'));
            }
        });
        // Click outside modal content closes modal
        document.querySelectorAll('.fullscreen-modal').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) modal.classList.remove('active');
            });
        });