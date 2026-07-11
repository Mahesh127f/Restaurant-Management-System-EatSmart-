</div><!-- end page-wrapper -->

<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <h4 style="font-family:var(--font-display);font-size:1.3rem">EatSmart 🍽️</h4>
        <p style="font-size:0.85rem;color:rgba(255,255,255,0.4);margin-top:8px;line-height:1.6">
          A smarter dining experience. Reserve tables, pre-order food, and help reduce food waste.
        </p>
      </div>
      <div>
        <h4>Quick Links</h4>
        <a href="menu.php">Our Menu</a>
        <a href="reservation.php">Reserve a Table</a>
        <a href="waste_deals.php">🌱 Waste Deals</a>
        <a href="my_orders.php">Track Order</a>
      </div>
      <div>
        <h4>Account</h4>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
        <a href="my_orders.php">My Orders</a>
        <a href="my_reservations.php">My Reservations</a>
      </div>
      <div>
        <h4>Contact Us</h4>
        <p style="font-size:0.85rem;color:rgba(255,255,255,0.4);line-height:1.8">
          📍 42 Food Street, New Delhi<br>
          📞 +91 98765 43210<br>
          📧 hello@eatsmart.com<br>
          ⏰ 11 AM – 11 PM Daily
        </p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© <?php echo date('Y'); ?> EatSmart. Made with ❤️ by Group EatSmart.</p>
    </div>
  </div>
</footer>

<script src="<?php echo SITE_URL; ?>/js/main.js"></script>
<?php if(isset($extraJs)) echo $extraJs; ?>
</body>
</html>
