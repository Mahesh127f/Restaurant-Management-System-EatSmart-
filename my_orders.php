<?php
$pageTitle = 'My Orders';
require_once 'includes/header.php';
requireLogin();
$db = getDB();

$orders = $db->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
$orders->bind_param('i', $_SESSION['user_id']);
$orders->execute();
$ordersArr = []; $res = $orders->get_result();
while($o=$res->fetch_assoc()) $ordersArr[] = $o;

$statusSteps = ['pending'=>0,'accepted'=>1,'cooking'=>2,'ready'=>3,'delivered'=>4];
$statusEmoji = ['pending'=>'⏳','accepted'=>'✅','cooking'=>'🍳','ready'=>'🔔','delivered'=>'🎉','cancelled'=>'❌'];
$stepLabels = ['Received','Accepted','Cooking','Ready','Delivered'];
$stepIcons = ['📋','✅','🍳','🔔','🎉'];
?>

<section class="section" style="padding-top:20px">
  <div class="container">
    <div class="section-title">
      <h2>📦 My Orders</h2>
      <p>Track your order status in real-time</p>
    </div>

    <?php if(empty($ordersArr)): ?>
    <div class="empty-state">
      <div class="icon">🛒</div>
      <h3 style="margin-bottom:8px">No orders yet</h3>
      <p>Browse our menu and place your first order!</p>
      <a href="menu.php" class="btn btn-primary mt-3">Browse Menu</a>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:20px">
      <?php foreach($ordersArr as $order):
        $stepIdx = $statusSteps[$order['status']] ?? 0;
        $isActive = !in_array($order['status'], ['delivered','cancelled']);
        $oid = str_pad($order['id'],4,'0',STR_PAD_LEFT);
        
        // Get order items
        $ois = $db->prepare("SELECT oi.*, mi.name FROM order_items oi JOIN menu_items mi ON oi.menu_item_id=mi.id WHERE oi.order_id=?");
        $ois->bind_param('i',$order['id']); $ois->execute();
        $orderItems = $ois->get_result()->fetch_all(MYSQLI_ASSOC);
      ?>
      <div class="card <?php echo $isActive?'':''; ?>" id="order_<?php echo $order['id']; ?>">
        <div class="card-header">
          <div class="flex-between">
            <div>
              <strong>Order #<?php echo $oid; ?></strong>
              <span class="badge badge-<?php echo $order['status']==='delivered'?'success':($order['status']==='cancelled'?'danger':'warning'); ?> ml-2" style="margin-left:10px">
                <?php echo ($statusEmoji[$order['status']]??'').' '.ucfirst($order['status']); ?>
              </span>
              <?php if($isActive): ?>
              <span class="badge badge-info" style="margin-left:6px;font-size:0.7rem;animation:pulse 2s infinite">🔴 Live</span>
              <?php endif; ?>
            </div>
            <div style="text-align:right">
              <div style="font-weight:700;color:var(--primary)">₹<?php echo number_format($order['total_amount'],0); ?></div>
              <div style="font-size:0.78rem;color:var(--text3)"><?php echo date('d M Y, h:i A',strtotime($order['created_at'])); ?></div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <!-- Status Tracker -->
          <?php if($order['status'] !== 'cancelled'): ?>
          <div class="order-tracker mb-3">
            <?php for($i=0;$i<5;$i++): ?>
            <div class="tracker-step <?php echo $i<$stepIdx?'done':($i===$stepIdx?'active':''); ?>">
              <div class="tracker-icon"><?php echo $stepIcons[$i]; ?></div>
              <div class="tracker-label"><?php echo $stepLabels[$i]; ?></div>
            </div>
            <?php endfor; ?>
          </div>
          <?php if($isActive): ?>
          <div style="text-align:center;font-size:0.85rem;color:var(--primary);margin-bottom:16px">
            🔄 Status updates automatically every 8 seconds
          </div>
          <?php endif; ?>
          <?php endif; ?>

          <!-- Order Items -->
          <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px">
            <?php foreach($orderItems as $oi): ?>
            <span style="background:var(--surface2);padding:4px 10px;border-radius:20px;font-size:0.82rem">
              <?php echo sanitize($oi['name']); ?> ×<?php echo $oi['quantity']; ?>
            </span>
            <?php endforeach; ?>
          </div>

          <div style="display:flex;gap:16px;font-size:0.82rem;color:var(--text3);flex-wrap:wrap">
            <span>📋 <?php echo ucfirst(str_replace('-',' ',$order['order_type'])); ?></span>
            <?php if($order['table_number']): ?>
            <span>🪑 Table <?php echo $order['table_number']; ?></span>
            <?php endif; ?>
            <?php if($order['special_instructions']): ?>
            <span>📝 <?php echo sanitize($order['special_instructions']); ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.5} }
</style>
<script>
// Auto-refresh active orders
const activeOrders = [<?php echo implode(',', array_map(fn($o)=>$o['id'], array_filter($ordersArr, fn($o)=>!in_array($o['status'],['delivered','cancelled'])))); ?>];

if(activeOrders.length > 0) {
  setInterval(async () => {
    for(const id of activeOrders) {
      try {
        const r = await fetch('php/get_order_status.php?id=' + id);
        const d = await r.json();
        if(d.status) updateTrackerUI(id, d.status);
      } catch(e){}
    }
  }, 8000);
}

function updateTrackerUI(orderId, status) {
  const steps = ['pending','accepted','cooking','ready','delivered'];
  const idx = steps.indexOf(status);
  const card = document.getElementById('order_' + orderId);
  if(!card) return;
  card.querySelectorAll('.tracker-step').forEach((el,i) => {
    el.classList.remove('active','done');
    if(i < idx) el.classList.add('done');
    else if(i === idx) el.classList.add('active');
  });
}
</script>

<?php require_once 'includes/footer.php'; ?>
