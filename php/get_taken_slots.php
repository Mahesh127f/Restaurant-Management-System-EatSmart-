<?php
require_once __DIR__ . '/config.php';
$date = sanitize($_GET['date'] ?? date('Y-m-d'));
$db = getDB();
$stmt = $db->prepare("SELECT table_id, time_slot FROM reservations WHERE reservation_date=? AND status != 'cancelled'");
$stmt->bind_param('s', $date);
$stmt->execute();
$taken = [];
$res = $stmt->get_result();
while($r=$res->fetch_assoc()) $taken[$r['table_id']][] = $r['time_slot'];
jsonResponse($taken);
?>
