<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: index.php?error=Please log in first.');
  exit;
}
//HELLLOOO THIS IS BEA
// Only reset order_saved and last_order when starting a new order (step 1)
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' &&
    isset($_SESSION['order_saved']) &&
    (empty($_GET['step']) || $_GET['step'] == '1')
) {
    unset($_SESSION['order_saved']);
    unset($_SESSION['last_order']);
}

$username = htmlspecialchars($_SESSION['username']);
$currentStep = $_POST['step'] ?? ($_GET['step'] ?? '1');

// Fee calculation based on location
function get_base_fee($sender_city, $recipient_city)
{
  if (strtolower($sender_city) === strtolower($recipient_city)) {
    return 50; // same city
  }
  return 80; // different city
}

// Preserve function now checks POST first, then last_order session for GET step 4
function preserve($name)
{
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    return htmlspecialchars($_POST[$name] ?? '');
  } elseif (isset($_SESSION['last_order'][$name])) {
    return htmlspecialchars($_SESSION['last_order'][$name]);
  }
  return '';
}

function validate_contact($number)
{
  $cleaned = preg_replace('/\s+/', '', $number);
  return preg_match('/^(09\d{9}|\+639\d{9})$/', $cleaned);
}

// --- Save order to MySQL database only once and redirect before output ---
// Only insert order on POST step 4, not on every load of step 4
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['step']) && $_POST['step'] == '4' &&
    !isset($_SESSION['order_saved'])
) {
    $pickup_date = preserve('pickup_date');
    $delivery_date = '';
    if ($pickup_date) {
      $delivery_date = date('Y-m-d', strtotime($pickup_date . ' +2 days'));
    }

    require_once 'db_connect.php';
    $order = [
      'username' => $_SESSION['username'],
      'sender_name' => preserve('sender_name'),
      'sender_contact' => preserve('sender_contact'),
      'sender_address' => preserve('sender_address'),
      'sender_region' => preserve('sender_region'),
      'sender_province' => preserve('sender_province'),
      'sender_city' => preserve('sender_city'),
      'sender_barangay' => preserve('sender_barangay'),
      'recipient_name' => preserve('recipient_name'),
      'recipient_contact' => preserve('recipient_contact'),
      'recipient_address' => preserve('recipient_address'),
      'recipient_region' => preserve('recipient_region'),
      'recipient_province' => preserve('recipient_province'),
      'recipient_city' => preserve('recipient_city'),
      'recipient_barangay' => preserve('recipient_barangay'),
      'item_name' => preserve('item_name'),
      'quantity' => preserve('quantity'),
      'item_category' => preserve('item_category'),
      'weight' => preserve('weight'),
      'value' => preserve('value'),
      'pickup_time' => preserve('pickup_time'),
      'pickup_date' => $pickup_date,
      'delivery_date' => $delivery_date,
      'remarks' => preserve('remarks'),
      'status' => 'Pending Pickup'
    ];

    $sql = "INSERT INTO orders (
      username, sender_name, sender_contact, sender_address, sender_region, sender_province, sender_city, sender_barangay,
      recipient_name, recipient_contact, recipient_address, recipient_region, recipient_province, recipient_city, recipient_barangay,
      item_name, quantity, item_category, weight, value, pickup_time, pickup_date, delivery_date, remarks, status
    ) VALUES (
      :username, :sender_name, :sender_contact, :sender_address, :sender_region, :sender_province, :sender_city, :sender_barangay,
      :recipient_name, :recipient_contact, :recipient_address, :recipient_region, :recipient_province, :recipient_city, :recipient_barangay,
      :item_name, :quantity, :item_category, :weight, :value, :pickup_time, :pickup_date, :delivery_date, :remarks, :status
    )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($order);
    $_SESSION['order_saved'] = true;
    $_SESSION['last_order'] = $order;
    header("Location: place_order.php?step=4");
    exit;
}

$order_saved = false;

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
        empty($_POST['sender_region']) || empty($_POST['sender_province']) || empty($_POST['sender_city']) || empty($_POST['sender_barangay'])
      ) {
        $currentStep = '1';
      } else {
        $currentStep = $nextStep;
      }
    } elseif ($currentStep === '2') {
      if (
        !isset($_POST['recipient_name'], $_POST['recipient_contact'], $_POST['recipient_address']) ||
        !validate_contact($_POST['recipient_contact']) ||
        empty($_POST['recipient_region']) || empty($_POST['recipient_province']) || empty($_POST['recipient_city']) || empty($_POST['recipient_barangay'])
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
        empty($_POST['pickup_date']) ||
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
let regions = [], provinces = [], cities = [], barangays = [];

// Load JSON and always populate dropdowns after DOM is ready
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
  // Ensure DOM is ready before populating
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      populateRegions('sender');
      populateRegions('recipient');
      restoreAllDropdowns();
    });
  } else {
    populateRegions('sender');
    populateRegions('recipient');
    restoreAllDropdowns();
  }
});

function populateRegions(prefix) {
  const regionSelect = document.getElementById(prefix + '_region');
  if (!regionSelect) return;
  regionSelect.innerHTML = '<option value="">Select Region</option>';
  regions.forEach(region => {
    const opt = document.createElement('option');
    opt.value = region.region_name;
    opt.textContent = region.region_name;
    regionSelect.appendChild(opt);
  });
  regionSelect.onchange = function() { populateProvinces(prefix); };
}

function populateProvinces(prefix) {
  const regionName = document.getElementById(prefix + '_region').value;
  const provinceSelect = document.getElementById(prefix + '_province');
  if (!provinceSelect) return;
  provinceSelect.innerHTML = '<option value="">Select Province</option>';
  document.getElementById(prefix + '_city').innerHTML = '<option value="">Select City/Municipality</option>';
  document.getElementById(prefix + '_barangay').innerHTML = '<option value="">Select Barangay</option>';
  const region = regions.find(r => r.region_name === regionName);
  if (!region) return;
  provinces.forEach(province => {
    if (province.region_code === region.region_code) {
      const opt = document.createElement('option');
      opt.value = province.province_name;
      opt.textContent = province.province_name;
      provinceSelect.appendChild(opt);
    }
  });
  provinceSelect.onchange = function() { populateCities(prefix); };
}

function populateCities(prefix) {
  const provName = document.getElementById(prefix + '_province').value;
  const citySelect = document.getElementById(prefix + '_city');
  if (!citySelect) return;
  citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
  document.getElementById(prefix + '_barangay').innerHTML = '<option value="">Select Barangay</option>';
  const province = provinces.find(p => p.province_name === provName);
  if (!province) return;
  cities.forEach(city => {
    if (city.province_code === province.province_code) {
      const opt = document.createElement('option');
      opt.value = city.city_name;
      opt.textContent = city.city_name;
      citySelect.appendChild(opt);
    }
  });
  citySelect.onchange = function() { populateBarangays(prefix); };
}

function populateBarangays(prefix) {
  const cityName = document.getElementById(prefix + '_city').value;
  const barangaySelect = document.getElementById(prefix + '_barangay');
  if (!barangaySelect) return;
  barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
  const city = cities.find(c => c.city_name === cityName);
  if (!city) return;
  barangays.forEach(brgy => {
    if (brgy.city_code === city.city_code) {
      const opt = document.createElement('option');
      opt.value = brgy.brgy_name;
      opt.textContent = brgy.brgy_name;
      barangaySelect.appendChild(opt);
    }
  });
}

// Restore dropdowns after postback or step change
function restoreAllDropdowns() {
  const senderRegion = <?= json_encode(preserve('sender_region')) ?>;
  const senderProvince = <?= json_encode(preserve('sender_province')) ?>;
  const senderCity = <?= json_encode(preserve('sender_city')) ?>;
  const senderBarangay = <?= json_encode(preserve('sender_barangay')) ?>;
  const recipientRegion = <?= json_encode(preserve('recipient_region')) ?>;
  const recipientProvince = <?= json_encode(preserve('recipient_province')) ?>;
  const recipientCity = <?= json_encode(preserve('recipient_city')) ?>;
  const recipientBarangay = <?= json_encode(preserve('recipient_barangay')) ?>;

  function restoreDropdown(prefix, region, province, city, barangay) {
    let tries = 0;
    function tryRestore() {
      tries++;
      if (regions.length && provinces.length && cities.length && barangays.length) {
        // Region
        if (region) {
          document.getElementById(prefix + '_region').value = region;
          populateProvinces(prefix);
        }
        // Province
        if (province) {
          document.getElementById(prefix + '_province').value = province;
          populateCities(prefix);
        }
        // City
        if (city) {
          document.getElementById(prefix + '_city').value = city;
          populateBarangays(prefix);
        }
        // Barangay
        if (barangay) {
          document.getElementById(prefix + '_barangay').value = barangay;
        }
      } else if (tries < 20) {
        setTimeout(tryRestore, 100);
      }
    }
    tryRestore();
  }

  restoreDropdown('sender', senderRegion, senderProvince, senderCity, senderBarangay);
  restoreDropdown('recipient', recipientRegion, recipientProvince, recipientCity, recipientBarangay);
}
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

      <?php
      // Helper: output hidden fields for a list of names
      function hidden_fields($fields) {
        foreach ($fields as $f) {
          echo '<input type="hidden" name="' . $f . '" value="' . preserve($f) . '">' . "\n";
        }
      }
      ?>

      <?php if ($currentStep == '1'): ?>
        <h2 class="text-xl font-semibold">01. Sender Information</h2>
        <input name="sender_name" placeholder="Full Name" value="<?= preserve('sender_name') ?>" required
          class="bg-gray-700 p-2 rounded w-full">
        <input name="sender_contact" placeholder="Contact Number" pattern="^(09\d{9}|\+639\d{9})$"
          title="09123456789 or +639123456789" value="<?= preserve('sender_contact') ?>" required
          class="bg-gray-700 p-2 rounded w-full">
        <input name="sender_address" placeholder="Street" value="<?= preserve('sender_address') ?>" required
          class="bg-gray-700 p-2 rounded w-full">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <select name="sender_region" id="sender_region" required class="bg-gray-700 p-2 rounded">
            <option value="">Select Region</option>
          </select>
          <select name="sender_province" id="sender_province" required class="bg-gray-700 p-2 rounded">
            <option value="">Select Province</option>
          </select>
          <select name="sender_city" id="sender_city" required class="bg-gray-700 p-2 rounded">
            <option value="">Select City/Municipality</option>
          </select>
          <select name="sender_barangay" id="sender_barangay" required class="bg-gray-700 p-2 rounded">
            <option value="">Select Barangay</option>
          </select>
        </div>
        <button type="submit" name="step" value="2"
          class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded no-print float-right">Next →</button>

      <?php elseif ($currentStep == '2'): ?>
        <?php
        // Carry sender fields forward
        hidden_fields([
          'sender_name','sender_contact','sender_address',
          'sender_region','sender_province','sender_city','sender_barangay'
        ]);
        ?>
        <h2 class="text-xl font-semibold">02. Recipient Information</h2>
        <input name="recipient_name" placeholder="Full Name" value="<?= preserve('recipient_name') ?>" required
          class="bg-gray-700 p-2 rounded w-full">
        <input name="recipient_contact" placeholder="Contact Number" pattern="^(09\d{9}|\+639\d{9})$"
          value="<?= preserve('recipient_contact') ?>" required class="bg-gray-700 p-2 rounded w-full">
        <input name="recipient_address" placeholder="Street" value="<?= preserve('recipient_address') ?>" required
          class="bg-gray-700 p-2 rounded w-full">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <select name="recipient_region" id="recipient_region" required class="bg-gray-700 p-2 rounded">
            <option value="">Select Region</option>
          </select>
          <select name="recipient_province" id="recipient_province" required class="bg-gray-700 p-2 rounded">
            <option value="">Select Province</option>
          </select>
          <select name="recipient_city" id="recipient_city" required class="bg-gray-700 p-2 rounded">
            <option value="">Select City/Municipality</option>
          </select>
          <select name="recipient_barangay" id="recipient_barangay" required class="bg-gray-700 p-2 rounded">
            <option value="">Select Barangay</option>
          </select>
        </div>
        <div class="flex justify-between no-print">
          <button type="submit" name="step" value="1" class="bg-gray-700 px-4 py-2 rounded">← Back</button>
          <button type="submit" name="step" value="3" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded">Next
            →</button>
        </div>

      <?php elseif ($currentStep == '3'): ?>
        <?php
        // Carry sender and recipient fields forward
        hidden_fields([
          'sender_name','sender_contact','sender_address',
          'sender_region','sender_province','sender_city','sender_barangay',
          'recipient_name','recipient_contact','recipient_address',
          'recipient_region','recipient_province','recipient_city','recipient_barangay'
        ]);
        $senderCity = preserve('sender_city');
        $recipientCity = preserve('recipient_city');
        $base_fee = get_base_fee($senderCity, $recipientCity);
        $weight = floatval(preserve('weight'));
        $value = floatval(preserve('value'));
        $estimated = $base_fee + ($weight * 40) + ($value * 0.01);
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
          <input type="date" name="pickup_date" value="<?= preserve('pickup_date') ?>" required 
            class="bg-gray-700 p-4 rounded text-lg border-2 border-red-500 focus:border-red-700 focus:outline-none transition w-full" 
            style="font-size:1.25rem;">
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
        <?php
        // Carry all fields forward (for printing or further processing)
        hidden_fields([
          'sender_name','sender_contact','sender_address',
          'sender_region','sender_province','sender_city','sender_barangay',
          'recipient_name','recipient_contact','recipient_address',
          'recipient_region','recipient_province','recipient_city','recipient_barangay',
          'item_name','quantity','item_category','weight','value','pickup_time','pickup_date','remarks'
        ]);
        // Delivery date: 2 days after pickup_date
        $pickup_date = preserve('pickup_date');
        $delivery_date = '';
        if ($pickup_date) {
          $delivery_date = date('Y-m-d', strtotime($pickup_date . ' +2 days'));
        }
        ?>
        <div class="print-section">
          <h2 class="text-xl font-semibold mb-4">04. Order Complete</h2>
          <div class="mb-4">
            <strong>Sender:</strong><br>
            <?= preserve('sender_name') ?: 'N/A' ?><br>
            <?= preserve('sender_contact') ?: 'N/A' ?><br>
            <?= preserve('sender_address') ?: 'N/A' ?><br>
            <?= preserve('sender_barangay') ?: '' ?><?= preserve('sender_barangay') ? ', ' : '' ?><?= preserve('sender_city') ?: '' ?><?= preserve('sender_city') ? ', ' : '' ?><?= preserve('sender_province') ?: '' ?><?= preserve('sender_province') ? ', ' : '' ?><?= preserve('sender_region') ?: '' ?>
          </div>
          <div class="mb-4">
            <strong>Recipient:</strong><br>
            <?= preserve('recipient_name') ?: 'N/A' ?><br>
            <?= preserve('recipient_contact') ?: 'N/A' ?><br>
            <?= preserve('recipient_address') ?: 'N/A' ?><br>
            <?= preserve('recipient_barangay') ?: '' ?><?= preserve('recipient_barangay') ? ', ' : '' ?><?= preserve('recipient_city') ?: '' ?><?= preserve('recipient_city') ? ', ' : '' ?><?= preserve('recipient_province') ?: '' ?><?= preserve('recipient_province') ? ', ' : '' ?><?= preserve('recipient_region') ?: '' ?>
          </div>
          <div class="mb-4">
            <strong>Package:</strong><br>
            Category: <?= preserve('item_category') ?: 'N/A' ?><br>
            Weight: <?= is_numeric(preserve('weight')) ? preserve('weight') : '0' ?> kg<br>
            <?php $value = preserve('value'); ?>
            Value: ₱<?= is_numeric($value) ? number_format((float)$value, 2) : '0.00' ?><br>
            Pickup: <?= preserve('pickup_time') ?: 'N/A' ?> on <?= $pickup_date ?: 'N/A' ?><br>
            Delivery Date: <?= $delivery_date ?: 'N/A' ?><br>
            Remarks: <?= preserve('remarks') ?: 'None' ?>
          </div>
          <button onclick="window.print()" class="bg-gray-700 px-4 py-2 rounded no-print">🖨️ Print Summary</button>
          <a href="order_tracker.php" class="block text-red-400 underline mt-4 no-print">→ Track Order</a>
          <a href="home.php" class="block text-red-400 underline mt-4 no-print">← Return Home</a>
        </div>
      <?php endif; ?>
    </form>
  </div>
  <script>
function calculateFee() {
  const base = <?= isset($base_fee) ? $base_fee : 0 ?>;
  const weightInput = document.querySelector('input[name="weight"]');
  const valueInput = document.querySelector('input[name="value"]');
  const feeDisplay = document.getElementById('feeDisplay');
  if (!weightInput || !valueInput || !feeDisplay) return;

  const weight = parseFloat(weightInput.value) || 0;
  const value = parseFloat(valueInput.value) || 0;
  const fee = base + (weight * 40) + (value * 0.01);
  feeDisplay.textContent = fee.toLocaleString('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const weightInput = document.querySelector('input[name="weight"]');
  const valueInput = document.querySelector('input[name="value"]');
  if (weightInput) weightInput.addEventListener('input', calculateFee);
  if (valueInput) valueInput.addEventListener('input', calculateFee);
  calculateFee(); // Initial calculation on load
});
</script>
