<?php
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
require_once 'db_connect.php';

// Redirect to login if not logged in
if (!$username) {
    header('Location: account.php');
    exit;
}

// Fetch all orders for the user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE username = ?");
$stmt->execute([$username]);
$user_orders = $stmt->fetchAll();

// Prepare events for FullCalendar
$events = [];
foreach ($user_orders as $order) {
    if (!empty($order['delivery_date'])) {
        $events[] = [
            'title' => $order['recipient_name'] . ' (' . $order['item_category'] . ')',
            'start' => $order['delivery_date'],
            'id'    => $order['id'],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <meta charset='utf-8' />
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.css" rel="stylesheet">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js'></script>
    <style>
      body { font-family: Arial, sans-serif; background: #181818; color: #eee; margin: 0; }
      #calendar { max-width: 900px; margin: 40px auto; background: #222; border-radius: 12px; padding: 20px; }
      .fc-toolbar-title { color: #ff4655; }
    </style>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          events: <?= json_encode($events) ?>,
          eventClick: function(info) {
            // Open order details in account.php
            window.open('account.php?order_id=' + info.event.id, '_blank');
          },
          dayMaxEvents: true,
        });
        calendar.render();
      });
    </script>
  </head>
  <body>
    <div id='calendar'></div>
  </body>