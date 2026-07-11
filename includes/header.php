<?php
require_once __DIR__ . '/../php/config.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? $pageTitle . ' — ' . SITE_NAME : SITE_NAME; ?></title>
  <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍽️</text></svg>">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="navbar-inner">
    <a href="<?php echo SITE_URL; ?>/index.php" class="navbar-brand">Eat<span>Smart</span></a>
    <ul class="navbar-nav" id="mainNav">
      <li><a href="index.php" class="<?php echo $currentPage==='index'?'active':''; ?>">Home</a></li>
      <li><a href="menu.php" class="<?php echo $currentPage==='menu'?'active':''; ?>">Menu</a></li>
      <li><a href="reservation.php" class="<?php echo $currentPage==='reservation'?'active':''; ?>">Reserve Table</a></li>
      <li><a href="waste_deals.php" class="<?php echo $currentPage==='waste_deals'?'active':''; ?>">🌱 Deals</a></li>
      <?php if(isLoggedIn()): ?>
      <li><a href="my_orders.php" class="<?php echo $currentPage==='my_orders'?'active':''; ?>">My Orders</a></li>
      <?php if(isAdmin()): ?>
      <li><a href="admin/index.php" style="color:var(--primary);font-weight:600">Admin</a></li>
      <?php endif; ?>
      <li><a href="php/auth.php?action=logout">Logout (<?php echo sanitize($_SESSION['name'] ?? ''); ?>)</a></li>
      <?php else: ?>
      <li><a href="login.php" class="<?php echo $currentPage==='login'?'active':''; ?>">Login</a></li>
      <li><a href="register.php" class="<?php echo $currentPage==='register'?'active':''; ?>">Register</a></li>
      <?php endif; ?>
    </ul>
    <div class="navbar-actions">
      <button class="cart-btn" onclick="openCart()">
        🛒 Cart <span class="cart-count" id="cartCountTop">0</span>
      </button>
      <button class="hamburger" onclick="toggleNav()" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<!-- Cart Sidebar -->
<div class="cart-overlay" id="cartOverlay"></div>
<div class="cart-sidebar" id="cartSidebar">
  <div class="cart-header">
    <h3 style="font-family:var(--font-display)">Your Order</h3>
    <button class="cart-close" onclick="closeCart()">✕</button>
  </div>
  <div class="cart-items" id="cartItems"></div>
  <div class="cart-footer">
    <div class="cart-total-row"><span>Subtotal</span><span id="cartSubtotal">₹0</span></div>
    <div class="cart-total-row"><span>GST (5%)</span><span id="cartTax">₹0</span></div>
    <div class="cart-total-row grand"><span>Total</span><span id="cartTotal">₹0</span></div>
    <?php if(isLoggedIn()): ?>
    <a href="checkout.php" class="btn btn-primary btn-block btn-lg">Proceed to Checkout</a>
    <?php else: ?>
    <a href="login.php" class="btn btn-primary btn-block">Login to Order</a>
    <?php endif; ?>
  </div>
</div>

<!-- Chatbot -->
<button class="chatbot-toggle" onclick="Chatbot.toggle()" title="Chat with us">💬</button>
<div class="chatbot-window" id="chatWindow">
  <div class="chatbot-head">
    <div class="chatbot-avatar">🤖</div>
    <div><h4>EatSmart Assistant</h4><p>Usually replies instantly</p></div>
    <button onclick="Chatbot.toggle()" style="background:none;border:none;color:rgba(255,255,255,0.6);font-size:1.1rem;cursor:pointer;margin-left:auto">✕</button>
  </div>
  <div class="chatbot-quick">
    <button class="quick-chip" onclick="Chatbot.send('Show me the menu')">📋 Menu</button>
    <button class="quick-chip" onclick="Chatbot.send('Book a table')">🪑 Reserve</button>
    <button class="quick-chip" onclick="Chatbot.send('Best deals')">💚 Deals</button>
    <button class="quick-chip" onclick="Chatbot.send('Track my order')">📦 Track order</button>
  </div>
  <div class="chatbot-msgs" id="chatMsgs"></div>
  <div class="chatbot-input-row">
    <input id="chatInput" class="chatbot-input" placeholder="Type a message..." autocomplete="off">
    <button class="chatbot-send" onclick="Chatbot.send(document.getElementById('chatInput').value)">➤</button>
  </div>
</div>

<div class="page-wrapper fade-in">
