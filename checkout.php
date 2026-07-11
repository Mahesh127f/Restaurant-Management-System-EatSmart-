<?php
$pageTitle = 'Checkout';
require_once 'includes/header.php';
requireLogin();
$db = getDB();
$success = $error = '';
$newOrderId = null;

if($_SERVER['REQUEST_METHOD']==='POST') {
  $cartJson = $_POST['cart_data'] ?? '[]';
  $cart = json_decode($cartJson, true);
  $orderType = sanitize($_POST['order_type'] ?? 'dine-in');
  $tableNum = sanitize($_POST['table_number'] ?? '');
  $instructions = sanitize($_POST['special_instructions'] ?? '');

  if(empty($cart)) {
    $error = 'Your cart is empty.';
  } else {
    $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart));
    $tax = $subtotal * 0.05;
    $total = $subtotal + $tax;

    $stmt = $db->prepare("INSERT INTO orders (user_id, order_type, status, total_amount, table_number, special_instructions) VALUES (?,?,?,?,?,?)");
    $userId = $_SESSION['user_id'];
    $status = 'pending';
    $stmt->bind_param('issdss', $userId, $orderType, $status, $total, $tableNum, $instructions);


    if($stmt->execute()) {
      $orderId = $stmt->insert_id;
      $newOrderId = $orderId;
      foreach($cart as $item) {
        $istmt = $db->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price) VALUES (?,?,?,?)");
        $istmt->bind_param('iiid', $orderId, $item['id'], $item['qty'], $item['price']);
        $istmt->execute();
      }
      // Update loyalty points (1 point per ₹10)
      $pts = floor($total / 10);
      $db->query("UPDATE users SET loyalty_points = loyalty_points + $pts WHERE id = {$_SESSION['user_id']}");
      $success = "Order placed! Order #" . str_pad($orderId, 4, '0', STR_PAD_LEFT);
    } else {
      $error = 'Failed to place order. Please try again.';
    }
  }
}
?>

<section class="section" style="padding-top:20px">
  <div class="container" style="max-width:640px">
    <div class="section-title">
      <h2>🧾 Checkout</h2>
      <p>Review your order before placing</p>
    </div>

    <?php if($success): ?>
    <div class="card" style="text-align:center;padding:40px">
      <div style="font-size:3.5rem;margin-bottom:16px">🎉</div>
      <h2 style="margin-bottom:8px"><?php echo $success; ?></h2>
      <p style="margin-bottom:24px">Your order has been received! We'll start preparing it right away.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="my_orders.php?id=<?php echo $newOrderId; ?>" class="btn btn-primary btn-lg">📦 Track My Order</a>
        <a href="menu.php" class="btn btn-secondary">Continue Ordering</a>
      </div>
    </div>
    <script>Cart.clear();</script>
    <?php else: ?>

    <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

    <div id="checkoutContent">
      <!-- Order Summary populated by JS -->
      <div class="card mb-3" id="orderSummaryCard">
        <div class="card-header"><h3>📋 Order Summary</h3></div>
        <div class="card-body" id="orderSummaryBody">
          <div class="spinner"></div>
        </div>
      </div>

      <form method="POST" id="checkoutForm">
        <input type="hidden" name="cart_data" id="cartDataInput">
        <div class="card mb-3">
          <div class="card-header"><h3>🍽️ Order Details</h3></div>
          <div class="card-body">
            <div class="form-group">
              <label class="form-label">Order Type</label>
              <select name="order_type" class="form-control" id="orderTypeSelect">
                <option value="dine-in">🪑 Dine In</option>
                <option value="pre-order">⏱️ Pre-Order (arrive later)</option>
                <option value="takeaway">🥡 Takeaway</option>
              </select>
            </div>
            <div class="form-group" id="tableGroup">
              <label class="form-label">Table Number (optional)</label>
              <input type="text" name="table_number" class="form-control" placeholder="e.g. T03">
            </div>
            <div class="form-group">
              <label class="form-label">Special Instructions</label>
              <textarea name="special_instructions" class="form-control" rows="2" placeholder="Allergies, spice level, extra requests..."></textarea>
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-body">
            <div class="cart-total-row"><span>Subtotal</span><span id="coSubtotal">₹0</span></div>
            <div class="cart-total-row"><span>GST (5%)</span><span id="coTax">₹0</span></div>
            <div class="cart-total-row grand"><span>Total Payable</span><span id="coTotal">₹0</span></div>
            <?php
            $user = $db->prepare("SELECT loyalty_points FROM users WHERE id=?");
            $user->bind_param('i', $_SESSION['user_id']); $user->execute();
            $pts = $user->get_result()->fetch_assoc()['loyalty_points'];
            ?>
            <div style="font-size:0.82rem;color:var(--success);margin-bottom:12px">
              🏆 You have <?php echo $pts; ?> loyalty points (₹<?php echo floor($pts/10); ?> equivalent)
            </div>
            <p style="font-size:0.8rem;color:var(--text3);margin-bottom:12px">Payment at the restaurant — Cash, UPI, or Card accepted.</p>
            <button type="submit" class="btn btn-primary btn-block btn-lg" id="placeOrderBtn">
              ✅ Place Order
            </button>
          </div>
        </div>
      </form>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  Cart.render();
  const items = Cart.items;
  const body = document.getElementById('orderSummaryBody');

  if(!items || items.length === 0) {
    body.innerHTML = '<div class="empty-state"><div class="icon">🛒</div><p>Your cart is empty. <a href="menu.php">Browse menu →</a></p></div>';
    document.getElementById('checkoutForm')?.remove();
    return;
  }

  body.innerHTML = items.map(i => `
    <div class="cart-item" style="border-bottom:1px solid var(--border);padding:10px 0">
      <div class="cart-item-img">${i.emoji}</div>
      <div class="cart-item-info">
        <div class="cart-item-name">${i.name}</div>
        <div style="font-size:0.82rem;color:var(--text3)">₹${i.price} × ${i.qty}</div>
      </div>
      <div style="font-weight:700;color:var(--primary)">₹${(i.price*i.qty).toFixed(0)}</div>
    </div>
  `).join('');

  const sub = Cart.total();
  const tax = sub * 0.05;
  document.getElementById('coSubtotal').textContent = '₹' + sub.toFixed(0);
  document.getElementById('coTax').textContent = '₹' + tax.toFixed(0);
  document.getElementById('coTotal').textContent = '₹' + (sub+tax).toFixed(0);
  document.getElementById('cartDataInput').value = JSON.stringify(items);
});

document.getElementById('orderTypeSelect')?.addEventListener('change', e => {
  document.getElementById('tableGroup').style.display = e.target.value === 'takeaway' ? 'none' : '';
});
</script>

<?php require_once 'includes/footer.php'; ?>
