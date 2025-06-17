<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: index.php?error=Please log in first.');
  exit;
}
$username = htmlspecialchars($_SESSION['username']);
$currentStep = $_POST['step'] ?? '1';

// Fee calculation based on location
function get_base_fee($sender_city, $recipient_city)
{
  if (strtolower($sender_city) === strtolower($recipient_city)) {
    return 50; // same city
  }
  return 80; // different city
}

function preserve($name)
{
  return htmlspecialchars($_POST[$name] ?? '');
}

function validate_contact($number)
{
  $cleaned = preg_replace('/\s+/', '', $number);
  return preg_match('/^(09\d{9}|\+639\d{9})$/', $cleaned);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nextStep = $_POST['step'] ?? $currentStep;
  $isGoingBack = (int) $nextStep < (int) $currentStep;

  if ($isGoingBack) {
    $currentStep = $nextStep; // allow back with no validation
  } else {
    // Validate only when going forward
    if ($currentStep === '1') {
      if (
        !isset($_POST['sender_name'], $_POST['sender_contact'], $_POST['sender_address']) ||
        !validate_contact($_POST['sender_contact']) ||
        empty($_POST['sender_province']) || empty($_POST['sender_city']) || empty($_POST['sender_barangay'])
      ) {
        $currentStep = '1';
      } else {
        $currentStep = $nextStep;
      }
    } elseif ($currentStep === '2') {
      if (
        !isset($_POST['recipient_name'], $_POST['recipient_contact'], $_POST['recipient_address']) ||
        !validate_contact($_POST['recipient_contact']) ||
        empty($_POST['recipient_province']) || empty($_POST['recipient_city']) || empty($_POST['recipient_barangay'])
      ) {
        $currentStep = '2';
      } else {
        $currentStep = $nextStep;
      }
    } elseif ($currentStep === '3') {
      if (
        empty($_POST['item_category']) ||
        empty($_POST['weight']) ||
        empty($_POST['value']) ||
        empty($_POST['pickup_time']) ||
        !isset($_POST['weight'], $_POST['value'])
      ) {
        $currentStep = '3';
      } else {
        $currentStep = $nextStep;
      }
    } else {
      $currentStep = $nextStep;
    }
  }
}

// Dropdown data
$provinces = [
  'Metro Manila' => ['Manila', 'Quezon City', 'Pasig'],
  'Calabarzon' => ['Calamba', 'Batangas City', 'Antipolo'],
];
$barangays = [
  'Manila' => ['Barangay 1', 'Barangay 2'],
  'Quezon City' => ['Barangay A', 'Barangay B'],
  'Pasig' => ['Barangay X', 'Barangay Y'],
  'Calamba' => ['Barangay C1', 'Barangay C2'],
  'Batangas City' => ['Barangay B1', 'Barangay B2'],
  'Antipolo' => ['Barangay P1', 'Barangay P2'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>ValorCrate - Shipping</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .progress-step {
      text-align: center;
      width: 25%;
    }

    .progress-active {
      color: #ff4655;
      border-bottom: 4px solid #ff4655;
    }

    .fee-display {
      font-size: 2.5rem;
      color: #ff4655;
      font-weight: bold;
    }

    @media print {
      .no-print {
        display: none;
      }
    }
  </style>
  <script>
    const locations = <?= json_encode(['provinces' => $provinces, 'barangays' => $barangays]) ?>;
    function updateCities(prefix) {
      const prov = document.getElementById(prefix + '_province').value;
      const citySelect = document.getElementById(prefix + '_city');
      citySelect.innerHTML = '<option value="">Select City</option>';
      if (locations.provinces[prov]) {
        locations.provinces[prov].forEach(city => {
          citySelect.options.add(new Option(city, city));
        });
      }
      updateBarangays(prefix);
    }
    function updateBarangays(prefix) {
      const city = document.getElementById(prefix + '_city').value;
      const barangaySelect = document.getElementById(prefix + '_barangay');
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      if (locations.barangays[city]) {
        locations.barangays[city].forEach(brgy => {
          barangaySelect.options.add(new Option(brgy, brgy));
        });
      }
    }
    window.addEventListener('DOMContentLoaded', () => {
      ['sender', 'recipient'].forEach(prefix => {
        updateCities(prefix);
      });
    });
  </script>
</head>

<body class="bg-gray-900 text-gray-200 min-h-screen py-8 px-4">
  <div class="max-w-3xl mx-auto bg-gray-800 p-8 rounded shadow-xl">
    <h1 class="text-3xl font-bold text-center text-red-500 mb-2">Shipping Form</h1>
    <div class="flex justify-end mb-6 no-print">
      <a href="home.php" onclick="return confirm('Are you sure you want to go back?')"
        class="bg-gray-700 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded">
        ← Back to Home
      </a>
    </div>
    <div class="flex justify-between mb-8">
      <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="progress-step <?= $currentStep == $i ? 'progress-active' : '' ?>">
          <?= sprintf('%02d', $i) ?><br><span
            class="text-sm"><?= ['Sender', 'Recipient', 'Package', 'Complete'][$i - 1] ?></span>
        </div>
      <?php endfor; ?>
    </div>

    <form method="POST" class="space-y-6">
      <input type="hidden" name="step" value="<?= $currentStep ?>">

      <?php if ($currentStep == '1'): ?>
        <h2 class="text-xl font-semibold">01. Sender Information</h2>
        <input name="sender_name" placeholder="Full Name" value="<?= preserve('sender_name') ?>" required
          class="bg-gray-700 p-2 rounded w-full">
        <input name="sender_contact" placeholder="Contact Number" pattern="^(09\d{9}|\+639\d{9})$"
          title="09123456789 or +639123456789" value="<?= preserve('sender_contact') ?>" required
          class="bg-gray-700 p-2 rounded w-full">
        <input name="sender_address" placeholder="Street" value="<?= preserve('sender_address') ?>" required
          class="bg-gray-700 p-2 rounded w-full">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <select name="sender_province" id="sender_province" onchange="updateCities('sender')" required
            class="bg-gray-700 p-2 rounded">
            <option value="">Select Province</option>
            <?php foreach ($provinces as $prov => $cities): ?>
              <option <?= preserve('sender_province') == $prov ? 'selected' : '' ?>><?= $prov ?></option>
            <?php endforeach; ?>
          </select>
          <select name="sender_city" id="sender_city" onchange="updateBarangays('sender')" required
            class="bg-gray-700 p-2 rounded"></select>
          <select name="sender_barangay" id="sender_barangay" required class="bg-gray-700 p-2 rounded"></select>
        </div>
        <button type="submit" name="step" value="2"
          class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded no-print float-right">Next →</button>

      <?php elseif ($currentStep == '2'): ?>
        <h2 class="text-xl font-semibold">02. Recipient Information</h2>
        <input name="recipient_name" placeholder="Full Name" value="<?= preserve('recipient_name') ?>" required
          class="bg-gray-700 p-2 rounded w-full">
        <input name="recipient_contact" placeholder="Contact Number" pattern="^(09\d{9}|\+639\d{9})$"
          value="<?= preserve('recipient_contact') ?>" required class="bg-gray-700 p-2 rounded w-full">
        <input name="recipient_address" placeholder="Street" value="<?= preserve('recipient_address') ?>" required
          class="bg-gray-700 p-2 rounded w-full">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <select name="recipient_province" id="recipient_province" onchange="updateCities('recipient')" required
            class="bg-gray-700 p-2 rounded">
            <option value="">Select Province</option>
            <?php foreach ($provinces as $prov => $cities): ?>
              <option <?= preserve('recipient_province') == $prov ? 'selected' : '' ?>><?= $prov ?></option>
            <?php endforeach; ?>
          </select>
          <select name="recipient_city" id="recipient_city" onchange="updateBarangays('recipient')" required
            class="bg-gray-700 p-2 rounded"></select>
          <select name="recipient_barangay" id="recipient_barangay" required class="bg-gray-700 p-2 rounded"></select>
        </div>
        <div class="flex justify-between no-print">
          <button type="submit" name="step" value="1" class="bg-gray-700 px-4 py-2 rounded">← Back</button>
          <button type="submit" name="step" value="3" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded">Next
            →</button>
        </div>

      <?php elseif ($currentStep == '3'): ?>
        <?php
        $senderCity = $_POST['sender_city'] ?? '';
        $recipientCity = $_POST['recipient_city'] ?? '';
        $base_fee = get_base_fee($senderCity, $recipientCity);
        $weight = floatval($_POST['weight'] ?? 0);
        $value = floatval($_POST['value'] ?? 0);
        $estimated = $base_fee + ($weight * 85) + ($value * 0.01);
        ?>
        <h2 class="text-xl font-semibold">03. Package Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <input name="item_name" placeholder="Item Name (optional)" value="<?= preserve('item_name') ?>"
            class="bg-gray-700 p-2 rounded">
          <input type="number" name="quantity" placeholder="Quantity (optional)" value="<?= preserve('quantity') ?>"
            min="1" class="bg-gray-700 p-2 rounded">
          <select name="item_category" required class="bg-gray-700 p-2 rounded">
            <option value="">Select Category *</option>
            <?php foreach (['parcel', 'electronics', 'document'] as $cat): ?>
              <option value="<?= $cat ?>" <?= preserve('item_category') == $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <input type="number" name="weight" placeholder="Weight (kg) *" value="<?= preserve('weight') ?>" min="0.01"
            step="0.01" required class="bg-gray-700 p-2 rounded">
          <input type="number" name="value" placeholder="Goods Value (₱) *" value="<?= preserve('value') ?>" min="1"
            required class="bg-gray-700 p-2 rounded">
          <select name="pickup_time" required class="bg-gray-700 p-2 rounded">
            <option value="">Pickup Time</option>
            <?php foreach (['8AM-10AM', '10AM-12PM', '1PM-3PM', '3PM-5PM'] as $time): ?>
              <option <?= preserve('pickup_time') == $time ? 'selected' : '' ?>><?= $time ?></option>
            <?php endforeach; ?>
          </select>
          <textarea name="remarks" placeholder="Remarks (optional)" rows="2"
            class="bg-gray-700 p-2 rounded"><?= preserve('remarks') ?></textarea>
        </div>
        <div class="text-center mt-4">
          <p class="uppercase text-gray-400">Estimated Fee</p>
          <div class="fee-display" id="feeDisplay">
            ₱<?= number_format($estimated, 2) ?>
          </div>
          <small class="text-gray-400">Auto-updates when you enter weight or value</small>
        </div>
        <div class="mt-4 no-print">
          <label><input type="checkbox" required class="mr-2">I have read, understand agreed to the terms and
            condition.</label>
        </div>
        <div class="flex justify-between no-print">
          <button type="submit" name="step" value="2" class="bg-gray-700 px-4 py-2 rounded">← Back</button>
          <button type="submit" name="step" value="4" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded">Confirm
            →</button>
        </div>

      <?php elseif ($currentStep == '4'): ?>
        <div class="print-section">
          <h2 class="text-xl font-semibold mb-4">04. Order Complete</h2>
          <p class="mb-4">Sender:
            <?= preserve('sender_name') . ', ' . preserve('sender_address') . ', ' . preserve('sender_barangay') . ', ' . preserve('sender_city') . ', ' . preserve('sender_province') ?>
          </p>
          <p class="mb-4">Recipient:
            <?= preserve('recipient_name') . ', ' . preserve('recipient_address') . ', ' . preserve('recipient_barangay') . ', ' . preserve('recipient_city') . ', ' . preserve('recipient_province') ?>
          </p>
          <p class="mb-4">Package: <?= preserve('item_category') ?>, <?= preserve('weight') ?>kg,
            ₱<?= number_format(preserve('value'), 2) ?>, Pickup: <?= preserve('pickup_time') ?></p>
          <button onclick="window.print()" class="bg-gray-700 px-4 py-2 rounded no-print">🖨️ Print Summary</button>
          <a href="home.php" class="block text-red-400 underline mt-4 no-print">← Return Home</a>
        </div>
      <?php endif; ?>
    </form>
  </div>
  <script>
    function calculateFee() {
      const base = <?= $base_fee ?>;
      const weight = parseFloat(document.querySelector('input[name="weight"]').value) || 0;
      const value = parseFloat(document.querySelector('input[name="value"]').value) || 0;
      const fee = base + (weight * 85) + (value * 0.01);
      document.getElementById('feeDisplay').textContent = `₱${fee.toFixed(2)}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
      const weightInput = document.querySelector('input[name="weight"]');
      const valueInput = document.querySelector('input[name="value"]');

      weightInput.addEventListener('input', calculateFee);
      valueInput.addEventListener('input', calculateFee);

      // trigger once at load to ensure accurate display
      calculateFee();
    });
  </script>

</body>

</html>