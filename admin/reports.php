<?php
$pageTitle = 'Sales Report';
require_once '../php/config.php';
requireAdmin();
$db = getDB();

// Date range filter
$from = sanitize($_GET['from'] ?? date('Y-m-d', strtotime('-30 days')));
$to = sanitize($_GET['to'] ?? date('Y-m-d'));

// Summary
$summary = $db->query("SELECT COUNT(*) as total_orders, COALESCE(SUM(total_amount),0) as total_revenue, COALESCE(AVG(total_amount),0) as avg_order, COALESCE(SUM(discount_amount),0) as total_discount FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status != 'cancelled'")->fetch_assoc();

// Daily revenue
$dailyRev = $db->query("SELECT DATE(created_at) as day, COUNT(*) as cnt, SUM(total_amount) as rev FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status != 'cancelled' GROUP BY DATE(created_at) ORDER BY day DESC LIMIT 14");

// Top items
$topItems = $db->query("SELECT mi.name, SUM(oi.quantity) as qty, SUM(oi.quantity * oi.unit_price) as revenue FROM order_items oi JOIN menu_items mi ON oi.menu_item_id=mi.id JOIN orders o ON oi.order_id=o.id WHERE DATE(o.created_at) BETWEEN '$from' AND '$to' AND o.status != 'cancelled' GROUP BY oi.menu_item_id ORDER BY qty DESC LIMIT 10");
$topArr = []; $maxQty = 1;
while($r=$topItems->fetch_assoc()) { $topArr[]=$r; if($r['qty']>$maxQty) $maxQty=$r['qty']; }

// AI Demand Prediction: which dishes sell most by day of week
$dayPred = $db->query("SELECT mi.name, DAYNAME(o.created_at) as dow, SUM(oi.quantity) as qty FROM order_items oi JOIN menu_items mi ON oi.menu_item_id=mi.id JOIN orders o ON oi.order_id=o.id WHERE o.status != 'cancelled' GROUP BY oi.menu_item_id, DAYNAME(o.created_at) ORDER BY qty DESC");
$today = date('l'); // e.g. 'Wednesday'
$todayPreds = [];
while($r=$dayPred->fetch_assoc()) {
  if($r['dow'] === $today) $todayPreds[] = $r;
}
usort($todayPreds, fn($a,$b) => $b['qty'] - $a['qty']);
$todayPreds = array_slice($todayPreds, 0, 5);

// Order type breakdown
$typeBreak = $db->query("SELECT order_type, COUNT(*) as cnt FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to' AND status != 'cancelled' GROUP BY order_type");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Sales Report — EatSmart Admin</title>
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-topbar">
    <h1>📈 Sales Report</h1>
    <div class="topbar-actions">
      <form method="GET" style="display:flex;gap:8px;align-items:center">
        <input type="date" name="from" value="<?php echo $from; ?>" class="form-control" style="width:140px">
        <span style="color:var(--text3)">to</span>
        <input type="date" name="to" value="<?php echo $to; ?>" class="form-control" style="width:140px">
        <button type="submit" class="topbar-btn primary">Filter</button>
      </form>
    </div>
  </div>
  <div class="admin-content">

    <!-- Summary Stats -->
    <div class="stat-grid" style="grid-template-columns:repeat(4,1fr)">
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon orange">📦</div></div>
        <div class="stat-num"><?php echo number_format($summary['total_orders']); ?></div>
        <div class="stat-label">Total Orders</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon green">💰</div></div>
        <div class="stat-num">₹<?php echo number_format($summary['total_revenue'],0); ?></div>
        <div class="stat-label">Total Revenue</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon blue">📊</div></div>
        <div class="stat-num">₹<?php echo number_format($summary['avg_order'],0); ?></div>
        <div class="stat-label">Avg Order Value</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-top"><div class="stat-icon yellow">🌱</div></div>
        <div class="stat-num">₹<?php echo number_format($summary['total_discount'],0); ?></div>
        <div class="stat-label">Discounts Given</div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

      <!-- Top Selling Items -->
      <div class="data-card">
        <div class="data-card-header"><h3>🏆 Top Selling Items</h3></div>
        <div class="data-card-body">
          <?php foreach($topArr as $item): ?>
          <div class="chart-bar-row">
            <div class="chart-bar-label"><?php echo sanitize($item['name']); ?></div>
            <div class="chart-bar-track">
              <div class="chart-bar-fill" style="width:<?php echo round($item['qty']/$maxQty*100); ?>%"></div>
            </div>
            <div class="chart-bar-val"><?php echo $item['qty']; ?> sold</div>
          </div>
          <?php endforeach; ?>
          <?php if(empty($topArr)): ?><p style="color:var(--text3);font-size:0.88rem">No data in range.</p><?php endif; ?>
        </div>
      </div>

      <!-- AI Demand Prediction -->
      <div class="data-card">
        <div class="data-card-header">
          <h3>🤖 AI — Today's Demand Forecast</h3>
          <span style="font-size:0.78rem;color:var(--text3)"><?php echo $today; ?></span>
        </div>
        <div class="data-card-body">
          <?php if(!empty($todayPreds)): ?>
          <p style="font-size:0.82rem;color:var(--text3);margin-bottom:12px">Based on historical orders on <?php echo $today; ?>s:</p>
          <?php $maxP = $todayPreds[0]['qty'] ?: 1; foreach($todayPreds as $i=>$pred): ?>
          <div class="chart-bar-row">
            <div class="chart-bar-label"><?php echo sanitize($pred['name']); ?></div>
            <div class="chart-bar-track">
              <div class="chart-bar-fill" style="width:<?php echo round($pred['qty']/$maxP*100); ?>%;background:var(--accent)"></div>
            </div>
            <div class="chart-bar-val" style="color:var(--warning)"><?php echo $pred['qty']; ?> exp.</div>
          </div>
          <?php endforeach; ?>
          <div class="alert alert-info" style="margin-top:12px;font-size:0.82rem">
            💡 Prepare more of the top items above for today.
          </div>
          <?php else: ?>
          <div class="empty-state" style="padding:20px">
            <p>Not enough historical data yet. Predictions improve as orders come in.</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Daily Revenue Table -->
    <div class="data-card" style="margin-top:20px">
      <div class="data-card-header"><h3>📅 Daily Revenue Breakdown</h3></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Date</th><th>Day</th><th>Orders</th><th>Revenue</th><th>Avg/Order</th></tr></thead>
          <tbody>
          <?php while($day=$dailyRev->fetch_assoc()): ?>
          <tr>
            <td><?php echo date('d M Y',strtotime($day['day'])); ?></td>
            <td><?php echo date('l',strtotime($day['day'])); ?></td>
            <td><?php echo $day['cnt']; ?></td>
            <td><strong>₹<?php echo number_format($day['rev'],0); ?></strong></td>
            <td>₹<?php echo $day['cnt']>0?number_format($day['rev']/$day['cnt'],0):'0'; ?></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Order Type Breakdown -->
    <div class="data-card" style="margin-top:20px">
      <div class="data-card-header"><h3>📊 Order Type Breakdown</h3></div>
      <div class="data-card-body" style="display:flex;gap:20px;flex-wrap:wrap">
        <?php while($t=$typeBreak->fetch_assoc()): ?>
        <div style="text-align:center;padding:16px 24px;background:var(--surface);border-radius:var(--radius-sm)">
          <div style="font-size:1.6rem;font-weight:700;color:var(--primary)"><?php echo $t['cnt']; ?></div>
          <div style="font-size:0.85rem;color:var(--text3)"><?php echo ucfirst(str_replace('-',' ',$t['order_type'])); ?></div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>

  </div>
</div>
</body>
</html>
