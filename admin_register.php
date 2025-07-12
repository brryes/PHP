<?php
session_start();
include 'db_connect.php';

$showSuccessPopup = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $region = $_POST['region'] ?? '';
    $province = $_POST['province'] ?? '';
    $city = $_POST['city'] ?? '';
    $barangay = $_POST['barangay'] ?? '';
    $role = $_POST['role'] ?? 'Administrator';

    if (!$username || !$email || !$password || !$confirm_password || !$region || !$province || !$city || !$barangay) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!preg_match('/[\W_]/', $password)) {
        $error = 'Password must contain at least one special character.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        }

        if (!$error) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, region, province, city, barangay, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password, $region, $province, $city, $barangay, $role])) {
                $showSuccessPopup = true;
            } else {
                $error = "Registration failed: " . $stmt->errorInfo()[2];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-main {
            background-image: url('images/valorcrate.jpg');
            background-size: cover;
            background-position: center;
        }

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
            <img src="images/logo.png" alt="Riot Logo" class="w-36 mb-10 mt-2" />

            <div class="w-full max-w-sm text-center">
                <h1 class="text-2xl font-bold text-black mb-6">Admin Registration</h1>

                <?php if ($error): ?>
                    <div class="mb-4 text-red-600 text-sm font-semibold"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" id="registerForm" class="space-y-4 text-black" novalidate>
                    <input type="text" name="username" placeholder="Username" required
                        class="w-full px-4 py-3 rounded-full bg-white border border-gray-300"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" />

                    <input type="email" name="email" placeholder="Email" required
                        class="w-full px-4 py-3 rounded-full bg-white border border-gray-300"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />

                    <select name="region" id="region" required
                        class="w-full px-4 py-3 rounded-full border border-gray-300">
                        <option value="">Select Region</option>
                    </select>
                    <select name="province" id="province" required
                        class="w-full px-4 py-3 rounded-full border border-gray-300">
                        <option value="">Select Province</option>
                    </select>
                    <select name="city" id="city" required class="w-full px-4 py-3 rounded-full border border-gray-300">
                        <option value="">Select City/Municipality</option>
                    </select>
                    <select name="barangay" id="barangay" required
                        class="w-full px-4 py-3 rounded-full border border-gray-300">
                        <option value="">Select Barangay</option>
                    </select>

                    <input type="password" name="password" placeholder="Password" required
                        class="w-full px-4 py-3 rounded-full bg-white border border-gray-300" />

                    <input type="password" name="confirm_password" placeholder="Confirm Password" required
                        class="w-full px-4 py-3 rounded-full bg-white border border-gray-300" />
                    <button type="submit" class="hidden"></button>
                </form>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center mt-6">
            <button type="submit" form="registerForm" class="bg-gray-200 hover:bg-gray-300 p-3 rounded-full shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </button>
            <p class="text-xs text-gray-500 mt-2">Already an admin? <a href="admin_login.php"
                    class="underline">Login</a></p>
        </div>
    </div>

    <!-- Right Background Panel -->
    <div class="h-full bg-main" style="width: 72.5%;"></div>

    <!-- Success Popup -->
    <div id="successPopup">
        <h2 class="text-xl font-bold mb-2">Admin Account Created</h2>
        <p class="mb-4">Redirecting to login in <span id="countdown">3</span> seconds...</p>
    </div>

    <!-- Scripts for Dropdowns and Popup -->
    <script>
        let regions = [], provinces = [], cities = [], barangays = [];

        Promise.all([
            fetch('addresses/region.json').then(res => res.json()),
            fetch('addresses/province.json').then(res => res.json()),
            fetch('addresses/city.json').then(res => res.json()),
            fetch('addresses/barangay.json').then(res => res.json())
        ]).then(([regionData, provinceData, cityData, barangayData]) => {
            regions = regionData;
            provinces = provinceData;
            cities = cityData;
            barangays = barangayData;
            populateRegions();
        });

        function populateRegions() {
            const select = document.getElementById('region');
            select.innerHTML = '<option value="">Select Region</option>';
            regions.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.region_name;
                opt.textContent = r.region_name;
                select.appendChild(opt);
            });
        }

        document.getElementById('region').addEventListener('change', function () {
            const regName = this.value;
            const region = regions.find(r => r.region_name === regName);
            const provinceSelect = document.getElementById('province');
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            document.getElementById('city').innerHTML = '<option value="">Select City/Municipality</option>';
            document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';

            provinces.filter(p => p.region_code === region?.region_code).forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.province_name;
                opt.textContent = p.province_name;
                provinceSelect.appendChild(opt);
            });
        });

        document.getElementById('province').addEventListener('change', function () {
            const provName = this.value;
            const province = provinces.find(p => p.province_name === provName);
            const citySelect = document.getElementById('city');
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';

            cities.filter(c => c.province_code === province?.province_code).forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.city_name;
                opt.textContent = c.city_name;
                citySelect.appendChild(opt);
            });
        });

        document.getElementById('city').addEventListener('change', function () {
            const cityName = this.value;
            const city = cities.find(c => c.city_name === cityName);
            const barangaySelect = document.getElementById('barangay');
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

            barangays.filter(b => b.city_code === city?.city_code).forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.brgy_name;
                opt.textContent = b.brgy_name;
                barangaySelect.appendChild(opt);
            });
        });

        <?php if ($showSuccessPopup): ?>
            const popup = document.getElementById('successPopup');
            const countdownEl = document.getElementById('countdown');
            let count = 3;
            popup.classList.add('show');
            const interval = setInterval(() => {
                count--;
                countdownEl.textContent = count;
                if (count <= 0) {
                    clearInterval(interval);
                    window.location.href = 'admin_login.php';
                }
            }, 1000);
        <?php endif; ?>
    </script>

</body>

</html>