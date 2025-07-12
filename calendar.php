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

// Prepare events for FullCalendar
$events = [];
foreach ($user_orders as $order) {
  if (!empty($order['delivery_date']) && strtotime($order['delivery_date']) !== false) {
    $now = time();
    $delivery_time = strtotime($order['delivery_date']);
    $db_status = strtolower(trim($order['status'] ?? ''));

    // --- STATUS LOGIC ---
    if ($db_status === 'cancelled') {
      $status = 'cancelled';
    } elseif ($delivery_time <= $now) {
      $status = 'delivered';
    } elseif ($db_status === 'in transit') {
      $status = 'in transit';
    } else {
      // If not cancelled, not delivered, not in transit, and delivery is in the future, it's pending
      $status = 'pending';
    }

    $color = match ($status) {
      'delivered' => '#22c55e',
      'in transit' => '#eab308',
      'pending' => '#6b7280',
      'cancelled' => '#ef4444',
      default => '#3b82f6'
    };
    $events[] = [
      'title' => $order['recipient_name'] . ' (' . $order['item_category'] . ')',
      'start' => $order['delivery_date'],
      'id' => $order['id'],
      'color' => $color,
      'status' => $status, // <-- for filtering in JS
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


    #filters {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    #filters label {
      font-weight: bold;
      color: #ff4655;
    }

    #filters select {
      padding: 6px 12px;
      background: #2a2a2a;
      color: #eee;
      border: 1px solid #444;
      border-radius: 6px;
    }

    #filterSection {
      max-width: 900px;
      margin: 0 auto;
      padding: 0 20px 8px;
      text-align: left;
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

    #filterContainer {
      max-width: 900px;
      margin: 0 auto;
      padding: 0 20px 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    #filters {
      display: flex;
      align-items: center;
      gap: 10px;
    }


    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.85);
      backdrop-filter: blur(4px);
    }

    .modal-content {
      background-color: #1e1e1e;
      color: #eee;
      margin: 8% auto;
      padding: 30px;
      border-radius: 12px;
      width: 90%;
      max-width: 640px;
      box-shadow: 0 0 30px rgba(255, 70, 85, 0.2);
      position: relative;
      border: 1px solid #333;
    }

    .close-btn {
      position: absolute;
      top: 14px;
      right: 20px;
      color: #fff;
      font-size: 24px;
      font-weight: bold;
      cursor: pointer;
      transition: color 0.2s;
    }

    .close-btn:hover {
      color: #ff4655;
    }

    .order-list {
      list-style: none;
      padding: 0;
      margin-top: 1rem;
    }

    #openTrackerBtn {
      padding: 8px 14px;
      background-color: #ff4655;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: transform 0.2s ease, background-color 0.3s ease, box-shadow 0.2s ease;
    }

    #trackerButtonWrapper {
      display: flex;
      justify-content: flex-end;
    }

    #openTrackerBtn:hover {
      background-color: #ff3242;
      transform: scale(1.05);
      box-shadow: 0 0 10px rgba(255, 70, 85, 0.5);
    }

    @media (max-width: 600px) {
      #filterContainer {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }

      #openTrackerBtn {
        align-self: flex-end;
      }
    }

    .custom-event-hover {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border-radius: 6px;
    }

    .custom-event-hover:hover {
      transform: scale(1.05);
      box-shadow: 0 0 10px rgba(255, 70, 85, 0.5);
      z-index: 10;
    }
  </style>
</head>

<body>

  <nav>
    <div class="logo">VALORCRATE</div>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="place_order.php">Place Order</a>
      <a href="order_tracker.php">Order Tracker</a>
      <a href="calendar.php">Calendar</a>
      <a href="account.php">Account</a>
      <a href="logout.php">Logout</a>
    </div>
  </nav>

  <h2>Delivery Calendar</h2>
  <div id="filterContainer">
    <div id="filters">
      <label for="statusFilter">Filter by Status:</label>
      <select id="statusFilter">
        <option value="all">All</option>
        <option value="delivered">Delivered</option>
        <option value="in transit">In Transit</option>
        <option value="pending">Pending</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>

    <div id="trackerButtonWrapper">
      <button id="openTrackerBtn">Track My Orders</button>
    </div>
  </div>



  <div id="orderCount"
    style="max-width: 900px; margin: 0 auto; text-align: left; padding: 0 20px; font-size: 1rem; color: #ccc;">
    Orders this month: <span id="orderCountValue">0</span>
  </div>


  <div id="calendar"></div>

  <div id="trackerModal" class="modal">
    <div class="modal-content">
      <span class="close-btn">&times;</span>
      <h3 class="text-2xl font-semibold text-pink-500 border-b border-gray-600 pb-2 mb-4">📦 Your Orders</h3>
      <?php if (count($user_orders) > 0): ?>
        <ul class="order-list">
          <?php foreach ($user_orders as $order): ?>
            <li
              style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;margin-bottom:10px;background:#2a2a2a;border-radius:8px;">
              <div style="font-size: 0.9rem; color: #ccc;">
                <div style="font-weight: bold; color: #fff;"><?= htmlspecialchars($order['recipient_name']) ?></div>
                <div><?= htmlspecialchars($order['item_category']) ?> • <?= htmlspecialchars($order['delivery_date']) ?>
                </div>
              </div>
              <div>
                <?php
                // Use the same logic as above for status
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
                $statusColor = match ($status) {
                  'delivered' => '#22c55e',
                  'in transit' => '#eab308',
                  'pending' => '#6b7280',
                  'cancelled' => '#ef4444',
                  default => '#3b82f6'
                };
                ?>
                <span
                  style="background:<?= $statusColor ?>;color:white;padding:5px 12px;border-radius:9999px;font-size:0.75rem;">
                  <?= ucfirst($status) ?>
                </span>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="text-gray-400 text-sm">No orders found.</div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');
      const statusFilter = document.getElementById('statusFilter');
      let rawEvents = <?= json_encode($events) ?>;

      function cloneEvents(events) {
        return events.map(e => ({ ...e }));
      }

      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: cloneEvents(rawEvents),
        eventClick: function (info) {
          window.open('account.php?order_id=' + info.event.id, '_blank');
        },
        dayMaxEvents: true,
        datesSet: updateOrderCount,
        eventDidMount: function (info) {
          // Set tooltip with recipient name and delivery date
          const tooltip = `Recipient: ${info.event.title}\nDelivery Date: ${info.event.start.toLocaleDateString()}`;
          info.el.setAttribute('title', tooltip);
          info.el.classList.add('custom-event-hover');
        }
      });


      calendar.render();

      function updateOrderCount(info) {
        const start = new Date(info.start);
        const end = new Date(info.end);
        let count = 0;
        calendar.getEvents().forEach(event => {
          const date = new Date(event.start);
          if (date >= start && date < end) count++;
        });
        document.getElementById('orderCountValue').textContent = count;
      }

      statusFilter.addEventListener('change', () => {
        const selected = statusFilter.value.toLowerCase();
        const filtered = selected === 'all'
          ? rawEvents
          : rawEvents.filter(e => (e.status || '').toLowerCase() === selected);
        calendar.removeAllEvents();
        calendar.addEventSource(cloneEvents(filtered));
      });

      document.getElementById('openTrackerBtn').addEventListener('click', () => {
        document.getElementById('trackerModal').style.display = 'block';
      });

      document.querySelector('.close-btn').addEventListener('click', () => {
        document.getElementById('trackerModal').style.display = 'none';
      });

      window.addEventListener('click', e => {
        if (e.target === document.getElementById('trackerModal')) {
          document.getElementById('trackerModal').style.display = 'none';
        }
      });
    });
  </script>
</body>

</html>