<?php
$pageTitle = 'Waste Deals';
require_once '../php/config.php';
requireAdmin();
$db = getDB();
$msg = $err = '';

// Add deal
if($_SERVER['REQUEST_METHOD']==='POST') {
  $itemId = (int)($_POST['menu_item_id'] ?? 0);
  $discPrice = (float)($_POST['discounted_price'] ?? 0);
  $qty = (int)($_POST['quantity_available'] ?? 1);
  $hours = (int)($_POST['expires_hours'] ?? 3);
  if(!$itemId || $discPrice <= 0) { $err = 'Select item and set discount price.'; }
  else {
    $orig = $db->query("SELECT price FROM menu_items WHERE id=$itemId")->fetch_assoc()['price'];
    $expires = date('Y-m-d H:i:s', strtotime("+$hours hours"));
    // Deactivate existing deals for same item
    $db->query("UPDATE waste_deals SET is_active=0 WHERE menu_item_id=$itemId");
    $stmt = $db->prepare("INSERT INTO waste_deals (menu_item_id, original_price, discounted_price, quantity_available, expires_at, is_active) VALUES (?,?,?,?,?,1)");
    $stmt->bind_param('iddis', $itemId, $orig, $discPrice, $qty, $expires);
    $stmt->execute() ? $msg = 'Waste deal created!' : $err = 'Error creating deal.';
  }
}

// Deactivate
if(isset($_GET['deactivate'])) {
  $db->query("UPDATE waste_deals SET is_active=0 WHERE id=" . (int)$_GET['deactivate']);
  $msg = 'Deal deactivated.'; 
}

$menuItems = $db->query("SELECT id, name, price FROM menu_items WHERE is_available=1 ORDER BY name");
$deals = $db->query("SELECT wd.*, mi.name, mi.description FROM waste_deals wd JOIN menu_items mi ON wd.menu_item_id=mi.id ORDER BY wd.created_at DESC LIMIT 30");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Waste Deals — EatSmart Admin</title>
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-topbar">
    <h1>🌱 Waste Deals Manager</h1>
    <div class="topbar-actions">
      <button class="topbar-btn primary" onclick="document.getElementById('addDealModal').classList.add('open')">+ Create Deal</button>
    </div>
  </div>
  <div class="admin-content">
    <?php if($msg): ?><div class="alert alert-success">✅ <?php echo $msg; ?></div><?php endif; ?>
    <?php if($err): ?><div class="alert alert-danger">⚠️ <?php echo $err; ?></div><?php endif; ?>

    <div class="alert alert-info" style="margin-bottom:20px">
      ℹ️ Mark surplus food here to list it at a discounted price. Deals automatically expire after the set time. This helps reduce food waste and attracts budget-conscious customers.
    </div>

    <div class="data-card">
      <div class="data-card-header"><h3>All Waste Deals</h3></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Item</th><th>Original</th><th>Discounted</th><th>Discount</th><th>Qty Left</th><th>Expires</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
          <?php while($deal=$deals->fetch_assoc()):
            $pct = round((1-$deal['discounted_price']/$deal['original_price'])*100);
            $expired = strtotime($deal['expires_at']) < time();
          ?>
          <tr>
            <td><strong><?php echo sanitize($deal['name']); ?></strong></td>
            <td><del style="color:var(--text3)">₹<?php echo number_format($deal['original_price'],0); ?></del></td>
            <td><strong style="color:var(--primary)">₹<?php echo number_format($deal['discounted_price'],0); ?></strong></td>
            <td><span class="badge badge-success"><?php echo $pct; ?>% off</span></td>
            <td><?php echo $deal['quantity_available']; ?></td>
            <td style="font-size:0.8rem"><?php echo date('d M, h:i A',strtotime($deal['expires_at'])); ?></td>
            <td>
              <?php if(!$deal['is_active'] || $expired): ?>
                <span class="badge badge-danger">Inactive</span>
              <?php else: ?>
                <span class="badge badge-success">🔴 Live</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if($deal['is_active'] && !$expired): ?>
              <a href="?deactivate=<?php echo $deal['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deactivate this deal?')">Deactivate</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Deal Modal -->
<div class="modal-overlay" id="addDealModal">
  <div class="modal">
    <div class="modal-header">
      <h3>🌱 Create Waste Deal</h3>
      <button class="modal-close" onclick="document.getElementById('addDealModal').classList.remove('open')">✕</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Menu Item *</label>
          <select name="menu_item_id" class="form-control" required id="dealItemSelect" onchange="setOrigPrice(this)">
            <option value="">— Select surplus item —</option>
            <?php
            $menuItems->data_seek(0);
            while($item=$menuItems->fetch_assoc()): ?>
            <option value="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>">
              <?php echo sanitize($item['name']); ?> (₹<?php echo number_format($item['price'],0); ?>)
            </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Original Price</label>
            <input type="text" id="origPriceDisp" class="form-control" readonly placeholder="Auto-filled">
          </div>
          <div class="form-group">
            <label class="form-label">Discounted Price (₹) *</label>
            <input type="number" name="discounted_price" id="discPrice" class="form-control" required step="0.01" min="1" placeholder="e.g. 200" oninput="calcSavings()">
            <div class="form-hint" id="savingsHint"></div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Quantity Available *</label>
            <input type="number" name="quantity_available" class="form-control" value="3" min="1" max="50" required>
          </div>
          <div class="form-group">
            <label class="form-label">Expires In (Hours) *</label>
            <select name="expires_hours" class="form-control">
              <option value="1">1 hour</option>
              <option value="2">2 hours</option>
              <option value="3" selected>3 hours</option>
              <option value="4">4 hours</option>
              <option value="6">6 hours</option>
              <option value="8">8 hours</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addDealModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Deal 🌱</button>
      </div>
    </form>
  </div>
</div>

<script>
let origPrice = 0;
function setOrigPrice(sel) {
  const opt = sel.options[sel.selectedIndex];
  origPrice = parseFloat(opt.dataset.price || 0);
  document.getElementById('origPriceDisp').value = origPrice > 0 ? '₹' + origPrice.toFixed(0) : '';
  calcSavings();
}
function calcSavings() {
  const disc = parseFloat(document.getElementById('discPrice').value);
  if(origPrice > 0 && disc > 0 && disc < origPrice) {
    const pct = Math.round((1 - disc/origPrice)*100);
    document.getElementById('savingsHint').textContent = '✅ ' + pct + '% discount — saves ₹' + (origPrice-disc).toFixed(0);
    document.getElementById('savingsHint').style.color = 'var(--success)';
  } else if(disc >= origPrice) {
    document.getElementById('savingsHint').textContent = '⚠️ Discounted price must be less than original';
    document.getElementById('savingsHint').style.color = 'var(--danger)';
  } else {
    document.getElementById('savingsHint').textContent = '';
  }
}
</script>
</body>
</html>
