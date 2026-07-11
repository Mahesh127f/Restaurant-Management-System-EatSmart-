<?php
$pageTitle = 'Customers';
require_once '../php/config.php';
requireAdmin();
$db = getDB();

$customers = $db->query("SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total_spent FROM users u LEFT JOIN orders o ON u.id=o.user_id AND o.status != 'cancelled' WHERE u.role='customer' GROUP BY u.id ORDER BY total_spent DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Customers — EatSmart Admin</title>
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-topbar">
    <h1>👥 Customers</h1>
  </div>
  <div class="admin-content">
    <div class="data-card">
      <div class="data-card-header">
        <h3>All Customers</h3>
        <input type="text" id="custSearch" class="form-control" style="width:200px" placeholder="Search..." oninput="filterCust(this.value)">
      </div>
      <div class="table-wrap">
        <table id="custTable">
          <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Loyalty Pts</th><th>Joined</th></tr></thead>
          <tbody>
          <?php while($c=$customers->fetch_assoc()): ?>
          <tr data-name="<?php echo strtolower($c['name'].' '.$c['email']); ?>">
            <td><?php echo $c['id']; ?></td>
            <td><strong><?php echo sanitize($c['name']); ?></strong></td>
            <td><?php echo sanitize($c['email']); ?></td>
            <td><?php echo sanitize($c['phone']??'—'); ?></td>
            <td><?php echo $c['order_count']; ?></td>
            <td><strong>₹<?php echo number_format($c['total_spent'],0); ?></strong></td>
            <td><span class="badge badge-success">🏆 <?php echo $c['loyalty_points']; ?></span></td>
            <td style="font-size:0.8rem;color:var(--text3)"><?php echo date('d M Y',strtotime($c['created_at'])); ?></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
function filterCust(q) {
  document.querySelectorAll('#custTable tbody tr').forEach(r => {
    r.style.display = r.dataset.name.includes(q.toLowerCase()) ? '' : 'none';
  });
}
</script>
</body>
</html>
