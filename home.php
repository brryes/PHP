<?php
session_start();
if (!isset($_SESSION["username"])) {
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ValorCrate Menu</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      background: #111827; /* Tailwind gray-900 */
      font-family: 'Oswald', Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .header {
      font-size: 3rem;
      font-weight: 600;
      text-align: center;
      margin-bottom: 24px;
      color: #ff4655;
      letter-spacing: 1px;
    }

    .menu-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 32px 32px;
      justify-content: center;
      align-items: center;
      width: 400px;
    }

    .menu-btn {
      background: #fff;
      color: #ff4655;
      font-size: 2.5rem;
      font-weight: 600;
      border: 2px solid #ff4655;
      border-radius: 12px;
      padding: 48px 0;
      text-align: center;
      cursor: pointer;
      transition: background 0.15s, color 0.15s, box-shadow 0.15s;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 200px;
      min-height: 120px;
    }

    .menu-btn:hover {
      background: #ff4655;
      color: #fff;
      box-shadow: 0 4px 16px rgba(255,70,85,0.13);
    }

    .footer {
      text-align: center;
      font-size: 1rem;
      color: #ff4655;
      margin-top: 24px;
      letter-spacing: 1px;
    }

    @media (max-width: 700px) {
      .menu-grid {
        width: 95vw;
        grid-template-columns: 1fr;
        gap: 24px;
      }
      .header {
        font-size: 2rem;
      }
      .menu-btn {
        font-size: 1.2rem;
        min-width: 100px;
        min-height: 60px;
        padding: 16px 0;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">ValorCrate</div>

    <div class="menu-grid">
      <a href="place_order.php" class="menu-btn">Place<br>Order</a>
      <a href="order_tracker.php" class="menu-btn">Order<br>Tracker</a>
      <a href="account.php" class="menu-btn">Account</a>
      <a href="logout.php" class="menu-btn">Logout</a>
    </div>

    <div class="footer">All rights reserved</div>
  </div>
</body>
</html>
