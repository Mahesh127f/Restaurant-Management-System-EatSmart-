<?php
$pageTitle = 'Reserve a Table';
require_once 'includes/header.php';
$db = getDB();

$success = $error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  if(!isLoggedIn()) { header('Location: login.php'); exit; }
  $date = sanitize($_POST['date'] ?? '');
  $slot = sanitize($_POST['time_slot'] ?? '');
  $guests = (int)($_POST['guests'] ?? 2);
  $tableId = (int)($_POST['table_id'] ?? 0);
  $notes = sanitize($_POST['special_requests'] ?? '');

  if(!$date || !$slot || !$tableId) {
    $error = 'Please fill in all required fields.';
  } else {
    // Check conflict
    $check = $db->prepare("SELECT id FROM reservations WHERE table_id=? AND reservation_date=? AND time_slot=? AND status != 'cancelled'");
    $check->bind_param('iss', $tableId, $date, $slot);
    $check->execute();
    if($check->get_result()->num_rows > 0) {
      $error = 'This table and time slot is already booked. Please choose another.';
    } else {
      $stmt = $db->prepare("INSERT INTO reservations (user_id, table_id, reservation_date, time_slot, guests, special_requests, status) VALUES (?,?,?,?,?,?,'confirmed')");
      $stmt->bind_param('iissis', $_SESSION['user_id'], $tableId, $date, $slot, $guests, $notes);
      if($stmt->execute()) {
        $success = '🎉 Your table has been reserved! See you at ' . $slot . ' on ' . date('d M Y', strtotime($date)) . '.';
      } else {
        $error = 'Something went wrong. Please try again.';
      }
    }
  }
}

$tables = $db->query("SELECT * FROM restaurant_tables WHERE is_active=1 ORDER BY capacity");
$timeSlots = ['11:00','11:30','12:00','12:30','13:00','13:30','14:00','18:00','18:30','19:00','19:30','20:00','20:30','21:00','21:30','22:00'];

// Get taken slots for today if date selected
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$takenSlots = [];
$tq = $db->prepare("SELECT table_id, time_slot FROM reservations WHERE reservation_date=? AND status != 'cancelled'");
$tq->bind_param('s', $selectedDate);
$tq->execute();
$tr = $tq->get_result();
while($r=$tr->fetch_assoc()) $takenSlots[$r['table_id']][] = $r['time_slot'];
?>

<section class="section" style="padding-top:20px">
  <div class="container" style="max-width:900px">
    <div class="section-title">
      <h2>🪑 Reserve a Table</h2>
      <p>Book your spot in advance and avoid the wait</p>
    </div>

    <?php if($success): ?><div class="alert alert-success">✅ <?php echo $success; ?> <a href="my_reservations.php">View reservations →</a></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger">⚠️ <?php echo $error; ?></div><?php endif; ?>

    <?php if(!isLoggedIn()): ?>
    <div class="alert alert-info">ℹ️ Please <a href="login.php">login</a> to make a reservation.</div>
    <?php endif; ?>

    <form method="POST" id="reservationForm">
      <div class="grid grid-2">
        <div class="card">
          <div class="card-header"><h3>📅 Choose Date & Time</h3></div>
          <div class="card-body">
            <div class="form-group">
              <label class="form-label">Date *</label>
              <input type="date" name="date" id="resDate" class="form-control" required
                min="<?php echo date('Y-m-d'); ?>"
                max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"
                value="<?php echo $selectedDate; ?>"
                onchange="loadSlots(this.value)">
            </div>
            <div class="form-group">
              <label class="form-label">Number of Guests *</label>
              <select name="guests" class="form-control" id="guestsSelect" onchange="filterTables()">
                <?php for($g=1;$g<=8;$g++): ?>
                <option value="<?php echo $g; ?>" <?php echo $g==2?'selected':''; ?>><?php echo $g; ?> Guest<?php echo $g>1?'s':''; ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Time Slot *</label>
              <input type="hidden" name="time_slot" id="selectedSlot" required>
              <div class="time-slots" id="timeSlotGrid">
                <?php foreach($timeSlots as $slot): ?>
                <div class="time-slot" data-slot="<?php echo $slot; ?>" onclick="selectSlot(this)">
                  <?php echo date('h:i A', strtotime($slot)); ?>
                </div>
                <?php endforeach; ?>
              </div>
              <p class="form-hint">Grey slots are already taken</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3>🪑 Choose Table</h3></div>
          <div class="card-body">
            <div id="tableGrid" style="display:flex;flex-direction:column;gap:10px">
              <?php while($table = $tables->fetch_assoc()): ?>
              <label class="table-option" style="display:flex;align-items:center;gap:12px;padding:14px;border:1.5px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;transition:all 0.2s" data-capacity="<?php echo $table['capacity']; ?>">
                <input type="radio" name="table_id" value="<?php echo $table['id']; ?>" style="accent-color:var(--primary)" required>
                <span style="font-size:1.4rem">🪑</span>
                <div>
                  <div style="font-weight:600">Table <?php echo $table['table_number']; ?></div>
                  <div style="font-size:0.82rem;color:var(--text3)"><?php echo $table['location']; ?> · Up to <?php echo $table['capacity']; ?> guests</div>
                </div>
                <span class="badge badge-success" style="margin-left:auto">Available</span>
              </label>
              <?php endwhile; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-body">
          <div class="form-group mb-0">
            <label class="form-label">Special Requests (optional)</label>
            <textarea name="special_requests" class="form-control" rows="2" placeholder="Birthday celebration, dietary requirements, preferred seating..."></textarea>
          </div>
        </div>
      </div>

      <div class="text-center mt-3">
        <button type="submit" class="btn btn-primary btn-lg" <?php echo !isLoggedIn()?'disabled':''; ?>>
          🪑 Confirm Reservation
        </button>
        <?php if(!isLoggedIn()): ?><p class="form-hint text-center mt-1">Login required to reserve</p><?php endif; ?>
      </div>
    </form>

    <!-- My Reservations Quick View -->
    <?php if(isLoggedIn()):
    $myRes = $db->prepare("SELECT r.*, rt.table_number, rt.location FROM reservations r JOIN restaurant_tables rt ON r.table_id=rt.id WHERE r.user_id=? AND r.reservation_date >= CURDATE() ORDER BY r.reservation_date, r.time_slot LIMIT 5");
    $myRes->bind_param('i', $_SESSION['user_id']);
    $myRes->execute();
    $upcoming = $myRes->get_result();
    if($upcoming->num_rows > 0):
    ?>
    <div class="card mt-4">
      <div class="card-header"><h3>📋 Your Upcoming Reservations</h3></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Date</th><th>Time</th><th>Table</th><th>Guests</th><th>Status</th></tr></thead>
          <tbody>
          <?php while($res=$upcoming->fetch_assoc()): ?>
          <tr>
            <td><?php echo date('d M Y', strtotime($res['reservation_date'])); ?></td>
            <td><?php echo date('h:i A', strtotime($res['time_slot'])); ?></td>
            <td>Table <?php echo $res['table_number']; ?> (<?php echo $res['location']; ?>)</td>
            <td><?php echo $res['guests']; ?> guests</td>
            <td><span class="badge badge-<?php echo $res['status']==='confirmed'?'success':'warning'; ?>"><?php echo ucfirst($res['status']); ?></span></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; endif; ?>
  </div>
</section>

<script>
const takenSlots = <?php echo json_encode($takenSlots); ?>;

function loadSlots(date) {
  fetch('php/get_taken_slots.php?date=' + date)
    .then(r => r.json()).then(data => {
      document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('taken','selected'));
      document.getElementById('selectedSlot').value = '';
    });
}

function filterTables() {
  const guests = parseInt(document.getElementById('guestsSelect').value);
  document.querySelectorAll('.table-option').forEach(t => {
    const cap = parseInt(t.dataset.capacity);
    t.style.display = cap >= guests ? '' : 'none';
    if(cap < guests) t.querySelector('input').checked = false;
  });
}

document.querySelectorAll('.table-option').forEach(t => {
  t.addEventListener('click', () => {
    document.querySelectorAll('.table-option').forEach(x => x.style.border = '1.5px solid var(--border)');
    t.style.border = '1.5px solid var(--primary)';
  });
});

document.getElementById('reservationForm')?.addEventListener('submit', e => {
  if(!document.getElementById('selectedSlot').value) {
    e.preventDefault(); alert('Please select a time slot.');
  }
});
</script>

<?php require_once 'includes/footer.php'; ?>
