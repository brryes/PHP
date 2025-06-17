<?php
session_start();

// Check if cart_data is posted
if (!isset($_POST['cart_data']) || empty($_POST['cart_data'])) {
    echo "<h2 style='color: white; text-align: center;'>No items were selected in your cart.</h2>";
    echo '<p style="text-align: center;"><a href="store.php" style="color: #ff4655;">Go back to store</a></p>';
    exit;
}

// Decode JSON cart data
$cart = json_decode($_POST['cart_data'], true);

if ($cart === null || !is_array($cart) || count($cart) === 0) {
    echo "<h2 style='color: white; text-align: center;'>Your cart is empty or invalid data received.</h2>";
    echo '<p style="text-align: center;"><a href="store.php" style="color: #ff4655;">Go back to store</a></p>';
    exit;
}

// Pricing data
$prices = [
    "Agents" => [ "Jett" => 15, "Reyna" => 12, "Phoenix" => 10, "Sage" => 14, "Sova" => 11, "Waylay" => 13, "Raze" => 16, "Brimstone" => 14, "Cypher" => 12, "Killjoy" => 13 ],
    "Maps" => [ "Bind" => 5, "Haven" => 5, "Split" => 5, "Ascent" => 6, "Icebox" => 6, "Breeze" => 7, "Fracture" => 7, "Lotus" => 8, "Pearl" => 8, "Sunset" => 7 ],
    "Bundles" => [ "Araxys Bundle" => 50, "Elderflame Bundle" => 45, "Evory Bundle" => 40, "Gaia Bundle" => 30, "Glitchpop Bundle" => 25, "Kuronami Bundle" => 35, "Sentinels Bundle" => 28, "Mystbloom Bundle" => 20, "Prelude Bundle" => 22, "Primordium Bundle" => 26 ],
    "Player Cards" => array_fill_keys(["Card1","Card2","Card3","Card4","Card5","Card6","Card7","Card8","Card9","Card10"], 300),
    "Buddy" => array_fill_keys(["Buddy1","Buddy2","Buddy3","Buddy4","Buddy5","Buddy6","Buddy7","Buddy8","Buddy9","Buddy10"], 700),
];

// Helper to get image path like in store.php
function getProductImage($section, $name) {
    $sectionKey = strtolower(str_replace(' ', '_', $section));
    $sections = [
        "Agents" => ["Jett", "Reyna", "Phoenix", "Sage", "Sova", "Waylay", "Raze", "Brimstone", "Cypher", "Killjoy"],
        "Maps" => ["Bind", "Haven", "Split", "Ascent", "Icebox", "Breeze", "Fracture", "Lotus", "Pearl", "Sunset"],
        "Bundles" => ["Araxys Bundle", "Elderflame Bundle", "Evory Bundle", "Gaia Bundle", "Glitchpop Bundle", "Kuronami Bundle", "Sentinels Bundle", "Mystbloom Bundle", "Prelude Bundle", "Primordium Bundle"],
        "Player Cards" => ["Card1","Card2","Card3","Card4","Card5","Card6","Card7","Card8","Card9","Card10"],
        "Buddy" => ["Buddy1","Buddy2","Buddy3","Buddy4","Buddy5","Buddy6","Buddy7","Buddy8","Buddy9","Buddy10"]
    ];
    if (!isset($sections[$section])) return "images/placeholder.png";
    $idx = array_search($name, $sections[$section]);
    if ($idx === false) return "images/placeholder.png";
    return "images/{$sectionKey}_" . ($idx+1) . ".png";
}

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest';

// Normalize section to match keys in $prices
function normalizeSection($section) {
    $section = strtolower(trim($section));
    if (in_array($section, ['bundle', 'bundles'])) return 'Bundles';
    if (in_array($section, ['agent', 'agents'])) return 'Agents';
    if (in_array($section, ['map', 'maps'])) return 'Maps';
    if (in_array($section, ['player card', 'player cards'])) return 'Player Cards';
    if ($section === 'buddy' || $section === 'buddies') return 'Buddy';
    return ucfirst($section); // Capitalize first letter for safety
}

// Normalize name to match keys in $prices (trim spaces)
function normalizeName($name) {
    return trim($name);
}

function getPrice($section, $name, $prices) {
    if (!isset($prices[$section])) return 0;
    if (!isset($prices[$section][$name])) return 0;
    return $prices[$section][$name];
}

// --- SHIPPING LOGIC ---

// Product shipping data (weight in kg, dimensions in inches)
$productShipping = [
    "Agents" => [ "weight" => 1.3, "height" => 10, "width" => 4, "length" => 4 ],
    "Maps" => [ "weight" => 0.2, "height" => 18, "width" => 24, "length" => 1 ],
    "Bundles" => [ "weight" => 2.5, "height" => 12, "width" => 8, "length" => 4 ],
    "Player Cards" => [ "weight" => 0.05, "height" => 4, "width" => 3, "length" => 0.1 ],
    "Buddy" => [ "weight" => 0.1, "height" => 2, "width" => 2, "length" => 1 ],
];

// Box types (max weight in kg, max dimensions in inches, box fee)
$boxTypes = [
    "Small"  => [ "max_weight" => 2,  "max_height" => 10, "max_width" => 8,  "max_length" => 8,  "fee" => 5 ],
    "Medium" => [ "max_weight" => 5,  "max_height" => 18, "max_width" => 18, "max_length" => 18, "fee" => 10 ],
    "Large"  => [ "max_weight" => 10, "max_height" => 24, "max_width" => 24, "max_length" => 24, "fee" => 18 ],
    "XLarge" => [ "max_weight" => 30, "max_height" => 36, "max_width" => 36, "max_length" => 36, "fee" => 30 ],
];

// Calculate total shipping requirements
$totalWeight = 0;
$maxHeight = 0;
$maxWidth = 0;
$maxLength = 0;

foreach ($cart as $item) {
    $section = normalizeSection($item['section'] ?? '');
    $qty = intval($item['quantity'] ?? 1);
    if (isset($productShipping[$section])) {
        $totalWeight += $productShipping[$section]['weight'] * $qty;
        $maxHeight = max($maxHeight, $productShipping[$section]['height']);
        $maxWidth  = max($maxWidth,  $productShipping[$section]['width']);
        $maxLength = max($maxLength, $productShipping[$section]['length']);
    }
}

// Determine box size and box fee
$selectedBox = "XLarge";
$boxFee = $boxTypes["XLarge"]["fee"];
foreach ($boxTypes as $box => $limits) {
    if (
        $totalWeight <= $limits["max_weight"] &&
        $maxHeight  <= $limits["max_height"] &&
        $maxWidth   <= $limits["max_width"] &&
        $maxLength  <= $limits["max_length"]
    ) {
        $selectedBox = $box;
        $boxFee = $limits["fee"];
        break;
    }
}

// Set a flat shipping fee (customize as needed)
$shippingFee = 3;

// Calculate totals
$total = 0;
foreach ($cart as $item) {
    $rawName = $item['name'] ?? 'Unknown';
    $rawSection = $item['section'] ?? 'Unknown';
    $quantity = intval($item['quantity'] ?? 1);

    $section = normalizeSection($rawSection);
    $name = normalizeName($rawName);

    $price = getPrice($section, $name, $prices);
    $subtotal = $price * $quantity;
    $total += $subtotal;
}
$discount = $total * 0.25;
$finalTotal = $total - $discount + $boxFee + $shippingFee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Confirm Purchase</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap");
    body {
      background: #181a20 url('images/storebg.png') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Montserrat', Arial, sans-serif;
      min-height: 100vh;
      margin: 0;
    }
    .summary-label { color: #ffbb00; }
    .summary-value { color: #fff; }
    .summary-row { border-bottom: 1px solid #333; padding: 0.7rem 0; }
    .summary-row:last-child { border-bottom: none; }
    .total-row { font-size: 1.5rem; color: #ff4655; font-weight: bold; }
    .payment-option {
      box-shadow: 0 2px 12px 0 rgba(0,0,0,0.25);
      transition: box-shadow 0.2s, border 0.2s, transform 0.2s;
      border-radius: 1rem;
      background: #23272f;
      border: 2px solid transparent;
      aspect-ratio: 1/1;
      min-width: 120px;
      min-height: 120px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .payment-option.selected {
      border-color: #ffbb00;
      box-shadow: 0 0 0 4px #ffbb00, 0 2px 16px 0 rgba(0,0,0,0.25);
      transform: scale(1.04);
    }
    .payment-option img { width: 60px; height: 60px; }
    .payment-radio { display: none; }
    .cart-item-img {
      width: 56px;
      height: 56px;
      object-fit: contain;
      border-radius: 0.5rem;
      background: #181a20;
      border: 1px solid #333;
      margin-right: 1rem;
    }
    /* Horizontal order summary styles */
    .order-summary-table {
      width: 100%;
      overflow-x: auto;
      margin-bottom: 1.5rem;
    }
    .order-summary-header, .order-summary-row {
      display: flex;
      align-items: center;
      padding: 0.5rem 0;
      border-bottom: 1px solid #333;
    }
    .order-summary-header {
      font-weight: bold;
      color: #ffbb00;
      background: #23272f;
    }
    .order-summary-row:last-child {
      border-bottom: none;
    }
    .order-summary-col-img { width: 56px; flex-shrink: 0; }
    .order-summary-col-name { flex: 2; min-width: 120px; }
    .order-summary-col-section { flex: 1; min-width: 80px; text-align: center; }
    .order-summary-col-qty { flex: 1; min-width: 60px; text-align: center; }
    .order-summary-col-price { flex: 1; min-width: 90px; text-align: right; }
    .order-summary-col-subtotal { flex: 1; min-width: 110px; text-align: right; color: #ff4655; font-weight: bold; }
    @media (max-width: 1100px) {
      .flex-col-on-mobile { flex-direction: column !important; }
      .side-panel { width: 100% !important; margin-left: 0 !important; }
    }
    @media (max-width: 700px) {
      .w-[480px] { width: 100% !important; }
      .h-[210px] { height: 140px !important; }
      .w-[320px] { width: 100% !important; }
      .min-w-[300px] { min-width: 0 !important; }
      .cart-item-img { width: 40px; height: 40px; margin-right: 0.5rem; }
      .order-summary-header, .order-summary-row { font-size: 0.95rem; }
      .payment-method-grid { grid-template-columns: 1fr 1fr !important; }
    }
    .payment-method-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
  </style>
</head>
<body class="relative text-white min-h-screen overflow-x-hidden">
  <div class="relative z-10 max-w-7xl mx-auto px-4 py-10">
    <!-- Back Button -->
    <a href="store.php" class="inline-block mb-6 px-5 py-2 rounded bg-[#23272f] text-[#ff4655] font-semibold hover:bg-[#ff4655] hover:text-white transition">
      <i class="fas fa-arrow-left mr-2"></i>Back to Store
    </a>

    <h1 class="text-center text-[2rem] tracking-widest font-bold mb-10 text-[#ff4655] drop-shadow">
      CONFIRM PURCHASE — <?= $username ?>
    </h1>

    <div class="flex gap-8 items-start flex-col-on-mobile lg:flex-row">
      <!-- Order Summary -->
      <div class="flex-1 min-w-[340px]">
        <div class="bg-[#23272f] rounded-lg shadow-lg p-6">
          <h2 class="text-lg font-bold mb-4 text-[#ffbb00]">Order Summary</h2>
          <div class="order-summary-table">
            <div class="order-summary-header">
              <div class="order-summary-col-img"></div>
              <div class="order-summary-col-name">Product</div>
              <div class="order-summary-col-section">Section</div>
              <div class="order-summary-col-qty">Qty</div>
              <div class="order-summary-col-price">Price</div>
              <div class="order-summary-col-subtotal">Subtotal</div>
            </div>
            <?php foreach ($cart as $item):
              $rawName = $item['name'] ?? 'Unknown';
              $rawSection = $item['section'] ?? 'Unknown';
              $quantity = intval($item['quantity'] ?? 1);

              $section = normalizeSection($rawSection);
              $name = normalizeName($rawName);

              $price = getPrice($section, $name, $prices);
              $subtotal = $price * $quantity;
              $imgPath = getProductImage($section, $name);
            ?>
              <div class="order-summary-row">
                <div class="order-summary-col-img">
                  <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($name) ?>" class="cart-item-img" />
                </div>
                <div class="order-summary-col-name"><?= htmlspecialchars($name) ?></div>
                <div class="order-summary-col-section"><?= htmlspecialchars($section) ?></div>
                <div class="order-summary-col-qty"><?= $quantity ?></div>
                <div class="order-summary-col-price">₱<?= number_format($price, 2) ?></div>
                <div class="order-summary-col-subtotal">₱<?= number_format($subtotal, 2) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <!-- Side Panel: Total, Shipping, Payment -->
      <div class="w-full lg:w-[420px] flex-shrink-0 side-panel" style="margin-left:0;">
        <div class="bg-[#23272f] rounded-lg shadow-lg p-6 mb-6">
          <div class="flex justify-between summary-row">
            <span class="summary-label">Subtotal</span>
            <span class="summary-value">₱<?= number_format($total, 2) ?></span>
          </div>
          <div class="flex justify-between summary-row">
            <span class="summary-label">Discount</span>
            <span class="summary-value text-[#b02a2a]">- ₱<?= number_format($discount, 2) ?></span>
          </div>
          <div class="flex justify-between summary-row">
            <span class="summary-label">Box Fee <span class="text-xs text-gray-400">(<?= $selectedBox ?> Box)</span></span>
            <span class="summary-value text-blue-400">₱<?= number_format($boxFee, 2) ?></span>
          </div>
          <div class="flex justify-between summary-row">
            <span class="summary-label">Shipping Fee</span>
            <span class="summary-value text-green-400">₱<?= number_format($shippingFee, 2) ?></span>
          </div>
          <div class="flex justify-between total-row mt-4">
            <span>Total Payable</span>
            <span>₱<?= number_format($finalTotal, 2) ?></span>
          </div>
        </div>
        <form action="payment.php" method="post" onsubmit="return validatePaymentMethod();" class="bg-[#23272f] rounded-lg shadow-lg p-6">
          <div class="mb-4">
            <label class="block mb-2 text-lg font-semibold text-[#ffbb00]">Shipping Address:</label>
            <textarea name="shipping_address" required rows="3" class="w-full p-3 rounded bg-[#181a20] text-white border border-gray-600 focus:border-[#ff4655]"></textarea>
          </div>
          <div class="mb-6">
            <label class="block mb-4 text-lg font-semibold text-[#ffbb00]">Select Payment Method:</label>
            <div class="payment-method-grid">
              <label class="payment-option cursor-pointer hover:shadow-2xl hover:scale-105 transition-all"
                     data-method="paymaya">
                <input type="radio" name="payment_method" value="paymaya" class="payment-radio" required>
                <img src="images/paymaya.png" alt="PayMaya" class="mb-2"/>
                <span class="font-bold text-lg">PayMaya</span>
              </label>
              <label class="payment-option cursor-pointer hover:shadow-2xl hover:scale-105 transition-all"
                     data-method="banktransfer">
                <input type="radio" name="payment_method" value="banktransfer" class="payment-radio" required>
                <img src="images/bank.png" alt="Bank Transfer" class="mb-2"/>
                <span class="font-bold text-lg">Bank Transfer</span>
              </label>
              <label class="payment-option cursor-pointer hover:shadow-2xl hover:scale-105 transition-all"
                     data-method="gcash">
                <input type="radio" name="payment_method" value="gcash" class="payment-radio" required>
                <img src="images/gcash.png" alt="GCash" class="mb-2"/>
                <span class="font-bold text-lg">GCash</span>
              </label>
              <label class="payment-option cursor-pointer hover:shadow-2xl hover:scale-105 transition-all"
                     data-method="711">
                <input type="radio" name="payment_method" value="711" class="payment-radio" required>
                <img src="images/711.png" alt="7/11" class="mb-2"/>
                <span class="font-bold text-lg">7/11 Store</span>
              </label>
            </div>
          </div>
          <!-- Pass cart data and shipping info to payment.php -->
          <input type="hidden" name="cart_data" value='<?= htmlspecialchars(json_encode($cart), ENT_QUOTES) ?>'>
          <input type="hidden" name="shipping_fee" value="<?= $shippingFee ?>">
          <input type="hidden" name="box_fee" value="<?= $boxFee ?>">
          <input type="hidden" name="box_size" value="<?= $selectedBox ?>">
          <input type="hidden" name="total_fee" value="<?= $finalTotal ?>">
          <button type="submit"
                  class="w-full mt-4 py-3 rounded bg-gradient-to-r from-[#ffbb00] to-[#ff4655] text-[#181a20] text-xl font-bold tracking-wide hover:brightness-110 transition">
            <i class="fas fa-check mr-2"></i> Purchase
          </button>
        </form>
      </div>
    </div>
    <script>
      function validatePaymentMethod() {
        const checked = document.querySelector('input[name="payment_method"]:checked');
        if (!checked) {
          alert('Please select a payment method.');
          return false;
        }
        return true;
      }
      // Highlight selected payment method
      document.querySelectorAll('.payment-radio').forEach(radio => {
        radio.addEventListener('change', function() {
          document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
          const label = this.closest('.payment-option');
          label.classList.add('selected');
          label.setAttribute('data-method', this.value);
        });
      });
    </script>
  </div>
</body>
</html>