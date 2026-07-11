<?php
$pageTitle = 'Orders';
require_once '../php/config.php';
requireAdmin();
$db = getDB();
$msg = '';

// Update order status
if(isset($_GET['update']) && isset($_POST['status'])) {
  $oid = (int)$_GET['update'];
  $status = sanitize($_POST['status']);
  $valid = ['pending','accepted','cooking','ready','delivered','cancelled'];
  if(in_array($status, $valid)) {
    $db->query("UPDATE orders SET status='$status' WHERE id=$oid");
    $msg = "Order #" . str_pad($oid,4,'0',STR_PAD_LEFT) . " updated to " . ucfirst($status);
  }
}

// Filter
$filter = sanitize($_GET['filter'] ?? 'all');
$where = $filter === 'all' ? '' : "WHERE o.status = '$filter'";
$orders = $db->query("SELECT o.*, u.name as cname, u.phone FROM orders o LEFT JOIN users u ON o.user_id=u.id $where ORDER BY o.created_at DESC LIMIT 50");
$statusColors = ['pending'=>'warning','accepted'=>'info','cooking'=>'warning','ready'=>'success','delivered'=>'success','cancelled'=>'danger'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Orders — EatSmart Admin</title>
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-topbar">
    <h1>📦 Orders</h1>
    <div class="topbar-actions">
      <span style="font-size:0.82rem;color:var(--text3)">Auto-refreshes every 20s</span>
    </div>
  </div>
  <div class="admin-content">
    <?php if($msg): ?><div class="alert alert-success">✅ <?php echo $msg; ?></div><?php endif; ?>

    <!-- Filter Tabs -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
      <?php foreach(['all','pending','accepted','cooking','ready','delivered','cancelled'] as $f): ?>
      <a href="?filter=<?php echo $f; ?>" class="btn <?php echo $filter===$f?'btn-primary':'btn-secondary'; ?> btn-sm">
        <?php echo ucfirst($f); ?>
        <?php if($f==='pending'): ?>(<?php echo $db->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'")->fetch_assoc()['c']; ?>)<?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="data-card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Customer</th><th>Type</th><th>Amount</th><th>Status</th><th>Time</th><th>Items</th><th>Action</th></tr></thead>
          <tbody>
          <?php while($o=$orders->fetch_assoc()):
            $oid = str_pad($o['id'],4,'0',STR_PAD_LEFT);
            $items = $db->query("SELECT oi.quantity, mi.name FROM order_items oi JOIN menu_items mi ON oi.menu_item_id=mi.id WHERE oi.order_id={$o['id']}");
            $itemNames = []; while($i=$items->fetch_assoc()) $itemNames[] = $i['quantity'].'× '.$i['name'];
          ?>
          <tr id="row_<?php echo $o['id']; ?>">
            <td><strong>#<?php echo $oid; ?></strong></td>
            <td>
              <div style="font-weight:500"><?php echo sanitize($o['cname']??'Guest'); ?></div>
              <?php if($o['table_number']): ?><div style="font-size:0.78rem;color:var(--text3)">Table <?php echo $o['table_number']; ?></div><?php endif; ?>
            </td>
            <td><span class="badge badge-info" style="font-size:0.72rem"><?php echo ucfirst(str_replace('-',' ',$o['order_type'])); ?></span></td>
            <td><strong>₹<?php echo number_format($o['total_amount'],0); ?></strong></td>
            <td><span class="badge badge-<?php echo $statusColors[$o['status']]??'dark'; ?>"><?php echo ucfirst($o['status']); ?></span></td>
            <td style="font-size:0.8rem;color:var(--text3)"><?php echo date('d M, h:i A',strtotime($o['created_at'])); ?></td>
            <td style="font-size:0.8rem;max-width:160px;color:var(--text2)"><?php echo implode(', ',$itemNames); ?></td>
            <td>
              <button class="btn btn-secondary btn-sm" onclick="openUpdateModal(<?php echo $o['id']; ?>,'<?php echo $o['status']; ?>')">Update</button>
            </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Update Modal -->
<div class="modal-overlay" id="updateModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Update Order Status</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form method="POST" id="updateForm">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">New Status</label>
          <select name="status" class="form-control" id="statusSelect">
            <option value="pending">⏳ Pending</option>
            <option value="accepted">✅ Accepted</option>
            <option value="cooking">🍳 Cooking</option>
            <option value="ready">🔔 Ready</option>
            <option value="delivered">🎉 Delivered</option>
            <option value="cancelled">❌ Cancelled</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openUpdateModal(id, status) {
  document.getElementById('updateForm').action = 'orders.php?update=' + id;
  document.getElementById('statusSelect').value = status;
  document.getElementById('updateModal').classList.add('open');
}
function closeModal() { document.getElementById('updateModal').classList.remove('open'); }
setTimeout(() => location.reload(), 20000);
</script>
</body>
</html>
