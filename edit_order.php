<?php
require_once 'db_connect.php';
session_start();

// Redirect if not admin
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit();
}

// Get order ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid order ID.");
}

$order_id = (int) $_GET['id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $sender_name = trim($_POST['sender_name']);
    $recipient_name = trim($_POST['recipient_name']);
    $item_name = trim($_POST['item_name']);
    $item_category = trim($_POST['item_category']);
    $weight = floatval($_POST['weight']);
    $value = floatval($_POST['value']);
    $pickup_date = $_POST['pickup_date'];
    $delivery_date = $_POST['delivery_date'];
    $status = trim($_POST['status']);

    $stmt = $pdo->prepare("UPDATE orders SET username = ?, sender_name = ?, recipient_name = ?, item_name = ?, item_category = ?, weight = ?, value = ?, pickup_date = ?, delivery_date = ?, status = ? WHERE id = ?");
    $stmt->execute([$username, $sender_name, $recipient_name, $item_name, $item_category, $weight, $value, $pickup_date, $delivery_date, $status, $order_id]);

    header("Location: admin_home.php?success=Order updated");
    exit();
}

// Fetch current order data
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Order</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center">
    <div class="bg-gray-800 p-8 rounded shadow-md w-full max-w-3xl">
        <h2 class="text-2xl font-bold text-red-400 mb-6">Edit Order #<?= $order['id'] ?></h2>

        <form method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1">Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($order['username']) ?>" required
                        class="w-full px-3 py-2 bg-gray-700 text-white rounded">
                </div>
                <div>
                    <label class="block mb-1">Sender Name</label>
                    <input type="text" name="sender_name" value="<?= htmlspecialchars($order['sender_name']) ?>"
                        class="w-full px-3 py-2 bg-gray-700 text-white rounded">
                </div>
                <div>
                    <label class="block mb-1">Recipient Name</label>
                    <input type="text" name="recipient_name" value="<?= htmlspecialchars($order['recipient_name']) ?>"
                        class="w-full px-3 py-2 bg-gray-700 text-white rounded">
                </div>
                <div>
                    <label class="block mb-1">Item Name</label>
                    <input type="text" name="item_name" value="<?= htmlspecialchars($order['item_name']) ?>"
                        class="w-full px-3 py-2 bg-gray-700 text-white rounded">
                </div>
                <div>
                    <label class="block mb-1">Item Category</label>
                    <input type="text" name="item_category" value="<?= htmlspecialchars($order['item_category']) ?>"
                        class="w-full px-3 py-2 bg-gray-700 text-white rounded">
                </div>
                <div>
                    <label class="block mb-1">Weight (kg)</label>
                    <input type="number" name="weight" step="0.01" value="<?= $order['weight'] ?>"
                        class="w-full px-3 py-2 bg-gray-700 text-white rounded">
                </div>
                <div>
                    <label class="block mb-1">Value (₱)</label>
                    <input type="number" name="value" step="0.01" value="<?= $order['value'] ?>"
                        class="w-full px-3 py-2 bg-gray-700 text-white rounded">
                </div>
                <div>
                    <label class="block mb-1">Pickup Date</label>
                    <input type="date" name="pickup_date" value="<?= $order['pickup_date'] ?>"
                        class="w-full px-3 py-2 bg-gray-700 text-white rounded">
                </div>
                <div>
                    <label class="block mb-1">Delivery Date</label>
                    <input type="date" name="delivery_date" value="<?= $order['delivery_date'] ?>"
                        class="w-full px-3 py-2 bg-gray-700 text-white rounded">
                </div>
                <div>
                    <label class="block mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 bg-gray-700 text-white rounded">
                        <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="In Transit" <?= $order['status'] === 'In Transit' ? 'selected' : '' ?>>In Transit
                        </option>
                        <option value="Delivered" <?= $order['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered
                        </option>
                        <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled
                        </option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded text-white">Update
                    Order</button>
                <a href="admin_home.php" class="text-gray-400 hover:text-white">Cancel</a>
            </div>
        </form>
    </div>
</body>

</html>