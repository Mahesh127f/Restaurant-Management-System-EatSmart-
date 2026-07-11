<?php
$pageTitle = 'Dashboard';
require_once '../php/config.php';
requireAdmin();
$db = getDB();

// Stats
$todayOrders = $db->query("SELECT COUNT(*) as c FROM orders WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
$todayRevenue = $db->query("SELECT COALESCE(SUM(total_amount),0) as r FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled'")->fetch_assoc()['r'];
$totalUsers = $db->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$activeDeals = $db->query("SELECT COUNT(*) as c FROM waste_deals WHERE is_active=1 AND expires_at>NOW()")->fetch_assoc()['c'];
$pendingOrders = $db->query("SELECT COUNT(*) as c FROM orders WHERE status IN ('pending','accepted','cooking')")->fetch_assoc()['c'];
$pendingRes = $db->query("SELECT COUNT(*) as c FROM reservations WHERE status='pending' AND reservation_date>=CURDATE()")->fetch_assoc()['c'];

// Recent orders
$recentOrders = $db->query("SELECT o.*, u.name as cname FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 8");

// Top dishes AI
$topDishes = $db->query("SELECT mi.name, SUM(oi.quantity) as total FROM order_items oi JOIN menu_items mi ON oi.menu_item_id=mi.id GROUP BY oi.menu_item_id ORDER BY total DESC LIMIT 6");
$topArr = []; $maxQty = 1;
while($r=$topDishes->fetch_assoc()) { $topArr[]=$r; if($r['total']>$maxQty) $maxQty=$r['total']; }

// Weekly revenue
$weekRev = [];
for($i=6;$i>=0;$i--) {
  $date = date('Y-m-d', strtotime("-$i days"));
  $day = date('D', strtotime($date));
  $r = $db->query("SELECT COALESCE(SUM(total_amount),0) as rev FROM orders WHERE DATE(created_at)='$date' AND status!='cancelled'")->fetch_assoc()['rev'];
  $weekRev[] = ['day'=>$day,'rev'=>(float)$r];
}
$maxRev = max(array_column($weekRev,'rev')) ?: 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin Dashboard — EatSmart</title>
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="admin-main">
  <div class="admin-topbar">
    <h1>📊 Dashboard</h1>
    <div class="topbar-actions">
      <span style="font-size:0.82rem;color:var(--text3)">Welcome, <?php echo sanitize($_SESSION['name']); ?></span>
      <a href="orders.php" class="topbar-btn primary">+ New Order</a>
      <a href="../index.php" class="topbar-btn" target="_blank">View Site ↗</a>
    </div>
  </div>
  <div class="admin-content">

    <!-- Stat Cards -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon orange">📦</div>
          <?php if($pendingOrders>0): ?><span class="stat-change up">⚡ <?php echo $pendingOrders; ?> active</span><?php endif; ?>
        </div>
        <div class="stat-num" data-count="<?php echo $todayOrders; ?>"><?php echo $todayOrders; ?></div>
        <div class="stat-label">Orders Today</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon green">💰</div></div>
        <div class="stat-num">₹<?php echo number_format($todayRevenue,0); ?></div>
        <div class="stat-label">Revenue Today</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon blue">👥</div></div>
        <div class="stat-num" data-count="<?php echo $totalUsers; ?>"><?php echo $totalUsers; ?></div>
        <div class="stat-label">Registered Customers</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon yellow">🌱</div>
          <?php if($activeDeals>0): ?><span class="stat-change up"><?php echo $activeDeals; ?> live</span><?php endif; ?>
        </div>
        <div class="stat-num" data-count="<?php echo $activeDeals; ?>"><?php echo $activeDeals; ?></div>
        <div class="stat-label">Active Waste Deals</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon orange">🪑</div>
          <?php if($pendingRes>0): ?><span class="stat-change up"><?php echo $pendingRes; ?> pending</span><?php endif; ?>
        </div>
        <div class="stat-num" data-count="<?php echo $pendingRes; ?>"><?php echo $pendingRes; ?></div>
        <div class="stat-label">Upcoming Reservations</div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

      <!-- Weekly Revenue Chart -->
      <div class="data-card">
        <div class="data-card-header"><h3>📈 Revenue This Week</h3></div>
        <div class="data-card-body">
          <?php foreach($weekRev as $day): ?>
          <div class="chart-bar-row">
            <div class="chart-bar-label"><?php echo $day['day']; ?></div>
            <div class="chart-bar-track">
              <div class="chart-bar-fill" style="width:<?php echo $maxRev>0?round($day['rev']/$maxRev*100):0; ?>%"></div>
            </div>
            <div class="chart-bar-val">₹<?php echo number_format($day['rev'],0); ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Top Dishes -->
      <div class="data-card">
        <div class="data-card-header">
          <h3>🤖 AI — Top Dishes</h3>
          <span style="font-size:0.78rem;color:var(--text3)">By order count</span>
        </div>
        <div class="data-card-body">
          <?php foreach($topArr as $dish): ?>
          <div class="chart-bar-row">
            <div class="chart-bar-label"><?php echo sanitize($dish['name']); ?></div>
            <div class="chart-bar-track">
              <div class="chart-bar-fill" style="width:<?php echo round($dish['total']/$maxQty*100); ?>%;background:var(--accent)"></div>
            </div>
            <div class="chart-bar-val"><?php echo $dish['total']; ?> orders</div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="data-card" style="margin-top:20px">
      <div class="data-card-header">
        <h3>🕐 Recent Orders</h3>
        <a href="orders.php" class="topbar-btn">View All</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Customer</th><th>Type</th><th>Items</th><th>Amount</th><th>Status</th><th>Time</th><th>Action</th></tr>
          </thead>
          <tbody>
          <?php while($o=$recentOrders->fetch_assoc()):
            $oid = str_pad($o['id'],4,'0',STR_PAD_LEFT);
            $itemCount = $db->query("SELECT SUM(quantity) as c FROM order_items WHERE order_id={$o['id']}")->fetch_assoc()['c'];
          ?>
          <tr>
            <td><strong>#<?php echo $oid; ?></strong></td>
            <td><?php echo sanitize($o['cname'] ?? 'Guest'); ?></td>
            <td><span class="badge badge-info"><?php echo ucfirst(str_replace('-',' ',$o['order_type'])); ?></span></td>
            <td><?php echo $itemCount; ?> item<?php echo $itemCount!=1?'s':''; ?></td>
            <td><strong>₹<?php echo number_format($o['total_amount'],0); ?></strong></td>
            <td><span class="badge badge-<?php echo in_array($o['status'],['delivered'])? 'success':(in_array($o['status'],['cancelled'])?'danger':'warning'); ?>"><?php echo ucfirst($o['status']); ?></span></td>
            <td style="font-size:0.8rem;color:var(--text3)"><?php echo date('h:i A',strtotime($o['created_at'])); ?></td>
            <td>
              <a href="orders.php?update=<?php echo $o['id']; ?>" class="btn btn-secondary btn-sm">Update</a>
            </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- end admin-content -->
</div><!-- end admin-main -->

<script>
document.querySelectorAll('[data-count]').forEach(el => {
  const target = parseInt(el.dataset.count);
  let cur = 0; const step = Math.ceil(target/30);
  const t = setInterval(() => { cur=Math.min(cur+step,target); el.textContent=cur; if(cur>=target)clearInterval(t); }, 40);
});
// Auto-refresh every 30s
setTimeout(() => location.reload(), 30000);
</script>
</body>
</html>
