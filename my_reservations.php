<?php
$pageTitle = 'My Reservations';
require_once 'includes/header.php';
requireLogin();
$db = getDB();

if(isset($_GET['cancel'])) {
  $rid = (int)$_GET['cancel'];
  $db->prepare("UPDATE reservations SET status='cancelled' WHERE id=? AND user_id=?")->execute() ?: null;
  $stmt = $db->prepare("UPDATE reservations SET status='cancelled' WHERE id=? AND user_id=?");
  $stmt->bind_param('ii', $rid, $_SESSION['user_id']);
  $stmt->execute();
  header('Location: my_reservations.php?msg=cancelled'); exit;
}

$reservations = $db->prepare("SELECT r.*, rt.table_number, rt.location, rt.capacity FROM reservations r JOIN restaurant_tables rt ON r.table_id=rt.id WHERE r.user_id=? ORDER BY r.reservation_date DESC, r.time_slot DESC");
$reservations->bind_param('i', $_SESSION['user_id']);
$reservations->execute();
$resArr = $reservations->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<section class="section" style="padding-top:20px">
  <div class="container">
    <div class="flex-between mb-3">
      <h2>🪑 My Reservations</h2>
      <a href="reservation.php" class="btn btn-primary">+ New Reservation</a>
    </div>
    <?php if(isset($_GET['msg']) && $_GET['msg']==='cancelled'): ?>
    <div class="alert alert-success">Reservation cancelled successfully.</div>
    <?php endif; ?>
    <?php if(empty($resArr)): ?>
    <div class="empty-state">
      <div class="icon">🪑</div>
      <h3>No reservations yet</h3>
      <p>Book a table for your next visit!</p>
      <a href="reservation.php" class="btn btn-primary mt-3">Reserve a Table</a>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:16px">
      <?php foreach($resArr as $res):
        $isPast = strtotime($res['reservation_date']) < strtotime('today');
        $canCancel = !$isPast && $res['status'] === 'confirmed';
      ?>
      <div class="card">
        <div class="card-body">
          <div class="flex-between">
            <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap">
              <div style="font-size:2rem">🪑</div>
              <div>
                <h3 style="margin-bottom:4px">Table <?php echo $res['table_number']; ?> — <?php echo $res['location']; ?></h3>
                <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:0.88rem;color:var(--text2)">
                  <span>📅 <?php echo date('d M Y', strtotime($res['reservation_date'])); ?></span>
                  <span>⏰ <?php echo date('h:i A', strtotime($res['time_slot'])); ?></span>
                  <span>👥 <?php echo $res['guests']; ?> guests</span>
                  <span>🏠 Up to <?php echo $res['capacity']; ?> capacity</span>
                </div>
                <?php if($res['special_requests']): ?>
                <div style="font-size:0.82rem;color:var(--text3);margin-top:6px">📝 <?php echo sanitize($res['special_requests']); ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div style="text-align:right">
              <span class="badge badge-<?php echo $res['status']==='confirmed'?'success':($res['status']==='cancelled'?'danger':'warning'); ?> mb-2" style="display:block;margin-bottom:8px">
                <?php echo ucfirst($res['status']); ?>
              </span>
              <?php if($canCancel): ?>
              <a href="my_reservations.php?cancel=<?php echo $res['id']; ?>" class="btn btn-outline btn-sm" style="color:var(--danger);border-color:var(--danger)" onclick="return confirm('Cancel this reservation?')">Cancel</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require_once 'includes/footer.php'; ?>
