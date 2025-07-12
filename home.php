<?php
session_start();
if (!isset($_SESSION["username"])) {
  header("Location: index.php");
  exit;
}

$username = $_SESSION['username'];
$hour = date('H');

// Determine greeting and corresponding Tailwind color class
if ($hour < 12) {
  $greeting = "Hello User";
  $bgColor = "bg-yellow-500/10";
  $borderColor = "border-yellow-500";
  $textColor = "text-yellow-400";
} elseif ($hour < 18) {
  $greeting = "Hello User";
  $bgColor = "bg-orange-500/10";
  $borderColor = "border-orange-500";
  $textColor = "text-orange-400";
} else {
  $greeting = "Hello User";
  $bgColor = "bg-indigo-600/10";
  $borderColor = "border-indigo-500";
  $textColor = "text-indigo-400";
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
    html,
    body {
      height: 100%;
      margin: 0;
      font-family: 'Oswald', sans-serif;
      overflow: hidden;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-fadeInDown {
      animation: fadeInDown 0.6s ease-out;
    }

    #bg-video {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: -2;
    }

    .video-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      z-index: -1;
    }

    .main-wrapper {
      height: 100vh;
      width: 100vw;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      text-align: center;
      position: relative;
      z-index: 10;
    }

    .logo {
      width: 220px;
      margin-bottom: 2rem;
      filter: drop-shadow(0 0 10px rgba(255, 70, 85, 0.7));
    }

    .menu-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 1.5rem;
      max-width: 480px;
      width: 100%;
    }

    .menu-btn {
      background: rgba(255, 255, 255, 0.08);
      border: 2px solid rgba(255, 70, 85, 0.5);
      color: #ff4655;
      font-size: 1.25rem;
      font-weight: 700;
      text-transform: uppercase;
      padding: 1.5rem 1rem;
      border-radius: 1rem;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: 0 4px 20px rgba(255, 70, 85, 0.2);
      transition: all 0.25s ease-in-out;
      text-decoration: none;
    }

    .menu-btn:hover {
      background: rgba(255, 70, 85, 0.2);
      color: white;
      transform: scale(1.05);
      box-shadow: 0 0 20px rgba(255, 70, 85, 0.5);
    }

    .logout-btn {
      position: absolute;
      bottom: 30px;
      background: rgba(255, 255, 255, 0.08);
      border: 2px solid rgba(255, 70, 85, 0.5);
      color: #ff4655;
      padding: 1rem 2rem;
      border-radius: 1rem;
      font-size: 1rem;
      font-weight: 600;
      transition: 0.3s ease;
    }

    .logout-btn:hover {
      background: rgba(255, 70, 85, 0.2);
      color: white;
    }

    .footer {
      margin-top: 2rem;
      color: #ff4655;
      font-size: 0.9rem;
      opacity: 0.8;
    }

    @media (max-width: 640px) {
      .logo {
        width: 160px;
      }

      .menu-btn {
        font-size: 1rem;
        padding: 1.2rem;
      }

      .menu-grid {
        grid-template-columns: 1fr;
      }

      .logout-btn {
        padding: 0.8rem 1.5rem;
        font-size: 0.9rem;
      }
    }
  </style>
</head>

<body>
  <!-- 🔁 Background Video -->
  <video id="bg-video" autoplay muted loop playsinline>
    <source src="mainmenu.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>

  <!-- 🔳 Overlay -->
  <div class="video-overlay"></div>

  <!-- 🌇 Dynamic Time-Based Greeting -->
  <a href="user_profile.php" title="View Profile"
    class="absolute top-6 right-6 <?= $bgColor ?> border <?= $borderColor ?> text-white px-5 py-3 rounded-xl z-50 backdrop-blur-md shadow-[0_0_15px_rgba(255,70,85,0.4)] animate-fadeInDown transition transform hover:scale-105 hover:shadow-lg">
    <div class="text-sm md:text-base leading-tight text-center">
      <div class="text-gray-300"><?= $greeting ?>,</div>
      <div class="font-bold <?= $textColor ?> tracking-wide underline"><?= htmlspecialchars($username) ?>!</div>
    </div>
  </a>




  <!-- 🔊 Background Music -->
  <audio id="bg-music" autoplay loop>
    <source src="bgmusic.mp3" type="audio/mpeg">
    Your browser does not support the audio element.
  </audio>

  <!-- 🔉 Hover Sound -->
  <audio id="hover-sound" src="click.mp3" preload="auto"></audio>

  <!-- 🌟 Main Layout -->
  <div class="main-wrapper">


    <img src="valorcrate_logo.png" alt="ValorCrate Logo" class="logo" />

    <!-- Place Order shown first in the grid -->
    <div class="menu-grid">
      <a href="place_order.php" class="menu-btn">Place Order</a>
      <a href="order_tracker.php" class="menu-btn">Track Order</a>
      <a href="calendar.php" class="menu-btn">Calendar</a>
      <a href="account.php" class="menu-btn">Order Details</a>
    </div>

    <!-- Logout button at bottom center -->
    <a href="logout.php" class="logout-btn">Logout</a>

    <div class="footer">© <?= date("Y") ?> ValorCrate. All rights reserved.</div>
  </div>

  <!-- 🎧 Scripts -->
  <script>
    const hoverSound = document.getElementById('hover-sound');
    const bgMusic = document.getElementById('bg-music');
    const buttons = document.querySelectorAll('.menu-btn');

    buttons.forEach(button => {
      button.addEventListener('mouseenter', () => {
        hoverSound.currentTime = 0;
        hoverSound.play();
      });
    });

    bgMusic.volume = 0.2;
    hoverSound.volume = 0.3;

    function enableAudio() {
      bgMusic.play().catch(e => console.log("Autoplay blocked:", e));
      document.removeEventListener('click', enableAudio);
      document.removeEventListener('keydown', enableAudio);
    }

    document.addEventListener('click', enableAudio);
    document.addEventListener('keydown', enableAudio);
  </script>
</body>

</html>