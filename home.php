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
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html,
    body {
      height: 100%;
      font-family: 'Oswald', sans-serif;
      overflow: hidden;
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

    .container {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      padding: 2rem;
      z-index: 10;
    }


    .logo {
      width: 240px;
      margin-bottom: 2rem;
      filter: drop-shadow(0 0 10px rgba(255, 70, 85, 0.6));
    }

    .menu-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 1.5rem;
      width: 100%;
      max-width: 420px;
    }

    .menu-btn {
      background: rgba(255, 255, 255, 0.12);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      color: #ff4655;
      font-size: 1.8rem;
      font-weight: bold;
      border: 2px solid rgba(255, 70, 85, 0.5);
      border-radius: 1rem;
      padding: 2.5rem 0;
      transition: all 0.3s ease-in-out;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100px;
      box-shadow: 0 0 20px rgba(255, 70, 85, 0.15), 0 8px 32px rgba(0, 0, 0, 0.25);
    }

    .menu-btn:hover {
      transform: scale(1.05);
      background: rgba(255, 70, 85, 0.2);
      color: #ffffff;
      box-shadow: 0 0 30px rgba(255, 70, 85, 0.6), 0 10px 40px rgba(0, 0, 0, 0.3);
    }


    .footer {
      margin-top: 2rem;
      font-size: 0.9rem;
      color: #ff4655;
      opacity: 0.8;
      letter-spacing: 1px;
    }

    @media (max-width: 600px) {
      .logo {
        width: 160px;
        margin-bottom: 1.5rem;
      }

      .menu-btn {
        font-size: 1.2rem;
        padding: 1.5rem 0;
        min-height: 80px;
      }
    }
  </style>
</head>

<body>
  <!-- ?? Background Video -->
  <video id="bg-video" autoplay muted loop playsinline>
    <source src="mainmenu.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>

  <!-- ?? Semi-Transparent Overlay -->
  <div class="video-overlay"></div>

  <!-- ?? Background Music -->
  <audio id="bg-music" autoplay loop>
    <source src="bgmusic.mp3" type="audio/mpeg">
    Your browser does not support the audio element.
  </audio>

  <!-- ?? Hover Sound Effect -->
  <audio id="hover-sound" src="click.mp3" preload="auto"></audio>

  <!-- ?? Centered Logo and Menu -->
  <div class="container">
    <img src="valorcrate_logo.png" alt="ValorCrate Logo" class="logo" />
    <div class="menu-grid">
      <a href="place_order.php" class="menu-btn">Place<br>Order</a>
      <a href="order_tracker.php" class="menu-btn">Order<br>Tracker</a>
      <a href="account.php" class="menu-btn">Account</a>
      <a href="logout.php" class="menu-btn">Logout</a>
    </div>
    <div class="footer">© <?= date("Y") ?> ValorCrate. All rights reserved.</div>
  </div>

  <!-- ?? Script -->
  <script>
    const hoverSound = document.getElementById('hover-sound');
    const bgMusic = document.getElementById('bg-music');
    const buttons = document.querySelectorAll('.menu-btn');

    // Play sound on hover
    buttons.forEach(button => {
      button.addEventListener('mouseenter', () => {
        hoverSound.currentTime = 0;
        hoverSound.play();
      });
    });

    // ?? Adjust background music volume
    bgMusic.volume = 0.2;

    // ?? Start music only after first user interaction
    function enableAudio() {
      bgMusic.play().catch((e) => {
        console.log("Autoplay prevented:", e);
      });
      document.removeEventListener('click', enableAudio);
      document.removeEventListener('keydown', enableAudio);
    }

    document.addEventListener('click', enableAudio);
    document.addEventListener('keydown', enableAudio);
  </script>
</body>


</html>