<?php
// If you want to use the database, uncomment the next line and make sure db_connect.php defines $conn
// require_once 'db_connect.php';

$agents = [
  [ 
    "name" => "Bea",  
    "status" => "Owned",   
    "image" => "images/bea3.png",  
    "thumb" => "images/bea1.png",  
    "gradient" => "bg-gradient-to-r from-[#ffdede] to-[#ffeaea]",
    "description" => "Hey! I am Bea, the fastest healer in the city.",
    "abilities" => [
      ["name" => "Flash", "desc" => "Blinds enemies with a flash."],
      ["name" => "Sprint", "desc" => "Run faster for a short time."],
      ["name" => "Heal", "desc" => "Restore health over 5 seconds."]
    ]
  ],
  [ 
    "name" => "Iana", 
    "status" => "Locked",  
    "image" => "images/iana3.png", 
    "thumb" => "images/iana1.png", 
    "gradient" => "bg-gradient-to-r from-[#e0c3fc] to-[#8ec5fc]",
    "description" => "Hi! I'm Iana, master of illusions.",
    "abilities" => [
      ["name" => "Holo", "desc" => "Creates a decoy hologram."],
      ["name" => "Phase", "desc" => "Pass through walls temporarily."],
      ["name" => "Pulse", "desc" => "Detect enemies nearby."]
    ]
  ],
  [ 
    "name" => "Karl", 
    "status" => "Unlocked",
    "image" => "images/karl2.png", 
    "thumb" => "images/karl1.png", 
    "gradient" => "bg-gradient-to-r from-[#d4fc79] to-[#96e6a1]",
    "description" => "Karl here! Ready to defend and attack.",
    "abilities" => [
      ["name" => "Shield", "desc" => "Deploy a protective barrier."],
      ["name" => "Strike", "desc" => "Powerful melee attack."],
      ["name" => "Roar", "desc" => "Boost nearby allies."]
    ]
  ],
  [ 
    "name" => "Mika", 
    "status" => "Trial",   
    "image" => "images/mika3.png", 
    "thumb" => "images/mika1.png", 
    "gradient" => "bg-gradient-to-r from-[#fbc2eb] to-[#a6c1ee]",
    "description" => "Mika here, bringing the chill wherever I go.",
    "abilities" => [
      ["name" => "Freeze", "desc" => "Freeze enemies in place."],
      ["name" => "Ice Wall", "desc" => "Create a wall of ice."],
      ["name" => "Blizzard", "desc" => "Call a damaging snowstorm."]
    ]
  ],
  [ 
    "name" => "Enzo", 
    "status" => "Owned",   
    "image" => "images/nzo2.png",  
    "thumb" => "images/nzo1.png",  
    "gradient" => "bg-gradient-to-r from-[#ffecd2] to-[#fcb69f]",
    "description" => "Enzo here! Ready to ignite the battlefield with my fiery skills.",
    "abilities" => [
      ["name" => "Fireball", "desc" => "Launch a ball of fire."],
      ["name" => "Dash", "desc" => "Quickly dash forward."],
      ["name" => "Flame Shield", "desc" => "Create a shield of flames."]
    ]
  ]
];

// Only run this block if $conn is defined (database is connected)
if (isset($conn)) {
    $sql = "SELECT * FROM agents";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $row['abilities'] = json_decode($row['abilities'], true);
            $agents[] = $row;
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Waylay Store</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Anton&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed&display=swap');

    .sidebar-icon { width: 48px; height: 48px; object-fit: cover; cursor: pointer; }
    #agent-name { 
      font-size: 9.5rem; 
      text-align: center; 
      width: 100%; 
      text-shadow: 0 2px 12px #ff4c4c, 0 0 32px #000a;
      transition: text-shadow 0.3s;
    }
    
    /* Abilities box style */
    #abilities-box {
      background: rgba(0,0,0,0.5);
      border: 3px solid #ff4c4c;
      border-radius: 1rem;
      padding: 2rem;
      width: 350px;
      max-width: 90vw;
      color: white;
      font-family: 'Anton', sans-serif;
      text-align: center;
      box-shadow: 0 0 20px #ff4c4c;
      user-select: none;
      margin-top: 1.5rem;
    }

    #abilities-box h2 {
      font-size: 2.5rem;
      margin-bottom: 1rem;
      letter-spacing: 2px;
      text-shadow: 0 0 10px #ff4c4c;
      transition: text-shadow 0.3s;
    }

    #agent-abilities {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    #agent-abilities li.tooltip {
      position: relative;
      font-size: 1.6rem;
      font-weight: 700;
      margin: 1rem 0;
      cursor: pointer;
      transition: font-size 0.3s ease;
      letter-spacing: 1.5px;
    }

    #agent-abilities li.tooltip:hover {
      font-size: 2rem;
      font-weight: 900;
    }

    #agent-abilities li.tooltip .tooltiptext {
      visibility: hidden;
      width: 260px;
      background-color: rgba(255, 76, 76, 0.9);
      color: #fff;
      text-align: center;
      border-radius: 8px;
      padding: 0.5rem 1rem;
      position: absolute;
      z-index: 10;
      bottom: 125%;
      left: 50%;
      margin-left: -130px;
      opacity: 0;
      transition: opacity 0.3s;
      font-size: 1rem;
      box-shadow: 0 0 15px #ff4c4c;
      user-select: none;
    }

    #agent-abilities li.tooltip:hover .tooltiptext {
      visibility: visible;
      opacity: 1;
    }

    #agent-abilities li.tooltip .tooltiptext::after {
      content: "";
      position: absolute;
      top: 100%;
      left: 50%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: rgba(255, 76, 76, 0.9) transparent transparent transparent;
    }

    #description-box {
      margin-top: 1.5rem;
      background: rgba(0,0,0,0.4);
      border-radius: 1rem;
      padding: 1.5rem 2rem;
      width: 350px;
      max-width: 90vw;
      color: white;
      font-family: 'Roboto Condensed',sans-serif;
      text-align: center;
      box-shadow: 0 0 10px #ff4c4c;
      font-size: 1.3rem;
    }
    #agent-status {
      font-size: 2rem;
      font-weight: bold;
    }
    #homeBtn {
      position: fixed;
      right: 16px;
      bottom: 24px;
      width: 48px;
      height: 48px;
      background: rgba(0, 0, 0, 0.6);
      border: none;
      color: #fff;
      font-size: 1.8rem;
      border-radius: 50%;
      cursor: pointer;
      z-index: 1001;
      transition: background 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    #homeBtn:hover,
    #homeBtn:focus {
      background: #ff4655;
      color: white;
      outline: none;
    }
  </style>
</head>
<body id="main-body" class="relative min-h-screen bg-gradient-to-r from-[#ffecd2] to-[#fcb69f] font-['Roboto_Condensed',sans-serif] select-none overflow-x-hidden transition-all duration-500">
<audio id="bg-music" src="images/agentvbg.mp3" autoplay loop></audio>

<!-- UI: Top -->
<div class="fixed top-0 left-0 w-full z-30 bg-white/50 backdrop-blur-md py-3 flex justify-between px-6 items-center">
  <div class="text-xl font-bold uppercase tracking-wide text-gray-800">Agent's Store</div>
  <div class="flex items-center space-x-4">
    <button class="text-gray-700 hover:text-gray-900"><i class="fas fa-cog"></i></button>
    <button class="text-gray-700 hover:text-gray-900"><i class="fas fa-user-circle"></i></button>
  </div>
</div>

<!-- UI: Faint Name Background -->
<div class="absolute z-0 w-full h-full overflow-hidden faint-group px-4 md:px-20 pt-20">
  <div class="text-[10rem] font-['Anton'] font-extrabold text-white opacity-25 leading-none">ENZO</div>
  <div class="text-[10rem] font-['Anton'] font-extrabold text-white opacity-25 leading-none">ENZO</div>
  <div class="text-[10rem] font-['Anton'] font-extrabold text-white opacity-25 leading-none">ENZO</div>
  <div class="text-[10rem] font-['Anton'] font-extrabold text-white opacity-25 leading-none">ENZO</div>
  <div class="text-[10rem] font-['Anton'] font-extrabold text-white opacity-25 leading-none">ENZO</div>
</div>

<!-- UI: Main Content -->
<div class="relative flex flex-col sm:flex-row items-center sm:items-start justify-center mt-28 px-4 sm:px-10 space-y-6 sm:space-y-0 sm:space-x-10 z-10">

  <!-- Info Panel (centered, includes description box) -->
  <div id="info-panel" class="flex flex-col items-center w-full sm:w-1/3 text-white transition-all duration-500">
    <div class="flex flex-col items-center w-full">
      <h1 id="agent-name" class="font-['Anton'] text-5xl sm:text-6xl uppercase leading-none select-text text-center w-full">ENZO</h1>
      <div class="flex items-center justify-center space-x-2 mb-4 opacity-80 text-sm sm:text-base font-semibold">
        <svg class="w-4 h-4 text-[#2ecc71]" fill="none" stroke="#2ecc71" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
        <span id="agent-status">Owned</span>
      </div>
    </div>
    <!-- Abilities Box (centered) -->
    <div id="abilities-box" class="flex flex-col items-center">
      <h2>Abilities</h2>
      <ul id="agent-abilities">
        <!-- Dynamically filled by JS -->
        <li class="tooltip">Fireball
          <span class="tooltiptext">Launch a ball of fire.</span>
        </li>
        <li class="tooltip">Dash
          <span class="tooltiptext">Quickly dash forward.</span>
        </li>
        <li class="tooltip">Flame Shield
          <span class="tooltiptext">Create a shield of flames.</span>
        </li>
      </ul>
    </div>
    <!-- Description Box -->
    <div id="description-box">
      Enzo here! Ready to ignite the battlefield with my fiery skills.
    </div>
  </div>

  <!-- Agent Display Image (right side) -->
  <div id="agent-display" class="relative w-full sm:w-1/3 flex justify-center min-h-[400px]">
    <img src="images/nzo2.png" alt="Enzo" class="max-w-full h-auto rounded-lg shadow-lg" draggable="false"/>
  </div>

</div>

<!-- UI: Agent Selector -->
<div class="absolute bottom-10 left-0 right-0 flex justify-center space-x-8 sm:space-x-12 px-6 sm:px-20 z-20 w-full max-w-[1800px] mx-auto">
  <?php foreach ($agents as $agent): ?>
    <div class="relative w-52 h-64 bg-[#2a3a2a]/70 rounded overflow-hidden cursor-pointer group agent-box"
         data-name="<?= $agent['name'] ?>"
         data-status="<?= $agent['status'] ?>"
         data-image="<?= $agent['image'] ?>"
         data-gradient="<?= $agent['gradient'] ?>"
         data-description="<?= htmlspecialchars($agent['description'] ?? '', ENT_QUOTES) ?>"
         data-abilities='<?= json_encode($agent['abilities'], JSON_HEX_APOS) ?>'>
      <img src="<?= $agent['thumb'] ?>" alt="<?= $agent['name'] ?>" class="w-full h-full object-cover" draggable="false"/>
      <div class="absolute top-2 left-2 bg-black/60 px-2 rounded text-white font-bold tracking-widest select-none pointer-events-none"><?= strtoupper($agent['name']) ?></div>
      <div class="absolute bottom-2 left-2 bg-black/60 px-2 rounded text-white font-semibold select-none pointer-events-none"><?= $agent['status'] ?></div>
      <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-30 transition"></div>
    </div>
  <?php endforeach; ?>
</div>

<script>
  const agentBoxes = document.querySelectorAll(".agent-box");
  const agentName = document.getElementById("agent-name");
  const agentStatus = document.getElementById("agent-status");
  const agentDisplay = document.getElementById("agent-display");
  const mainBody = document.body;
  const descriptionBox = document.getElementById("description-box");
  const abilitiesBoxTitle = document.querySelector("#abilities-box h2");

  // Map agent names to their main color (first color in their gradient)
  const agentShadowColors = {
    "BEA": "#ffdede",
    "IANA": "#e0c3fc",
    "KARL": "#d4fc79",
    "MIKA": "#fbc2eb",
    "ENZO": "#ffecd2"
  };

  agentBoxes.forEach(box => {
    box.addEventListener('click', () => {
      const name = box.dataset.name;
      const status = box.dataset.status;
      const image = box.dataset.image;
      const gradientClasses = box.dataset.gradient.split(' ');
      const abilities = JSON.parse(box.dataset.abilities);
      const description = box.dataset.description;

      // Update agent name and status
      agentName.textContent = name.toUpperCase();

      // Get shadow color for this agent
      const shadowColor = agentShadowColors[name.toUpperCase()] || "#ff4c4c";

      // Set shadow for agent name and abilities title
      agentName.style.textShadow = `0 2px 12px ${shadowColor}, 0 0 32px #000a`;
      if (abilitiesBoxTitle) {
        abilitiesBoxTitle.style.textShadow = `0 0 10px ${shadowColor}`;
      }

      agentStatus.textContent = status;

      // Remove old gradient classes from body
      mainBody.classList.forEach(cls => {
        if (cls.startsWith('bg-gradient-to-r') || cls.startsWith('from-') || cls.startsWith('to-')) {
          mainBody.classList.remove(cls);
        }
      });

      // Add new gradient classes
      gradientClasses.forEach(cls => mainBody.classList.add(cls));

      // Update faint name backgrounds
      document.querySelectorAll(".faint-group div").forEach(div => {
        div.textContent = name.toUpperCase();
      });

      // Update agent image
      const imgTag = agentDisplay.querySelector('img');
      imgTag.src = image;
      imgTag.alt = name;

      // Update abilities list
      const abilitiesList = document.getElementById("agent-abilities");
      abilitiesList.innerHTML = "";
      abilities.forEach(ability => {
        const li = document.createElement("li");
        li.classList.add("tooltip");
        li.textContent = ability.name;

        const tooltipSpan = document.createElement("span");
        tooltipSpan.classList.add("tooltiptext");
        tooltipSpan.textContent = ability.desc;

        li.appendChild(tooltipSpan);
        abilitiesList.appendChild(li);
      });

      // Update description box
      descriptionBox.textContent = description;
    });
  });
</script>
<!-- Floating Home Button -->
<a id="homeBtn" href="home.php" title="Go to Home">
  <i class="fas fa-home"></i>
</a>  
</body>
</html>