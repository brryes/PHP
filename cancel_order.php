<?php
// filepath: c:\xampp\htdocs\PHP\cancel_order.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['username']) || !isset($_POST['order_id'])) {
    header('Location: order_tracker.php');
    exit;
}

$order_id = $_POST['order_id'];
$username = $_SESSION['username'];

// Only allow cancelling user's own order and if not already delivered/cancelled
$stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND username = ? AND (status IS NULL OR status NOT IN ('delivered','cancelled'))");
$stmt->execute([$order_id, $username]);

header('Location: order_tracker.php');
exit;