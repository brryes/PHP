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
  <title>Valorant Menu</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

  <style>
    @import url("https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&display=swap");
    @import url('https://fonts.googleapis.com/css2?family=Anton&display=swap');

    body {
      font-family: "Oswald", sans-serif;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
      position: relative;
      min-height: 100vh;
      background: url('images/bg4.png') no-repeat center center fixed;
      background-size: cover;
    }

    #video-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      object-fit: cover;
      z-index: -10;
    }

    #chat-messages::-webkit-scrollbar {
      display: none;
    }

    #chat-messages {
      -ms-overflow-style: none;
      /* IE and Edge */
      scrollbar-width: none;
      /* Firefox */
    }

    #video-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background-color: rgba(0, 0, 0, 0.6);
      z-index: -5;
      pointer-events: none;
    }

    .menu-btn {
      font-size: 36px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      transition: transform 0.2s ease-in-out;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .menu-btn:hover {
      transform: scale(1.15);
    }

    .sidebar-icon {
      width: 48px;
      height: 48px;
      object-fit: cover;
      cursor: pointer;
    }

    .logo-container {
      position: absolute;
      top: 1.5rem;
      left: 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }

    .logo-text {
      font-family: 'Anton', sans-serif;
      font-weight: 700;
      font-size: 1.5rem;
      color: white;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      user-select: none;
      filter: drop-shadow(2px 2px 1px rgba(0, 0, 0, 0.8));
    }

    .logo-subtitle {
      font-size: 0.75rem;
      font-weight: 700;
      color: white;
      letter-spacing: 0.15em;
      user-select: none;
      text-shadow: 0 0 4px rgba(0, 0, 0, 0.7);
    }
  </style>
</head>

<body>
  <video id="video-bg" autoplay loop playsinline preload="auto" poster="images/bg4.png"
    onerror="this.style.display='none'">
    <source src="images/vbg.mp4" type="video/mp4" />
  </video>
  <div id="video-overlay"></div>

  <div class="logo-container">
    <div class="logo-text">RIOT n GAMES</div>
    <span class="logo-subtitle">V25 // ACT III</span>
  </div>

  <!-- LEFT MENU -->
  <nav class="absolute top-1/2 -translate-y-1/2 left-6 flex flex-col space-y-8 text-white">
    <a href="place_order.php" class="menu-btn text-[#ff4c5b]"><i class="fas fa-box"></i><span>Place Order</span></a>

    <a href="agents.php" class="menu-btn"><i class="fas fa-user-ninja"></i><span>Agents</span></a>
    <a href="logout.php" class="menu-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    <a href="order_tracker.php" class="menu-btn"><i class="fas fa-shipping-fast"></i><span>Order Tracker</span></a>
    <a href="account.php" class="menu-btn"><i class="fas fa-id-card"></i><span>Account</span></a>
  </nav>

  <!-- RIGHT SIDEBAR -->
  <aside class="absolute top-0 right-0 h-full w-[80px] bg-black/50 flex flex-col items-center pt-6 space-y-6 z-30">
    <button class="text-white hover:text-white/90 text-3xl"><i class="fas fa-shopping-cart"></i></button>
    <button><img src="images/user.png" alt="user" class="sidebar-icon" /></button>
  </aside>

  <!-- MAIN PANEL -->
  <aside class="absolute top-6 right-[100px] w-[320px] flex flex-col space-y-3 text-white font-oswald z-20">
    <div class="flex justify-end text-xs text-white/80">
      <div class="flex flex-wrap gap-3 justify-end">
        <div class="flex items-center space-x-1"><i class="fas fa-gem"></i><span>1 / 4</span></div>
        <div class="flex items-center space-x-1"><i class="fas fa-star"></i><span>2 / 3</span></div>
        <div class="flex items-center space-x-1"><i class="far fa-clock"></i><span>212</span></div>
        <div class="flex items-center space-x-1"><i class="fas fa-ruble-sign"></i><span>915</span></div>
        <div class="flex items-center space-x-1"><i class="fas fa-crown"></i><span>8 528</span></div>
      </div>
    </div>

    <button
      class="bg-[#7a3f3f] p-3 border border-white/20 rounded-md transform hover:scale-105 transition duration-200 text-left w-full">
      <img src="images/giveback.png" alt="featured" class="w-full mb-3 object-cover rounded-md" />
      <h2 class="text-3xl font-extrabold uppercase mb-1">Featured</h2>
      <p class="text-xs tracking-widest mb-2">Collection</p>
      <p class="text-[9px] leading-tight">
        50% of Riot's proceeds from weapon skins + 100% from accessories in the Give Back // V25 bundle go to the Riot
        Games Social Impact Fund.
      </p>
    </button>

    <a href="https://www.youtube.com/watch?v=btpWg1gDXIE" target="_blank"
      class="block transform transition duration-200 hover:scale-105">
      <section class="bg-[#1a1a1a] border border-white/20 rounded-md overflow-hidden cursor-pointer">
        <img src="images/vyse.png" alt="trailer" class="w-full object-cover" />
        <div class="p-2 text-xs font-extrabold uppercase leading-tight">
          All Paths End Here // Vyse Agent Trailer - Valorant
          <i class="fas fa-external-link-alt ml-1"></i>
        </div>
        <div class="flex justify-center space-x-2 mb-2">
          <span class="w-2 h-2 rounded-full bg-white/80"></span>
          <span class="w-2 h-2 rounded-full bg-white/40"></span>
          <span class="w-2 h-2 rounded-full bg-white/40"></span>
        </div>
      </section>
    </a>

    <button
      class="bg-gradient-to-b from-[#5a6a6a] to-[#2a3a3a] border border-white/20 rounded-md py-2 text-center text-white font-semibold tracking-widest text-sm transform hover:scale-105 transition duration-200">
      PATCH NOTES
      <div class="text-xs font-normal mt-1">10.09</div>
    </button>

    <button
      class="bg-[#1a1a1a] border border-white/20 rounded-md p-2 text-xs uppercase font-semibold tracking-widest text-white/80 transform hover:scale-105 transition duration-200">
      Featured Modes
    </button>

    <button
      class="bg-[#2a3a3a] border border-white/20 rounded-md flex items-center justify-between p-2 text-white text-xs font-semibold tracking-widest transform hover:scale-105 transition duration-200">
      <div class="flex items-center space-x-2"><i class="fas fa-diamond text-lg"></i><span>Team Deathmatch</span></div>
      <span class="text-[10px] font-normal">8-10 mins</span>
    </button>
  </aside>

  <!-- Chat Display Area -->
  <div id="chat-display"
    class="absolute bottom-[34px] left-6 w-[320px] h-[180px] bg-black/80 hover:bg-black/90 border border-white/20 rounded-md overflow-hidden z-20 transition duration-300">
    <div id="chat-messages" class="p-2 text-xs text-white space-y-1 overflow-y-scroll h-full"></div>
  </div>

  <!-- Party Input -->
  <div
    class="absolute bottom-4 left-6 w-[320px] bg-black/90 border border-white/20 rounded-md flex items-center px-3 py-1 text-xs text-white z-20">
    <label for="party" class="mr-2">Party:</label>
    <input id="party" type="text" class="flex-grow bg-transparent outline-none placeholder-white/50"
      placeholder="Type a message..." />
    <button id="send-button" class="hover:text-white ml-2"><i class="fas fa-paper-plane"></i></button>
  </div>

  <script>
    const username = <?php echo json_encode($_SESSION['username']); ?>;
    const partyInput = document.getElementById('party');
    const chatMessages = document.getElementById('chat-messages');
    const sendButton = document.getElementById('send-button');
    const videoBg = document.getElementById('video-bg');

    videoBg.volume = 0.060;

    const clickSound = new Audio('images/click.mp3');
    clickSound.volume = 0.3;

    const keySound = new Audio('images/keypress.mp3');
    keySound.volume = 0.010;

    function addMessage() {
      const message = partyInput.value.trim();
      if (message) {
        const messageElement = document.createElement('div');
        messageElement.textContent = `${username}: ${message}`;
        chatMessages.appendChild(messageElement);
        partyInput.value = '';

        while (chatMessages.children.length > 10) {
          chatMessages.removeChild(chatMessages.firstChild);
        }

        chatMessages.scrollTop = chatMessages.scrollHeight;
      }
    }

    partyInput.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        addMessage();
      }
      keySound.currentTime = 0;
      keySound.play();
    });

    sendButton.addEventListener('click', function () {
      clickSound.currentTime = 0;
      clickSound.play();
      addMessage();
    });

    const elementsWithSound = [
      ...document.querySelectorAll('button'),
      ...document.querySelectorAll('.menu-btn'),
      document.querySelector('.fa-shopping-cart'),
      document.querySelector('.sidebar-icon')
    ];

    elementsWithSound.forEach(el => {
      el.addEventListener('click', () => {
        clickSound.currentTime = 0;
        clickSound.play();
      });
    });
  </script>
</body>

</html>