<?php
require_once __DIR__ . '/config.php';
$id = (int)($_GET['id'] ?? 0);
if(!$id) jsonResponse(['error' => 'No order ID']);
$db = getDB();
$stmt = $db->prepare("SELECT status, updated_at FROM orders WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if($order) jsonResponse($order);
else jsonResponse(['error' => 'Not found']);
?>
