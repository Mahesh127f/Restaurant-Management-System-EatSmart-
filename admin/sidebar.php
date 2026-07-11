<?php
$adminPage = basename($_SERVER['PHP_SELF'], '.php');
$pendingCount = 0;
try {
  $db2 = getDB();
  $pc = $db2->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'");
  $pendingCount = $pc ? $pc->fetch_assoc()['c'] : 0;
} catch(Exception $e) {}
?>
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-brand">
    EatSmart
    <small>Admin Panel</small>
  </div>
  <nav class="sidebar-nav">
    <div class="sidebar-section">Main</div>
    <a href="index.php" class="<?php echo $adminPage==='index'?'active':''; ?>">
      <span class="icon">📊</span> Dashboard
    </a>

    <div class="sidebar-section">Orders</div>
    <a href="orders.php" class="<?php echo $adminPage==='orders'?'active':''; ?>">
      <span class="icon">📦</span> All Orders
      <?php if($pendingCount>0): ?><span class="badge-pill"><?php echo $pendingCount; ?></span><?php endif; ?>
    </a>
    <a href="kitchen.php" class="<?php echo $adminPage==='kitchen'?'active':''; ?>">
      <span class="icon">🍳</span> Kitchen Board
    </a>

    <div class="sidebar-section">Restaurant</div>
    <a href="menu.php" class="<?php echo $adminPage==='menu'?'active':''; ?>">
      <span class="icon">🍛</span> Menu Manager
    </a>
    <a href="reservations.php" class="<?php echo $adminPage==='reservations'?'active':''; ?>">
      <span class="icon">🪑</span> Reservations
    </a>
    <a href="waste_deals.php" class="<?php echo $adminPage==='waste_deals'?'active':''; ?>">
      <span class="icon">🌱</span> Waste Deals
    </a>

    <div class="sidebar-section">Analytics</div>
    <a href="reports.php" class="<?php echo $adminPage==='reports'?'active':''; ?>">
      <span class="icon">📈</span> Sales Report
    </a>
    <a href="customers.php" class="<?php echo $adminPage==='customers'?'active':''; ?>">
      <span class="icon">👥</span> Customers
    </a>

    <div class="sidebar-section">System</div>
    <a href="../index.php" target="_blank">
      <span class="icon">🌐</span> View Website
    </a>
    <a href="../php/auth.php?action=logout">
      <span class="icon">🚪</span> Logout
    </a>
  </nav>
  <div class="sidebar-footer">
    <strong><?php echo sanitize($_SESSION['name']); ?></strong>
    <?php echo ucfirst($_SESSION['role']); ?>
  </div>
</aside>
