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
</head>

<body class="bg-gray-900 text-white min-h-screen p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-red-500">Admin Dashboard</h1>
        <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
            Logout
        </a>
    </div>

    <!-- Users Section -->
    <section class="mb-10">
        <h2 class="text-xl font-semibold text-red-400 mb-4">Registered Users</h2>
        <div class="overflow-x-auto bg-gray-800 rounded shadow">
            <table class="w-full text-sm">
                <thead class="bg-gray-700 text-gray-300">
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
                        <tr class="border-t border-gray-700 hover:bg-gray-700">
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
                                    class="inline-block bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-white text-xs">Edit</a>
                                <a href="delete_user.php?id=<?= $user['id'] ?>"
                                    onclick="return confirm('Are you sure you want to delete this user?');"
                                    class="inline-block bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-white text-xs">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Orders Section -->
    <section>
        <h2 class="text-xl font-semibold text-red-400 mb-4">Orders</h2>
        <div class="overflow-x-auto bg-gray-800 rounded shadow">
            <table class="w-full text-sm">
                <thead class="bg-gray-700 text-gray-300">
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
                        <tr class="border-t border-gray-700 hover:bg-gray-700">
                            <td class="p-3"><?= $order['id'] ?></td>
                            <td class="p-3"><?= htmlspecialchars($order['username']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($order['sender_name']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($order['recipient_name']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($order['item_name']) ?> (<?= $order['item_category'] ?>)
                            </td>
                            <td class="p-3"><?= $order['weight'] ?> kg</td>
                            <td class="p-3">₱<?= number_format($order['value'], 2) ?></td>
                            <td class="p-3"><?= $order['pickup_date'] ?></td>
                            <td class="p-3"><?= $order['delivery_date'] ?></td>
                            <td class="p-3"><?= $order['status'] ?></td>
                            <td class="p-3 space-x-2">
                                <a href="edit_order.php?id=<?= $order['id'] ?>"
                                    class="inline-block bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-white text-xs">Edit</a>
                                <a href="delete_order.php?id=<?= $order['id'] ?>"
                                    onclick="return confirm('Are you sure you want to delete this order?');"
                                    class="inline-block bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-white text-xs">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</body>

</html>