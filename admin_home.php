<?php
session_start();
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}
require_once 'db_connect.php';

// Fetch all users
$userStmt = $pdo->query("SELECT id, username, email, region, province, city, barangay, registered_at FROM users");
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all orders
$orderStmt = $pdo->query("SELECT * FROM orders ORDER BY pickup_date DESC");
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #18181b 0%, #23272f 100%);
            min-height: 100vh;
        }
        .glass {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(8px);
            border-radius: 1rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            border: 1px solid rgba(255, 70, 85, 0.08);
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ff4655;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            text-shadow: 0 2px 8px #000a;
        }
        .table-header {
            background: #18181b;
            color: #ff4655;
            font-weight: bold;
            font-size: 1rem;
        }
        .table-row {
            transition: background 0.2s;
        }
        .table-row:hover {
            background: #262626;
        }
        .pill {
            padding: 2px 12px;
            border-radius: 9999px;
            font-size: 0.9rem;
            font-weight: bold;
            color: #fff;
            display: inline-block;
        }
        .pill-delivered { background: #22c55e; }
        .pill-pending { background: #6b7280; }
        .pill-intransit { background: #eab308; color: #222; }
        .pill-cancelled { background: #ef4444; }
        .admin-header {
            background: #1a1a1a;
            border-radius: 0.75rem;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px #000a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header h1 {
            font-size: 2.2rem;
            font-weight: bold;
            color: #ff4655;
            letter-spacing: 2px;
        }
        .admin-header .admin-user {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 500;
            margin-right: 1.5rem;
        }
        .admin-header .logout-btn {
            background: #ff4655;
            color: #fff;
            padding: 0.5rem 1.2rem;
            border-radius: 0.5rem;
            font-weight: bold;
            transition: background 0.2s;
        }
        .admin-header .logout-btn:hover {
            background: #b91c1c;
        }
        .action-btn {
            padding: 0.3rem 0.9rem;
            border-radius: 0.4rem;
            font-size: 0.85rem;
            font-weight: 600;
            margin-right: 0.3rem;
            transition: background 0.2s;
        }
        .action-edit { background: #2563eb; color: #fff; }
        .action-edit:hover { background: #1d4ed8; }
        .action-delete { background: #ef4444; color: #fff; }
        .action-delete:hover { background: #b91c1c; }
        @media (max-width: 900px) {
            .admin-header { flex-direction: column; gap: 1rem; }
        }
    </style>
</head>

<body class="text-white min-h-screen p-6">
    <div class="admin-header glass">
        <h1>🛡️ Admin Dashboard</h1>
        <div class="flex items-center">
            <span class="admin-user">Welcome, <span class="text-red-400"><?= htmlspecialchars($_SESSION['admin_username']) ?></span></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Users Section -->
    <section class="mb-12">
        <div class="section-title">👥 Registered Users</div>
        <div class="overflow-x-auto glass p-6">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Username</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Region</th>
                        <th class="p-3 text-left">Province</th>
                        <th class="p-3 text-left">City</th>
                        <th class="p-3 text-left">Barangay</th>
                        <th class="p-3 text-left">Registered At</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="border-t border-gray-700 table-row">
                            <td class="p-3"><?= $user['id'] ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['region']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['province']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['city']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['barangay']) ?></td>
                            <td class="p-3"><?= $user['registered_at'] ?></td>
                            <td class="p-3 space-x-2">
                                <a href="edit_user.php?id=<?= $user['id'] ?>"
                                    class="action-btn action-edit">Edit</a>
                                <a href="delete_user.php?id=<?= $user['id'] ?>"
                                    onclick="return confirm('Are you sure you want to delete this user?');"
                                    class="action-btn action-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Orders Section -->
    <section>
        <div class="section-title">📦 Orders</div>
        <div class="overflow-x-auto glass p-6">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="p-3">Order ID</th>
                        <th class="p-3">Username</th>
                        <th class="p-3">Sender</th>
                        <th class="p-3">Recipient</th>
                        <th class="p-3">Item</th>
                        <th class="p-3">Weight</th>
                        <th class="p-3">Value</th>
                        <th class="p-3">Pickup</th>
                        <th class="p-3">Delivery</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php
                            $now = time();
                            $delivery_time = strtotime($order['delivery_date']);
                            $db_status = strtolower(trim($order['status'] ?? ''));
                            if ($db_status === 'cancelled') {
                                $status = 'cancelled';
                            } elseif ($delivery_time <= $now) {
                                $status = 'delivered';
                            } elseif ($db_status === 'in transit') {
                                $status = 'in transit';
                            } else {
                                $status = 'pending';
                            }
                            $statusClass = match ($status) {
                                'delivered' => 'pill pill-delivered',
                                'in transit' => 'pill pill-intransit',
                                'pending' => 'pill pill-pending',
                                'cancelled' => 'pill pill-cancelled',
                                default => 'pill'
                            };
                        ?>
                        <tr class="border-t border-gray-700 table-row">
                            <td class="p-3"><?= $order['id'] ?></td>
                            <td class="p-3"><?= htmlspecialchars($order['username']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($order['sender_name']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($order['recipient_name']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($order['item_name']) ?> (<?= $order['item_category'] ?>)</td>
                            <td class="p-3"><?= $order['weight'] ?> kg</td>
                            <td class="p-3">₱<?= number_format($order['value'], 2) ?></td>
                            <td class="p-3"><?= $order['pickup_date'] ?></td>
                            <td class="p-3"><?= $order['delivery_date'] ?></td>
                            <td class="p-3"><span class="<?= $statusClass ?>"><?= ucfirst($status) ?></span></td>
                            <td class="p-3 space-x-2">
                                <a href="edit_order.php?id=<?= $order['id'] ?>"
                                    class="action-btn action-edit">Edit</a>
                                <a href="delete_order.php?id=<?= $order['id'] ?>"
                                    onclick="return confirm('Are you sure you want to delete this order?');"
                                    class="action-btn action-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</body>
</html>