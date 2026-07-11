<?php
$pageTitle = 'Reservations';
require_once '../php/config.php';
requireAdmin();
$db = getDB();
$msg = '';

if(isset($_GET['status']) && isset($_GET['id'])) {
  $rid = (int)$_GET['id'];
  $status = sanitize($_GET['status']);
  if(in_array($status, ['confirmed','cancelled','completed'])) {
    $db->query("UPDATE reservations SET status='$status' WHERE id=$rid");
    $msg = 'Reservation updated.';
  }
}

$filter = sanitize($_GET['filter'] ?? 'upcoming');
$where = match($filter) {
  'upcoming' => "WHERE r.reservation_date >= CURDATE() AND r.status != 'cancelled'",
  'today' => "WHERE r.reservation_date = CURDATE()",
  'past' => "WHERE r.reservation_date < CURDATE()",
  'cancelled' => "WHERE r.status = 'cancelled'",
  default => ''
};
$reservations = $db->query("SELECT r.*, u.name as cname, u.phone, rt.table_number, rt.location, rt.capacity FROM reservations r LEFT JOIN users u ON r.user_id=u.id LEFT JOIN restaurant_tables rt ON r.table_id=rt.id $where ORDER BY r.reservation_date, r.time_slot LIMIT 50");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Reservations — EatSmart Admin</title>
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-topbar">
    <h1>🪑 Reservations</h1>
  </div>
  <div class="admin-content">
    <?php if($msg): ?><div class="alert alert-success">✅ <?php echo $msg; ?></div><?php endif; ?>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
      <?php foreach(['upcoming','today','past','cancelled','all'] as $f): ?>
      <a href="?filter=<?php echo $f; ?>" class="btn <?php echo $filter===$f?'btn-primary':'btn-secondary'; ?> btn-sm"><?php echo ucfirst($f); ?></a>
      <?php endforeach; ?>
    </div>

    <div class="data-card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Customer</th><th>Date</th><th>Time</th><th>Table</th><th>Guests</th><th>Status</th><th>Notes</th><th>Action</th></tr></thead>
          <tbody>
          <?php while($r=$reservations->fetch_assoc()): ?>
          <tr>
            <td>#<?php echo $r['id']; ?></td>
            <td>
              <strong><?php echo sanitize($r['cname']??'Guest'); ?></strong>
              <?php if($r['phone']): ?><br><span style="font-size:0.78rem;color:var(--text3)"><?php echo $r['phone']; ?></span><?php endif; ?>
            </td>
            <td><?php echo date('d M Y',strtotime($r['reservation_date'])); ?></td>
            <td><?php echo date('h:i A',strtotime($r['time_slot'])); ?></td>
            <td>T<?php echo $r['table_number']; ?><br><span style="font-size:0.78rem;color:var(--text3)"><?php echo $r['location']; ?></span></td>
            <td><?php echo $r['guests']; ?></td>
            <td>
              <span class="badge badge-<?php echo $r['status']==='confirmed'?'success':($r['status']==='cancelled'?'danger':'warning'); ?>">
                <?php echo ucfirst($r['status']); ?>
              </span>
            </td>
            <td style="font-size:0.8rem;color:var(--text3);max-width:120px"><?php echo sanitize($r['special_requests']??'—'); ?></td>
            <td style="white-space:nowrap">
              <?php if($r['status']==='pending'): ?>
              <a href="?id=<?php echo $r['id']; ?>&status=confirmed&filter=<?php echo $filter; ?>" class="btn btn-success btn-sm">Confirm</a>
              <?php endif; ?>
              <?php if($r['status']!=='cancelled' && $r['status']!=='completed'): ?>
              <a href="?id=<?php echo $r['id']; ?>&status=cancelled&filter=<?php echo $filter; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this reservation?')">Cancel</a>
              <?php endif; ?>
              <?php if($r['status']==='confirmed'): ?>
              <a href="?id=<?php echo $r['id']; ?>&status=completed&filter=<?php echo $filter; ?>" class="btn btn-secondary btn-sm">Done</a>
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
</body>
</html>
