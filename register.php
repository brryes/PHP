<?php
session_start();

$showSuccessPopup = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (!$username || !$email || !$password || !$confirm_password) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!preg_match('/[\W_]/', $password)) {
        $error = 'Password must contain at least one special character.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Load users
        $usersFile = __DIR__ . '/users.json';
        $users = [];
        if (file_exists($usersFile)) {
            $json = file_get_contents($usersFile);
            $users = json_decode($json, true) ?: [];
        }

        // Check duplicate email
        foreach ($users as $user) {
            if (strcasecmp($user['email'], $email) === 0) {
                $error = 'Email already registered.';
                break;
            }
        }

        if (!$error) {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Add user
            $users[] = [
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password
            ];

            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            $showSuccessPopup = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Riot Games Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      margin: 0;
    }
    .bg-main {
      background-image: url('images/landingbg.png');
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
      border: 2px solid black !important;
      background-color: #f3f4f6 !important;
    }
    /* Popup styles */
    #successPopup {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      border-radius: 0.75rem;
      box-shadow: 0 10px 25px rgb(0 0 0 / 0.3);
      width: 320px;
      padding: 2rem;
      text-align: center;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      z-index: 9999;
      display: none;
    }
    #successPopup.show {
      display: block;
    }
  </style>
</head>
<body class="h-screen w-screen flex">

  <!-- Left Panel -->
  <div class="h-full flex flex-col justify-between bg-white p-10" style="width: 27.5%;">

    <!-- Top Section -->
    <div class="w-full flex flex-col items-center">
      <!-- Riot Logo -->
      <img src="images/logo.png" alt="Riot Logo" class="w-36 mb-10 mt-2" />

      <!-- Register Form -->
      <div class="w-full max-w-sm text-center">
        <h1 class="text-2xl font-bold text-black mb-6">Register</h1>

        <?php if ($error): ?>
          <div class="mb-4 text-red-600 text-sm font-semibold">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form action="" method="post" id="registerForm" class="space-y-5 text-black" novalidate>
          <input
            type="text"
            name="username"
            placeholder="Username"
            required
            class="w-full px-4 py-3 text-lg rounded-full bg-white placeholder-gray-400 border border-gray-300 focus:border-black"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            autocomplete="username"
          />

          <div class="relative">
            <input
              type="email"
              id="email"
              name="email"
              placeholder="Email"
              required
              class="w-full px-4 py-3 text-lg rounded-full bg-white placeholder-gray-400 border border-gray-300 focus:border-black"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              autocomplete="email"
            />
            <p id="emailError" class="text-red-600 text-sm mt-1 hidden">Please enter a valid email address.</p>
          </div>

          <input
            type="password"
            name="password"
            placeholder="Password"
            required
            class="w-full px-4 py-3 text-lg rounded-full bg-white placeholder-gray-400 border border-gray-300 focus:border-black"
            autocomplete="new-password"
          />

          <input
            type="password"
            name="confirm_password"
            placeholder="Confirm Password"
            required
            class="w-full px-4 py-3 text-lg rounded-full bg-white placeholder-gray-400 border border-gray-300 focus:border-black"
            autocomplete="new-password"
          />

          <button type="submit" form="registerForm" class="hidden"></button>
        </form>
      </div>
    </div>

    <!-- Bottom Section -->
    <div class="w-full max-w-sm mx-auto mt-6">
      <!-- Arrow Button Centered -->
      <div class="flex justify-center mb-6">
        <button type="submit" form="registerForm" class="bg-gray-200 hover:bg-gray-300 p-3 rounded-full shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
          </svg>
        </button>
      </div>

      <!-- Have an account Footer -->
      <div class="text-xs text-center text-gray-500">
        <a href="index.php" class="hover:underline block mb-2">Have an account? Login</a>
        <p>This is protected by reCAPTCHA and the Google Privacy Policy and Terms of Service apply.</p>
        <p class="mt-2 text-gray-400">v109.9.1</p>
      </div>
    </div>
  </div>

  <!-- Right Background Panel -->
  <div class="h-full bg-main" style="width: 72.5%;"></div>

  <!-- Success Popup -->
  <div id="successPopup">
    <h2 class="text-xl font-bold mb-2">Account Successfully Created</h2>
    <p class="mb-4">Redirecting to login page in <span id="countdown">3</span> seconds...</p>
  </div>

  <script>
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    const form = document.getElementById('registerForm');

    function validateEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    }

    emailInput.addEventListener('input', () => {
      if (!validateEmail(emailInput.value)) {
        emailError.classList.remove('hidden');
      } else {
        emailError.classList.add('hidden');
      }
    });

    form.addEventListener('submit', (e) => {
      if (!validateEmail(emailInput.value)) {
        e.preventDefault();
        emailError.classList.remove('hidden');
        emailInput.focus();
      }
    });

    <?php if ($showSuccessPopup): ?>
    // Show popup and start countdown
    const popup = document.getElementById('successPopup');
    const countdownEl = document.getElementById('countdown');
    let count = 3;

    popup.classList.add('show');

    const interval = setInterval(() => {
      count--;
      countdownEl.textContent = count;
      if (count <= 0) {
        clearInterval(interval);
        window.location.href = 'index.php';
      }
    }, 1000);
    <?php endif; ?>
  </script>

</body>
</html>
