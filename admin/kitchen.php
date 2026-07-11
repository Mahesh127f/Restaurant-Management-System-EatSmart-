<?php
require_once '../php/config.php';
requireAdmin();
$db = getDB();
$msg = '';

if(isset($_POST['update_status'])) {
  $oid = (int)$_POST['order_id'];
  $st = sanitize($_POST['new_status']);
  if(in_array($st,['accepted','cooking','ready','delivered'])) {
    $db->query("UPDATE orders SET status='$st' WHERE id=$oid");
  }
  header('Location: kitchen.php'); exit;
}

$orders = $db->query("SELECT o.*, u.name as cname FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE o.status IN ('pending','accepted','cooking','ready') ORDER BY FIELD(o.status,'pending','accepted','cooking','ready'), o.created_at ASC");
$ordersArr = []; while($o=$orders->fetch_assoc()) $ordersArr[] = $o;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Kitchen Board — EatSmart</title>
  <link rel="stylesheet" href="../css/admin.css">
  <style>
    body { background: #111; }
    .kitchen-topbar { background: #1A1108; padding: 14px 24px; display:flex; align-items:center; justify-content:space-between; border-bottom:2px solid #333; }
    .kitchen-topbar h1 { color: var(--accent); font-family: var(--font-display); font-size:1.4rem; }
    .kitchen-topbar .time { color:rgba(255,255,255,0.5); font-size:1rem; }
    .kitchen-main { padding: 20px; }
    .kb-cols { display:grid; grid-template-columns: repeat(4,1fr); gap:16px; }
    .kb-col-header { color:white; font-weight:700; font-size:0.85rem; text-transform:uppercase; letter-spacing:1px; padding:10px 0; border-bottom:3px solid; margin-bottom:12px; }
    .kb-col-header.pending { color:#FFB300; border-color:#FFB300; }
    .kb-col-header.accepted { color:#42A5F5; border-color:#42A5F5; }
    .kb-col-header.cooking { color:#FF6B35; border-color:#FF6B35; }
    .kb-col-header.ready { color:#66BB6A; border-color:#66BB6A; }
    .kb-card { background:#1E1E1E; border-radius:10px; overflow:hidden; margin-bottom:12px; border:1px solid #333; }
    .kb-card-head { padding:10px 14px; display:flex; justify-content:space-between; align-items:center; }
    .kb-card-head.pending { background:#2A2200; }
    .kb-card-head.accepted { background:#001A33; }
    .kb-card-head.cooking { background:#2A1200; }
    .kb-card-head.ready { background:#0A2200; }
    .kb-order-id { color:white; font-weight:700; font-size:0.95rem; }
    .kb-timer { font-size:0.75rem; padding:3px 8px; border-radius:20px; font-weight:600; }
    .kb-items { padding:10px 14px; }
    .kb-item { color:rgba(255,255,255,0.8); font-size:0.85rem; padding:5px 0; border-bottom:1px solid #2A2A2A; display:flex; justify-content:space-between; }
    .kb-item:last-child { border-bottom:none; }
    .kb-item-qty { background:#333; color:var(--accent); width:22px; height:22px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0; }
    .kb-footer { padding:10px 14px; background:#161616; display:flex; gap:6px; }
    .kb-btn { flex:1; padding:7px; border:none; border-radius:6px; font-family:var(--font-body); font-size:0.8rem; font-weight:600; cursor:pointer; transition:all 0.15s; }
    .kb-btn.accept { background:#1565C0; color:white; }
    .kb-btn.cook { background:#E65100; color:white; }
    .kb-btn.done { background:#2E7D32; color:white; }
    .kb-btn.deliver { background:#4A148C; color:white; }
    .kb-btn:hover { filter:brightness(1.2); }
    .empty-col { color:rgba(255,255,255,0.2); text-align:center; padding:30px 0; font-size:0.85rem; }
    @media(max-width:900px){.kb-cols{grid-template-columns:1fr 1fr;}}
    @media(max-width:560px){.kb-cols{grid-template-columns:1fr;}}
  </style>
</head>
<body>
<div class="kitchen-topbar">
  <h1>🍳 Kitchen Display Board</h1>
  <div style="display:flex;align-items:center;gap:20px">
    <div class="time" id="kitchenClock"></div>
    <a href="orders.php" style="color:rgba(255,255,255,0.5);font-size:0.82rem">Full Orders →</a>
  </div>
</div>

<div class="kitchen-main">
  <div class="kb-cols">
    <?php
    $cols = [
      'pending'  => ['label'=>'⏳ New Orders',   'next'=>'accepted', 'nextLabel'=>'Accept'],
      'accepted' => ['label'=>'✅ Accepted',      'next'=>'cooking',  'nextLabel'=>'Start Cooking'],
      'cooking'  => ['label'=>'🍳 Cooking',       'next'=>'ready',    'nextLabel'=>'Mark Ready'],
      'ready'    => ['label'=>'🔔 Ready to Serve','next'=>'delivered','nextLabel'=>'Delivered'],
    ];
    foreach($cols as $status => $col):
      $colOrders = array_filter($ordersArr, fn($o) => $o['status'] === $status);
    ?>
    <div>
      <div class="kb-col-header <?php echo $status; ?>"><?php echo $col['label']; ?> (<?php echo count($colOrders); ?>)</div>
      <?php if(empty($colOrders)): ?>
        <div class="empty-col">No orders</div>
      <?php endif; ?>
      <?php foreach($colOrders as $order):
        $oid = str_pad($order['id'],4,'0',STR_PAD_LEFT);
        $items = $db->query("SELECT oi.quantity, mi.name FROM order_items oi JOIN menu_items mi ON oi.menu_item_id=mi.id WHERE oi.order_id={$order['id']}");
        $mins = round((time() - strtotime($order['created_at'])) / 60);
        $timerColor = $mins > 20 ? '#f44336' : ($mins > 10 ? '#FF9800' : '#66BB6A');
      ?>
      <div class="kb-card">
        <div class="kb-card-head <?php echo $status; ?>">
          <div>
            <div class="kb-order-id">#<?php echo $oid; ?></div>
            <div style="font-size:0.75rem;color:rgba(255,255,255,0.4)"><?php echo sanitize($order['cname']??'Guest'); ?><?php if($order['table_number']): ?> · T<?php echo $order['table_number']; ?><?php endif; ?></div>
          </div>
          <div class="kb-timer" style="background:<?php echo $timerColor; ?>20;color:<?php echo $timerColor; ?>"><?php echo $mins; ?>m ago</div>
        </div>
        <div class="kb-items">
          <?php while($item=$items->fetch_assoc()): ?>
          <div class="kb-item">
            <span><?php echo sanitize($item['name']); ?></span>
            <span class="kb-item-qty"><?php echo $item['quantity']; ?></span>
          </div>
          <?php endwhile; ?>
          <?php if($order['special_instructions']): ?>
          <div style="font-size:0.75rem;color:#FFB300;margin-top:6px">📝 <?php echo sanitize($order['special_instructions']); ?></div>
          <?php endif; ?>
        </div>
        <div class="kb-footer">
          <form method="POST" style="display:contents">
            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
            <input type="hidden" name="new_status" value="<?php echo $col['next']; ?>">
            <input type="hidden" name="update_status" value="1">
            <button type="submit" class="kb-btn <?php echo ['accepted'=>'accept','cooking'=>'cook','ready'=>'done','delivered'=>'deliver'][$col['next']]??'accept'; ?>">
              <?php echo $col['nextLabel']; ?> →
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
function updateClock() {
  const now = new Date();
  document.getElementById('kitchenClock').textContent = now.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
updateClock(); setInterval(updateClock, 1000);
// Auto-refresh every 15 seconds
setTimeout(() => location.reload(), 15000);
</script>
</body>
</html>
