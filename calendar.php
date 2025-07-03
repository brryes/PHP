<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['username'])) {
  header('Location: account.php');
  exit;
}

$username = $_SESSION['username'];

// Fetch user orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE username = ?");
$stmt->execute([$username]);
$user_orders = $stmt->fetchAll();

$events = [];
foreach ($user_orders as $order) {
  if (!empty($order['delivery_date'])) {
    $events[] = [
      'title' => $order['recipient_name'] . ' (' . $order['item_category'] . ')',
      'start' => $order['delivery_date'],
      'id' => $order['id'],
    ];
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Delivery Calendar | ValorCrate</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #181818;
      color: #eee;
      margin: 0;
      padding: 0;
    }

    /* 🔺 Navigation Bar */
    nav {
      position: sticky;
      top: 0;
      background: rgba(17, 17, 17, 0.85);
      backdrop-filter: blur(8px);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      z-index: 99;
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

    h2 {
      text-align: center;
      color: #ff4655;
      margin: 30px 0 10px;
      font-size: 2rem;
    }

    #calendar {
      max-width: 900px;
      margin: 20px auto;
      background: #222;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 0 20px rgba(255, 70, 85, 0.08);
    }

    .fc-toolbar-title {
      color: #ff4655;
    }

    .fc-button {
      background-color: #ff4655 !important;
      border: none !important;
    }

    .fc-button:hover {
      background-color: #ff3242 !important;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var calendarEl = document.getElementById('calendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: <?= json_encode($events) ?>,
        eventClick: function (info) {
          window.open('account.php?order_id=' + info.event.id, '_blank');
        },
        dayMaxEvents: true
      });
      calendar.render();
    });
  </script>
</head>

<body>

  <!-- 🔺 Navbar -->
  <nav>
    <div class="logo">VALORCRATE</div>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="place_order.php">Order</a>
      <a href="calendar.php">Calendar</a>
      <a href="account.php">Account</a>
      <a href="logout.php">Logout</a>
    </div>
  </nav>

  <h2>Delivery Calendar</h2>
  <div id="calendar"></div>

</body>

</html>