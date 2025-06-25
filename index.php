<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Riot Games Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      margin: 0;
    }
    .bg-main {
      background-image: url('images/valorcrate.jpg');
      background-size: cover;
      background-position: center;
    }
    input::placeholder {
      color: #9ca3af;
    }
    input {
      outline: none;
    }
    input:focus {
      border: 2px solid black;
      background-color: #f3f4f6;
    }
  </style>
</head>
<body class="h-screen w-screen flex">

  <!-- Left Panel -->
  <div class="h-full flex flex-col justify-between bg-white p-10" style="width: 27.5%;">

    <!-- Top Section -->
    <div class="w-full flex flex-col items-center">
      <!-- Riot Logo -->
      <img src="images/logo.png" alt="Riot Logo" class="w-36 mb-10 mt-2">

      <!-- Sign In Form -->
      <div class="w-full max-w-sm text-center">
        <h1 class="text-2xl font-bold text-black mb-6">Sign In</h1>

        <?php if (isset($_GET['error'])): ?>
          <div class="mb-4 text-red-500 text-sm">
            <?= htmlspecialchars($_GET['error']) ?>
          </div>
        <?php endif; ?>

        <form action="login.php" method="post" id="loginForm" class="space-y-6 text-black">
          <div>
            <input
              type="text"
              id="username"
              name="username"
              placeholder="Username"
              required
              class="w-full px-4 py-3 text-base rounded-full bg-gray-100 placeholder-gray-400 border border-transparent focus:outline-none"
            >
          </div>

          <div>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Password"
              required
              class="w-full px-4 py-3 text-base rounded-full bg-gray-100 placeholder-gray-400 border border-transparent focus:outline-none"
            >
          </div>
        </form>
      </div>
    </div>

    <!-- Bottom Section -->
    <div class="w-full max-w-sm mx-auto mt-6">
      <!-- Arrow Button Centered -->
      <div class="flex justify-center mb-6">
        <button type="submit" form="loginForm" class="bg-gray-200 hover:bg-gray-300 p-3 rounded-full shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
          </svg>
        </button>
      </div>

      <!-- Can't Sign In & Captcha -->
      <div class="text-xs text-center text-gray-500">
        <a href="register.php" class="hover:underline block mb-2">No account? Register</a>
        <p>This is protected by reCAPTCHA and the Google Privacy Policy and Terms of Service apply.</p>
        <p class="mt-2 text-gray-400">v109.9.1</p>
      </div>
    </div>
  </div>

  <!-- Right Background Panel -->
  <div class="h-full bg-main" style="width: 72.5%;"></div>

</body>
</html>
