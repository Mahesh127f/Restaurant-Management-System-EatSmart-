<?php
require_once __DIR__ . '/config.php';
$action = $_GET['action'] ?? '';
if($action === 'logout') {
  session_destroy();
  header('Location: ' . SITE_URL . '/index.php');
  exit;
}
?>
