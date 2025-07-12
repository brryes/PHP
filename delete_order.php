// delete_order.php
<?php
require_once 'db_connect.php';

if (!isset($_GET['id'])) {
    die('Order ID not provided.');
}

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
$stmt->execute([$id]);

header("Location: admin_home.php");
exit();